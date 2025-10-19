<?php

namespace App\Policies;

use App\Models\Event;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class EventPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('manage-events');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Event $event): bool
    {
        return true; // Anyone authenticated can view events
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('manage-events');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Event $event): bool
    {
        // Admin with manage-events permission can edit any event
        if ($user->hasPermission('manage-events')) {
            return true;
        }

        // Original creator can edit
        if ($event->created_by === $user->id) {
            return true;
        }

        // Users explicitly granted edit permission can edit
        return $event->editors()->where('user_id', $user->id)->exists();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Event $event): bool
    {
        // Admin with manage-events permission can delete any event
        if ($user->hasPermission('manage-events')) {
            return true;
        }

        // Original creator can delete
        return $event->created_by === $user->id;
    }

    /**
     * Determine whether the user can manage editors for the event.
     */
    public function manageEditors(User $user, Event $event): bool
    {
        // Admin with manage-events permission can manage editors
        if ($user->hasPermission('manage-events')) {
            return true;
        }

        // Original creator can manage editors
        return $event->created_by === $user->id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Event $event): bool
    {
        return $user->hasPermission('manage-events');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Event $event): bool
    {
        return $user->hasPermission('manage-events');
    }
}
