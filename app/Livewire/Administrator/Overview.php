<?php

namespace App\Livewire\Administrator;

use App\Models\User;
use Livewire\Component;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

/**
 * Administrator Overview Livewire Component.
 *
 * Provides a dashboard overview of key system entities:
 * - Users count and recent registrations
 * - Roles count and distribution
 * - Permissions count and usage statistics
 *
 * This component is designed to give administrators a quick snapshot
 * of the system's user management state through widget-style displays.
 */
class Overview extends Component
{
    /**
     * Get the latest registered users for display in the widget.
     *
     * @param int $limit The maximum number of users to retrieve
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getLatestUsers(int $limit = 5)
    {
        return User::latest()->take($limit)->get();
    }

    /**
     * Get the most used roles based on user assignments.
     *
     * @param int $limit The maximum number of roles to retrieve
     * @return \Illuminate\Support\Collection
     */
    public function getTopRoles(int $limit = 5)
    {
        return Role::withCount('users')
            ->orderByDesc('users_count')
            ->take($limit)
            ->get();
    }

    /**
     * Get the most assigned permissions across all roles.
     *
     * @param int $limit The maximum number of permissions to retrieve
     * @return \Illuminate\Support\Collection
     */
    public function getTopPermissions(int $limit = 5)
    {
        return Permission::withCount('roles')
            ->orderByDesc('roles_count')
            ->take($limit)
            ->get();
    }

    /**
     * Render the component view.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('livewire.administrator.overview', [
            'totalUsers' => User::count(),
            'latestUsers' => $this->getLatestUsers(),
            'totalRoles' => Role::count(),
            'topRoles' => $this->getTopRoles(),
            'totalPermissions' => Permission::count(),
            'topPermissions' => $this->getTopPermissions(),
        ]);
    }
}
