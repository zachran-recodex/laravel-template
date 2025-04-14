<?php

namespace App\Listeners;

use App\Models\UserActivity;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class LogUserActivity implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle($event): void
    {
        if ($event instanceof Login) {
            $this->logActivity($event->user, 'login');
        } elseif ($event instanceof Logout) {
            $this->logActivity($event->user, 'logout');
        }
    }

    /**
     * Log the user activity.
     */
    private function logActivity($user, string $activityType): void
    {
        UserActivity::create([
            'user_id' => $user->id,
            'activity_type' => $activityType,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
