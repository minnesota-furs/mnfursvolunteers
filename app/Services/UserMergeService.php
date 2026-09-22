<?php

namespace App\Services;

use App\Models\ApplicationComment;
use App\Models\AuditLog;
use App\Models\Candidate;
use App\Models\CommunicationLog;
use App\Models\ConcatUserRoleGrant;
use App\Models\CustomFieldValue;
use App\Models\Department;
use App\Models\JobApplication;
use App\Models\Note;
use App\Models\NoteComment;
use App\Models\OneOffEventCheckIn;
use App\Models\OneOffEventReminder;
use App\Models\OneOffEventRsvp;
use App\Models\Recognition;
use App\Models\StaffCheckIn;
use App\Models\StaffCheckInSession;
use App\Models\User;
use App\Models\UserRelationship;
use App\Models\VolunteerHours;
use App\Models\VolunteerPerkRedemption;
use App\Models\Vote;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Laravel\Passport\AuthCode;
use Laravel\Passport\Client;
use Laravel\Passport\Token;

class UserMergeService
{
    /**
     * Reassign every related record owned by $duplicates onto $survivor, then
     * soft-delete each duplicate and mark it as merged.
     *
     * @param  Collection<int, User>  $duplicates
     * @return array<string, int> counts of records reassigned, keyed by category
     */
    public function merge(User $survivor, Collection $duplicates, User $actor): array
    {
        if ($duplicates->isEmpty()) {
            throw new InvalidArgumentException('No duplicate users were selected to merge.');
        }

        if ($duplicates->contains(fn (User $duplicate): bool => $duplicate->is($survivor))) {
            throw new InvalidArgumentException('Cannot merge a user into itself.');
        }

        $totals = [];

        DB::transaction(function () use ($survivor, $duplicates, $actor, &$totals): void {
            foreach ($duplicates as $duplicate) {
                foreach ($this->mergeOne($survivor, $duplicate) as $key => $count) {
                    $totals[$key] = ($totals[$key] ?? 0) + $count;
                }

                AuditLog::create([
                    'action' => 'user_merged',
                    'auditable_type' => User::class,
                    'auditable_id' => $survivor->id,
                    'comment' => "User {$actor->name} merged '{$duplicate->name}' (ID: {$duplicate->id}) into '{$survivor->name}' (ID: {$survivor->id})",
                    'user_id' => $actor->id,
                ]);

                $duplicate->merged_into_id = $survivor->id;
                $duplicate->save();
                $duplicate->delete();
            }
        });

        return $totals;
    }

    /**
     * @return array<string, int>
     */
    protected function mergeOne(User $survivor, User $duplicate): array
    {
        $counts = [];

        // Straight FK reassigns: no unique constraint on the column being changed.
        $counts['volunteer_hours'] = VolunteerHours::query()->where('user_id', $duplicate->id)->update(['user_id' => $survivor->id]);
        $counts['audit_logs'] = AuditLog::query()->where('user_id', $duplicate->id)->update(['user_id' => $survivor->id]);
        $counts['communication_logs'] = CommunicationLog::query()->where('user_id', $duplicate->id)->update(['user_id' => $survivor->id])
            + CommunicationLog::query()->where('sent_by', $duplicate->id)->update(['sent_by' => $survivor->id]);
        $counts['notes'] = Note::query()->where('user_id', $duplicate->id)->update(['user_id' => $survivor->id])
            + Note::query()->where('created_by', $duplicate->id)->update(['created_by' => $survivor->id]);
        $counts['note_comments'] = NoteComment::query()->where('user_id', $duplicate->id)->update(['user_id' => $survivor->id]);
        $counts['application_comments'] = ApplicationComment::query()->where('user_id', $duplicate->id)->update(['user_id' => $survivor->id]);
        $counts['job_applications'] = JobApplication::query()->where('user_id', $duplicate->id)->update(['user_id' => $survivor->id])
            + JobApplication::query()->where('claimed_by', $duplicate->id)->update(['claimed_by' => $survivor->id]);
        $counts['recognitions'] = Recognition::query()->where('user_id', $duplicate->id)->update(['user_id' => $survivor->id])
            + Recognition::query()->where('granted_by_user_id', $duplicate->id)->update(['granted_by_user_id' => $survivor->id]);
        $counts['staff_check_ins'] = StaffCheckIn::query()->where('user_id', $duplicate->id)->update(['user_id' => $survivor->id])
            + StaffCheckIn::query()->where('checked_in_by', $duplicate->id)->update(['checked_in_by' => $survivor->id]);
        $counts['staff_check_in_sessions'] = StaffCheckInSession::query()->where('created_by', $duplicate->id)->update(['created_by' => $survivor->id]);
        $counts['one_off_event_check_ins'] = OneOffEventCheckIn::query()->where('user_id', $duplicate->id)->update(['user_id' => $survivor->id]);
        $counts['oauth_clients'] = Client::query()->where('user_id', $duplicate->id)->update(['user_id' => $survivor->id]);
        $counts['oauth_tokens'] = Token::query()->where('user_id', $duplicate->id)->update(['user_id' => $survivor->id]);
        $counts['oauth_auth_codes'] = AuthCode::query()->where('user_id', $duplicate->id)->update(['user_id' => $survivor->id]);

        Department::query()->where('department_head_id', $duplicate->id)->update(['department_head_id' => $survivor->id]);

        // Row-level reassign-or-drop: table has a unique index that could collide.
        $counts['candidates'] = $this->reassignOrDrop(Candidate::class, $duplicate->id, $survivor->id, ['election_id']);
        $counts['votes'] = $this->reassignOrDrop(Vote::class, $duplicate->id, $survivor->id, ['election_id', 'candidate_id']);
        $counts['custom_field_values'] = $this->reassignOrDrop(CustomFieldValue::class, $duplicate->id, $survivor->id, ['custom_field_id']);
        $counts['volunteer_perk_redemptions'] = $this->reassignOrDrop(VolunteerPerkRedemption::class, $duplicate->id, $survivor->id, ['volunteer_perk_id']);
        $counts['concat_user_role_grants'] = $this->reassignOrDrop(ConcatUserRoleGrant::class, $duplicate->id, $survivor->id, ['sector_id']);
        $counts['one_off_event_rsvps'] = $this->reassignOrDrop(OneOffEventRsvp::class, $duplicate->id, $survivor->id, ['one_off_event_id']);
        $counts['one_off_event_reminders'] = $this->reassignOrDrop(OneOffEventReminder::class, $duplicate->id, $survivor->id, ['one_off_event_id']);

        // Pure junction tables with meaningful extra columns and no dedicated Eloquent model.
        $counts['shift_signups'] = $this->reassignOrDropPivot('shift_signups', $duplicate->id, $survivor->id, ['shift_id']);
        $counts['event_user'] = $this->reassignOrDropPivot('event_user', $duplicate->id, $survivor->id, ['event_id']);

        // belongsToMany pivots with no meaningful extra columns.
        $counts['tags'] = $this->mergePivotRelation($survivor->tags(), $duplicate->tags(), 'tags.id');
        $counts['departments'] = $this->mergePivotRelation($survivor->departments(), $duplicate->departments(), 'departments.id');
        $counts['head_departments'] = $this->mergePivotRelation($survivor->headDepartments(), $duplicate->headDepartments(), 'departments.id');

        $counts['user_relationships'] = $this->reassignUserRelationships($survivor, $duplicate);

        return $counts;
    }

