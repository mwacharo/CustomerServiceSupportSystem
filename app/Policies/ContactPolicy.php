<?php

namespace App\Policies;

use App\Models\Contact;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ContactPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->SuperAdmin || $user->hasRole('CallCentre'); // Admin or Manager can view any contacts
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Contact $contact): bool
    {
        // Only the user who created the contact or an admin can view the contact
        return $user->id === $contact->user_id || $user->SuperAdmin;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Admin or managers can create contacts
        return $user->SuperAdmin || $user->hasRole('CallCentre');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Contact $contact): bool
    {
        // Users can only update their own contact or admins can update any contact
        return $user->id === $contact->user_id || $user->SuperAdmin;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Contact $contact): bool
    {
        // Only admins or the user who created the contact can delete it
        return $user->id === $contact->user_id || $user->SuperAdmin;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Contact $contact): bool
    {
        // Admin can restore any contact
        return $user->SuperAdmin;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Contact $contact): bool
    {
        // Admin can force delete any contact
        return $user->SuperAdmin;
    }
}
