<?php

namespace App\Livewire\Settings;

use App\Models\UserActivity as UserActivityModel;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class UserActivity extends Component
{
    public $activities = [];
    public $perPage = 10;
    public $activityType = 'all'; // 'all', 'login', 'logout'
    public $searchTerm = '';

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->loadActivities();
    }

    /**
     * Load user activities based on filters.
     */
    public function loadActivities(): void
    {
        $query = UserActivityModel::with('user')
            ->select('user_activities.*')
            ->join('users', 'user_activities.user_id', '=', 'users.id')
            ->orderBy('user_activities.created_at', 'desc');

        // Filter by activity type
        if ($this->activityType !== 'all') {
            $query->where('activity_type', $this->activityType);
        }

        // Search by user name or email
        if (!empty($this->searchTerm)) {
            $query->where(function ($q) {
                $q->where('users.name', 'like', '%' . $this->searchTerm . '%')
                  ->orWhere('users.email', 'like', '%' . $this->searchTerm . '%')
                  ->orWhere('user_activities.ip_address', 'like', '%' . $this->searchTerm . '%');
            });
        }

        $this->activities = $query->limit($this->perPage)->get();
    }

    /**
     * Update the activity type filter.
     */
    public function filterByType($type): void
    {
        $this->activityType = $type;
        $this->loadActivities();
    }

    /**
     * Clear all filters.
     */
    public function clearFilters(): void
    {
        $this->activityType = 'all';
        $this->searchTerm = '';
        $this->loadActivities();
    }

    /**
     * Update search term and reload activities.
     */
    public function updatedSearchTerm(): void
    {
        $this->loadActivities();
    }

    /**
     * Render the component.
     */
    public function render()
    {
        return view('livewire.settings.user-activity');
    }
}
