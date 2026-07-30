<?php

namespace App\Http\Controllers;

use App\Http\Requests\CommandPaletteSearchRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;

class CommandPaletteController extends Controller
{
    public function __invoke(CommandPaletteSearchRequest $request): JsonResponse
    {
        $searchQuery = $request->validated('query');
        $likeSearchQuery = "%{$searchQuery}%";

        $users = User::query()
            ->where('active', true)
            ->where(function (Builder $query) use ($likeSearchQuery): void {
                $query->where('vol_code', 'like', $likeSearchQuery)
                    ->orWhere('email', 'like', $likeSearchQuery)
                    ->orWhere('name', 'like', $likeSearchQuery)
                    ->orWhere('first_name', 'like', $likeSearchQuery)
                    ->orWhere('last_name', 'like', $likeSearchQuery)
                    ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", [$likeSearchQuery]);
            })
            ->orderBy('name')
            ->limit(8)
            ->get(['id', 'vol_code', 'email', 'name', 'first_name', 'last_name'])
            ->map(fn (User $user): array => [
                'id' => $user->id,
                'title' => $user->displayName(),
                'alias' => $user->name,
                'email' => $user->email,
                'vol_code' => $user->vol_code,
                'url' => route('users.show', $user),
            ]);

        return response()->json(['users' => $users]);
    }
}