    /**
     * @param  class-string<Model>  $modelClass
     * @param  array<int, string>  $otherKeys
     */
    protected function reassignOrDrop(string $modelClass, int $duplicateId, int $survivorId, array $otherKeys): int
    {
        $moved = 0;

        foreach ($modelClass::query()->where('user_id', $duplicateId)->get() as $row) {
            $conflictQuery = $modelClass::query()->where('user_id', $survivorId);
            foreach ($otherKeys as $key) {
                $conflictQuery->where($key, $row->{$key});
            }

            if ($conflictQuery->exists()) {
                $row->delete();

                continue;
            }

            $row->update(['user_id' => $survivorId]);
            $moved++;
        }

        return $moved;
    }

    /**
     * Same as reassignOrDrop() but for a plain junction table with no Eloquent model.
     *
     * @param  array<int, string>  $otherKeys
     */
    protected function reassignOrDropPivot(string $table, int $duplicateId, int $survivorId, array $otherKeys): int
    {
        $moved = 0;

        foreach (DB::table($table)->where('user_id', $duplicateId)->get() as $row) {
            $conflictQuery = DB::table($table)->where('user_id', $survivorId);
            foreach ($otherKeys as $key) {
                $conflictQuery->where($key, $row->{$key});
            }

            if ($conflictQuery->exists()) {
                DB::table($table)->where('id', $row->id)->delete();

                continue;
            }

            DB::table($table)->where('id', $row->id)->update(['user_id' => $survivorId]);
            $moved++;
        }

        return $moved;
    }

    /**
     * Move every related row from $duplicateRelation onto $survivorRelation via
     * syncWithoutDetaching (safe against the pivot's unique constraint), then
     * detach everything from the duplicate.
     */
    protected function mergePivotRelation(BelongsToMany $survivorRelation, BelongsToMany $duplicateRelation, string $qualifiedKey): int
    {
        $ids = $duplicateRelation->pluck($qualifiedKey);
        $count = $ids->count();

        if ($count > 0) {
            $survivorRelation->syncWithoutDetaching($ids);
        }

        $duplicateRelation->detach();

        return $count;
    }

    protected function reassignUserRelationships(User $survivor, User $duplicate): int
    {
        $moved = 0;

        foreach (UserRelationship::where('user_id', $duplicate->id)->get() as $relationship) {
            if ($relationship->target_user_id === $survivor->id) {
                $relationship->delete();

                continue;
            }

            $conflict = UserRelationship::where('user_id', $survivor->id)
                ->where('target_user_id', $relationship->target_user_id)
                ->exists();

            if ($conflict) {
                $relationship->delete();

                continue;
            }

            $relationship->update(['user_id' => $survivor->id]);
            $moved++;
        }

        foreach (UserRelationship::where('target_user_id', $duplicate->id)->get() as $relationship) {
            if ($relationship->user_id === $survivor->id) {
                $relationship->delete();

                continue;
            }

            $conflict = UserRelationship::where('user_id', $relationship->user_id)
                ->where('target_user_id', $survivor->id)
                ->exists();

            if ($conflict) {
                $relationship->delete();

                continue;
            }

            $relationship->update(['target_user_id' => $survivor->id]);
            $moved++;
        }

        return $moved;
    }
}
