<?php

namespace App\Policies;

use App\Models\Template;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TemplatePolicy
{
    /**
     * Determine whether the user can view any templates.
     */
    public function viewAny(User $user): bool
    {
        // Allow all authenticated users to view templates
        return true;
    }

    /**
     * Determine whether the user can view a specific template.
     */
    public function view(User $user, Template $template): bool
    {
        // User can view if they own it (owner_id matches) or are admin
        return $user->id === $template->owner_id || $user->hasRole('admin');
    }

    /**
     * Determine whether the user can create templates.
     */
    public function create(User $user): bool
    {
        // Any authenticated user can create
        return true;
    }

    /**
     * Determine whether the user can update a specific template.
     */
    public function update(User $user, Template $template): bool
    {
        // Only owner or admin can update
        return $user->id === $template->owner_id || $user->hasRole('admin');
    }

    /**
     * Determine whether the user can delete a specific template.
     */
    public function delete(User $user, Template $template): bool
    {
        // Only owner or admin can delete
        return $user->id === $template->owner_id || $user->hasRole('admin');
    }

    /**
     * Determine whether the user can restore a deleted template.
     */
    public function restore(User $user, Template $template): bool
    {
        // Only owner or admin can restore
        return $user->id === $template->owner_id || $user->hasRole('admin');
    }

    /**
     * Determine whether the user can permanently delete a template.
     */
    public function forceDelete(User $user, Template $template): bool
    {
        // Only admin should be able to force delete
        return $user->hasRole('admin');
    }
}
