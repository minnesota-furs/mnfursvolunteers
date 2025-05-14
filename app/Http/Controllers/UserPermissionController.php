<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class UserPermissionController extends Controller
{
    public function edit(User $user)
    {
        $availablePermissions = config('permissions');
        return view('users.permissions.edit', compact('user', 'availablePermissions'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'permissions' => 'array',
            'permissions.*' => 'string',
        ]);

        $user->permissions = $validated['permissions'] ?? [];
        $user->save();

        return redirect()->route('users.show', $user)->with('status', 'Permissions updated.');
    }
}
