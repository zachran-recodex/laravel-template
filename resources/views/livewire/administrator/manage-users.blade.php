{{--
/**
 * User Management Interface View
 *
 * This Blade template provides a comprehensive UI for managing users within the application.
 * It works with the ManageUsers Livewire component to handle all CRUD operations.
 *
 * Features:
 * - Responsive user listing with pagination
 * - Real-time search functionality with debounce
 * - Modal forms for user creation and editing
 * - Role assignment via checkbox group
 * - User deletion with confirmation modal
 * - Flash notifications with automatic timeout
 * - Dark mode support
 *
 * Components:
 * - Flux UI components for consistent styling and behavior
 * - Livewire for reactive data binding and real-time updates
 * - Alpine.js for enhancing interactivity (notifications)
 *
 * @see App\Livewire\Administrator\ManageUsers
 * @see Spatie\Permission\Models\Role
 */
--}}

<flux:container>
    {{-- Page Header --}}
    <div class="flex justify-between items-center mb-6">
        <flux:heading level="1" size="xl">Manage Users</flux:heading>
        <flux:modal.trigger name="form" wire:click="openModal">
            <flux:button icon="plus" variant="primary">
                Add User
            </flux:button>
        </flux:modal.trigger>
    </div>

    {{-- Notification Area - Auto-dismisses after 5 seconds --}}
    @if($notification)
    <div class="mb-6" x-data="{shown: true}" x-init="setTimeout(() => {shown = false; $wire.set('notification', null);}, 5000)" x-show="shown">
        <flux:callout variant="{{ $notificationType }}"
                     icon="{{ $notificationType === 'success' ? 'check-circle' :
                            ($notificationType === 'warning' ? 'exclamation-circle' :
                            ($notificationType === 'danger' ? 'x-circle' : 'information-circle')) }}"
                     heading="{{ $notification }}" />
    </div>
    @endif

    {{-- Search Box with Live Debounce --}}
    <div class="mb-6">
        <flux:input wire:model.live.debounce.300ms="search" placeholder="Search users..." icon="magnifying-glass" />
    </div>

    {{-- Users Table with Responsive Layout --}}
    <div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-lg shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                <thead class="bg-zinc-50 dark:bg-zinc-800">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-300 uppercase tracking-wider">
                            No
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-300 uppercase tracking-wider">
                            Name
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-300 uppercase tracking-wider">
                            Email
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-300 uppercase tracking-wider">
                            Roles
                        </th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-300 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-zinc-900 divide-y divide-zinc-200 dark:divide-zinc-700">
                    @forelse($users as $user)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">
                            {{ $loop->iteration }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-zinc-900 dark:text-white">
                            {{ $user->name }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">
                            {{ $user->email }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">
                            @foreach($user->roles as $role)
                                <flux:badge variant="secondary" class="mr-1 mb-1">{{ $role->name }}</flux:badge>
                            @endforeach
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex justify-end gap-2">
                                {{-- Edit Button --}}
                                <flux:modal.trigger name="form" wire:click="editUser({{ $user->id }})">
                                    <flux:button icon="pencil" variant="filled" size="sm"></flux:button>
                                </flux:modal.trigger>

                                {{-- Delete Button --}}
                                <flux:modal.trigger name="delete" wire:click="confirmDelete({{ $user->id }})">
                                    <flux:button icon="trash" variant="danger" size="sm"></flux:button>
                                </flux:modal.trigger>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center text-sm text-zinc-500 dark:text-zinc-400">
                            No users found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-zinc-50 dark:bg-zinc-800">
                    <tr>
                        <td colspan="5" class="px-4 py-3">
                            {{ $users->links() }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- Create/Edit User Modal Form --}}
    <flux:modal name="form" class="min-w-sm md:min-w-lg lg:min-w-xl">
        <flux:heading size="lg" class="mb-4">{{ $isEditing ? 'Edit User' : 'Add New User' }}</flux:heading>

        <flux:separator class="mb-4" />

        <form wire:submit="save" class="space-y-6">
            {{-- Name Field --}}
            <flux:field>
                <flux:label>Name</flux:label>
                <flux:input wire:model="name" type="text" />
                <flux:error name="name" />
            </flux:field>

            {{-- Email Field --}}
            <flux:field>
                <flux:label>Email</flux:label>
                <flux:input wire:model="email" type="email" />
                <flux:error name="email" />
            </flux:field>

            {{-- Password Field - Conditional label based on edit/create mode --}}
            <flux:field>
                <flux:label>{{ $isEditing ? 'New Password (leave blank to keep current)' : 'Password' }}</flux:label>
                <flux:input wire:model="password" type="password" />
                <flux:error name="password" />
            </flux:field>

            {{-- Password Confirmation Field --}}
            <flux:field>
                <flux:label>Confirm Password</flux:label>
                <flux:input wire:model="password_confirmation" type="password" />
            </flux:field>

            {{-- Roles Selection Checkbox Group --}}
            <flux:field>
                <flux:label>Roles</flux:label>
                <flux:checkbox.group>
                    @foreach($roles as $role)
                        <flux:checkbox label="{{ $role->name }}" id="role_{{ $role->id }}" wire:model="selectedRoles" value="{{ $role->id }}" />
                    @endforeach
                </flux:checkbox.group>
                <flux:error name="selectedRoles" />
            </flux:field>

            {{-- Form Action Buttons --}}
            <div class="flex">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost" class="mr-2">Cancel</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">{{ $isEditing ? 'Update' : 'Create' }}</flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- Delete Confirmation Modal --}}
    <flux:modal name="delete" class="min-w-[22rem]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Delete user?</flux:heading>
                <flux:text class="mt-2">
                    <p>You're about to delete this user.</p>
                    <p>This action cannot be reversed.</p>
                </flux:text>
            </div>
            <div class="flex gap-2">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost">Cancel</flux:button>
                </flux:modal.close>
                <flux:button wire:click="deleteUser" variant="danger">Delete User</flux:button>
            </div>
        </div>
    </flux:modal>

</flux:container>
