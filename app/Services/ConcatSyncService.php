<?php

namespace App\Services;

use App\Models\ApplicationSetting;
use App\Models\ConcatSectorRoleMapping;
use App\Models\ConcatUserRoleGrant;
use App\Models\Sector;
use App\Models\User;

class ConcatSyncService
{
    public function __construct(private ConcatService $concat = new ConcatService) {}

    /**
     * Reconcile every watched sector's ConCat role grants against its current
     * department membership.
     *
     * @return array{granted: int, revoked: int, unmatched: int}
     */
    public function syncAll(): array
    {
        $totals = ['granted' => 0, 'revoked' => 0, 'unmatched' => 0];

        foreach (ConcatSectorRoleMapping::with('sector')->get() as $mapping) {
            $result = $this->syncSector($mapping->sector, $mapping);

            $totals['granted'] += $result['granted'];
            $totals['revoked'] += $result['revoked'];
            $totals['unmatched'] += $result['unmatched'];
        }

        ApplicationSetting::set('concat_last_synced_at', now()->toDateTimeString(), 'string', 'Last ConCat sync run', 'integrations');

        return $totals;
    }

    /**
     * Reconcile a single sector's ConCat role grants against its current
     * department membership. Grants users newly present in the sector who
     * match a ConCat email, and revokes grants for users no longer in any
     * department beneath the sector.
     *
     * @return array{granted: int, revoked: int, unmatched: int}
     */
    public function syncSector(Sector $sector, ?ConcatSectorRoleMapping $mapping = null): array
    {
        $mapping ??= $sector->concatRoleMapping;
        $result = ['granted' => 0, 'revoked' => 0, 'unmatched' => 0];

        if (! $mapping) {
            return $result;
        }

        $currentUserIds = User::whereHas('departments', function ($query) use ($sector) {
            $query->where('departments.sector_id', $sector->id);
        })->pluck('users.id');
        $existingGrants = ConcatUserRoleGrant::where('sector_id', $sector->id)->get()->keyBy('user_id');

        foreach ($existingGrants as $userId => $grant) {
            if ($currentUserIds->contains($userId)) {
                continue;
            }

            $this->concat->revokeRole($grant->concat_user_id, $grant->concat_role_id);
            $grant->delete();
            $result['revoked']++;
        }

        foreach ($currentUserIds as $userId) {
            if ($existingGrants->has($userId)) {
                continue;
            }

            $user = User::find($userId);
            if (! $user) {
                continue;
            }

            $concatUserId = $this->resolveConcatUserId($user);

            if (! $concatUserId) {
                $result['unmatched']++;

                continue;
            }

            $granted = $this->concat->grantRole($concatUserId, $mapping->concat_role_id, $mapping->concat_scope);

            if ($granted) {
                ConcatUserRoleGrant::create([
                    'user_id' => $user->id,
                    'sector_id' => $sector->id,
                    'concat_user_id' => $concatUserId,
                    'concat_role_id' => $mapping->concat_role_id,
                    'granted_at' => now(),
                ]);
                $result['granted']++;
            }
        }

        return $result;
    }

    /**
     * Revoke every grant a sector currently holds, without regard to
     * department membership — used when an admin un-watches a sector.
     */
    public function revokeAllForSector(Sector $sector): int
    {
        $grants = ConcatUserRoleGrant::where('sector_id', $sector->id)->get();

        foreach ($grants as $grant) {
            $this->concat->revokeRole($grant->concat_user_id, $grant->concat_role_id);
            $grant->delete();
        }

        return $grants->count();
    }

    /**
     * Manually associate a user with a specific ConCat account, overriding
     * whatever email-based match would otherwise apply. Any grants made under
     * a previous (likely wrong) ConCat account are revoked first, then the
     * user's currently-watched sectors are re-synced to grant under the new one.
     *
     * @return array{granted: int, revoked: int}
     */
    public function associateUser(User $user, string $concatUserId): array
    {
        $revoked = $this->revokeAllForUser($user);

        $user->update([
            'concat_user_id' => $concatUserId,
            'concat_checked_at' => now(),
        ]);

        $granted = 0;
        $watchedSectorIds = ConcatSectorRoleMapping::pluck('sector_id');
        $sectors = Sector::whereIn('id', $watchedSectorIds)
            ->whereHas('departments.users', fn ($query) => $query->where('users.id', $user->id))
            ->get();

        foreach ($sectors as $sector) {
            $granted += $this->syncSector($sector)['granted'];
        }

        return ['granted' => $granted, 'revoked' => $revoked];
    }

    /**
     * Remove a user's ConCat association entirely and revoke every role it granted.
     */
    public function disassociateUser(User $user): array
    {
        $revoked = $this->revokeAllForUser($user);

        $user->update([
            'concat_user_id' => null,
            'concat_checked_at' => null,
        ]);

        return ['revoked' => $revoked];
    }

    private function revokeAllForUser(User $user): int
    {
        $grants = ConcatUserRoleGrant::where('user_id', $user->id)->get();

        foreach ($grants as $grant) {
            $this->concat->revokeRole($grant->concat_user_id, $grant->concat_role_id);
            $grant->delete();
        }

        return $grants->count();
    }

    /**
     * Use the cached ConCat user id if present, otherwise look the user up by
     * email and cache the result (even a miss) so it isn't re-queried every run.
     */
    private function resolveConcatUserId(User $user): ?string
    {
        if ($user->concat_user_id) {
            return $user->concat_user_id;
        }

        $match = $this->concat->findUserByEmail($user->email);

        $user->update([
            'concat_user_id' => $match['id'] ?? null,
            'concat_checked_at' => now(),
        ]);

        return $match['id'] ?? null;
    }
}
