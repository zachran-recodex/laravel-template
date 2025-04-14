<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('User Activity')" :subheading="__('View user login and logout activities')">
        <div class="my-6 w-full space-y-6">
            <div class="flex flex-col gap-4">
                <div class="flex flex-col md:flex-row md:items-center gap-4 justify-between">
                    <div class="flex items-center gap-4">
                        <flux:button
                            wire:click="filterByType('all')"
                            :variant="$activityType === 'all' ? 'primary' : 'outline'"
                        >
                            All
                        </flux:button>

                        <flux:button
                            wire:click="filterByType('login')"
                            :variant="$activityType === 'login' ? 'primary' : 'outline'"
                        >
                            Logins
                        </flux:button>

                        <flux:button
                            wire:click="filterByType('logout')"
                            :variant="$activityType === 'logout' ? 'primary' : 'outline'"
                        >
                            Logouts
                        </flux:button>
                    </div>

                    <div class="w-full md:w-64">
                        <flux:input
                            wire:model.live.debounce.300ms="searchTerm"
                            type="text"
                            placeholder="Search users..."
                            class="w-full"
                        />
                    </div>
                </div>

                <div class="mt-4 bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-lg shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                            <thead class="bg-zinc-50 dark:bg-zinc-800">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-300 uppercase tracking-wider">
                                        User
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-300 uppercase tracking-wider">
                                        Activity
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-300 uppercase tracking-wider">
                                        IP Address
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-300 uppercase tracking-wider">
                                        Date & Time
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-zinc-900 divide-y divide-zinc-200 dark:divide-zinc-700">
                                @forelse ($activities as $activity)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $activity->user->name }}</div>
                                                <div class="text-sm text-gray-500 dark:text-gray-400">{{ $activity->user->email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $activity->activity_type === 'login' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' }}">
                                            {{ $activity->activity_type === 'login' ? __('Login') : __('Logout') }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ $activity->ip_address }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ $activity->created_at->format('Y-m-d H:i:s') }}
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-4 text-center text-sm text-zinc-500 dark:text-zinc-400">
                                        No activities found.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                @if(count($activities) >= $perPage)
                    <div class="mt-4 flex justify-center">
                        <flux:button
                            wire:click="$set('perPage', $perPage + 10)"
                            variant="outline"
                        >
                            Load More
                        </flux:button>
                    </div>
                @endif
            </div>
        </div>
    </x-settings.layout>
</section>
