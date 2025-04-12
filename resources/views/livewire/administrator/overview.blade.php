{{--
/**
 * Administrator Overview Dashboard View
 *
 * This Blade template provides a dashboard overview of key system entities through widgets:
 * - Users count and recent registrations
 * - Roles count and distribution
 * - Permissions count and usage statistics
 *
 * Features:
 * - Responsive widget layout using grid system
 * - Data visualization of user, role, and permission statistics
 * - Quick access to recent user registrations
 * - Dark mode support
 *
 * Components:
 * - Flux UI components for consistent styling and behavior
 * - Livewire for reactive data binding
 *
 * @see App\Livewire\Administrator\Overview
 */
--}}

<flux:container>
    {{-- Page Header --}}
    <div class="mb-6">
        <flux:heading level="1" size="xl">Administrator Overview</flux:heading>
        <flux:text class="text-zinc-500 dark:text-zinc-400">System statistics and recent activity</flux:text>
    </div>

    {{-- Dashboard Widgets Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">

        {{-- Users Widget --}}
        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-lg shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <flux:heading level="2" size="lg">Users</flux:heading>
                <flux:badge variant="solid" color="blue" class="rounded-full! p-2!">
                    <flux:icon.user variant="solid" />
                </flux:badge>
            </div>
            <div class="mb-4">
                <div class="text-3xl font-bold text-zinc-900 dark:text-white">{{ $totalUsers }}</div>
                <div class="text-sm text-zinc-500 dark:text-zinc-400">Total registered users</div>
            </div>
            <flux:separator class="mb-4" />
            <div>
                <div class="text-sm font-medium text-zinc-900 dark:text-white mb-2">Recent Registrations</div>
                <ul class="space-y-2">
                    @forelse($latestUsers as $user)
                    <li class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="w-8 h-8 rounded-full bg-zinc-200 dark:bg-zinc-700 flex items-center justify-center mr-2">
                                <span class="text-xs font-medium">{{ auth()->user()->initials() }}</span>
                            </div>
                            <div>
                                <div class="text-sm font-medium text-zinc-900 dark:text-white">{{ $user->name }}</div>
                                <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ $user->email }}</div>
                            </div>
                        </div>
                        <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ $user->created_at->diffForHumans() }}</div>
                    </li>
                    @empty
                    <li class="text-sm text-zinc-500 dark:text-zinc-400">No recent users</li>
                    @endforelse
                </ul>
            </div>
        </div>

        {{-- Roles Widget --}}
        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-lg shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <flux:heading level="2" size="lg">Roles</flux:heading>
                <flux:badge variant="solid" color="purple" class="rounded-full! p-2!">
                    <flux:icon.shield-check variant="solid" />
                </flux:badge>
            </div>
            <div class="mb-4">
                <div class="text-3xl font-bold text-zinc-900 dark:text-white">{{ $totalRoles }}</div>
                <div class="text-sm text-zinc-500 dark:text-zinc-400">Total system roles</div>
            </div>
            <flux:separator class="mb-4" />
            <div>
                <div class="text-sm font-medium text-zinc-900 dark:text-white mb-2">Top Roles by Usage</div>
                <ul class="space-y-2">
                    @forelse($topRoles as $role)
                    <li class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="w-8 h-8 rounded-full bg-purple-500 dark:bg-purple-600 flex items-center justify-center mr-2">
                                <span class="text-xs font-medium text-white dark:text-white">{{ substr($role->name, 0, 1) }}</span>
                            </div>
                            <div class="text-sm font-medium text-zinc-900 dark:text-white">{{ $role->name }}</div>
                        </div>
                        <div>
                            <flux:badge variant="secondary">{{ $role->users_count }} {{ Str::plural('user', $role->users_count) }}</flux:badge>
                        </div>
                    </li>
                    @empty
                    <li class="text-sm text-zinc-500 dark:text-zinc-400">No roles defined</li>
                    @endforelse
                </ul>
            </div>
        </div>

        {{-- Permissions Widget --}}
        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-lg shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <flux:heading level="2" size="lg">Permissions</flux:heading>
                <flux:badge variant="solid" color="green" class="rounded-full! p-2!">
                    <flux:icon.key variant="solid" />
                </flux:badge>
            </div>
            <div class="mb-4">
                <div class="text-3xl font-bold text-zinc-900 dark:text-white">{{ $totalPermissions }}</div>
                <div class="text-sm text-zinc-500 dark:text-zinc-400">Total system permissions</div>
            </div>
            <flux:separator class="mb-4" />
            <div>
                <div class="text-sm font-medium text-zinc-900 dark:text-white mb-2">Most Used Permissions</div>
                <ul class="space-y-2">
                    @forelse($topPermissions as $permission)
                    <li class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="w-8 h-8 rounded-full bg-green-500 dark:bg-green-600 flex items-center justify-center mr-2">
                                <span class="text-xs font-medium text-white dark:text-white">{{ substr($permission->name, 0, 1) }}</span>
                            </div>
                            <div class="text-sm font-medium text-zinc-900 dark:text-white">{{ $permission->name }}</div>
                        </div>
                        <div>
                            <flux:badge variant="secondary">{{ $permission->roles_count }} {{ Str::plural('role', $permission->roles_count) }}</flux:badge>
                        </div>
                    </li>
                    @empty
                    <li class="text-sm text-zinc-500 dark:text-zinc-400">No permissions defined</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</flux:container>
