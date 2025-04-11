{{--
/**
 * Role Management Interface View
 *
 * This Blade template provides a comprehensive UI for managing roles within the application.
 * It works with the ManageRoles Livewire component to handle all CRUD operations.
 *
 * Features:
 * - Responsive role listing with pagination
 * - Real-time search functionality with debounce
 * - Modal forms for role creation and editing
 * - Permission assignment via checkbox group
 * - Role deletion with confirmation modal
 * - Flash notifications with automatic timeout
 * - Dark mode support
 *
 * Components:
 * - Flux UI components for consistent styling and behavior
 * - Livewire for reactive data binding and real-time updates
 * - Alpine.js for enhancing interactivity (notifications)
 *
 * @see App\Livewire\Administrator\ManageRoles
 * @see Spatie\Permission\Models\Permission
 */
--}}

<flux:container>
    {{-- Page Header --}}
    <div class="flex justify-between items-center mb-6">
        <flux:heading level="1" size="xl">Manage Roles</flux:heading>
        <flux:modal.trigger name="form" wire:click="openModal">
            <flux:button icon="plus" variant="primary">
                Add Role
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
        <flux:input wire:model.live.debounce.300ms="search" placeholder="Search roles..." icon="magnifying-glass" />
    </div>

    {{-- Roles Table with Responsive Layout --}}
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
                            Permissions
                        </th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-300 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-zinc-900 divide-y divide-zinc-200 dark:divide-zinc-700">
                    @forelse($roles as $role)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">
                            {{ $loop->iteration }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-zinc-900 dark:text-white">
                            {{ $role->name }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">
                            @foreach($role->permissions as $permission)
                                <flux:badge variant="secondary" class="mr-1 mb-1">{{ $permission->name }}</flux:badge>
                            @endforeach
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex justify-end gap-2">
                                {{-- Edit Button --}}
                                <flux:modal.trigger name="form" wire:click="editRole({{ $role->id }})">
                                    <flux:button icon="pencil" variant="filled" size="sm"></flux:button>
                                </flux:modal.trigger>

                                {{-- Delete Button --}}
                                <flux:modal.trigger name="delete" wire:click="confirmDelete({{ $role->id }})">
                                    <flux:button icon="trash" variant="danger" size="sm"></flux:button>
                                </flux:modal.trigger>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-4 text-center text-sm text-zinc-500 dark:text-zinc-400">
                            No roles found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-zinc-50 dark:bg-zinc-800">
                    <tr>
                        <td colspan="4" class="px-4 py-3">
                            {{ $roles->links() }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- Create/Edit Role Modal Form --}}
    <flux:modal name="form" class="min-w-sm md:min-w-lg lg:min-w-xl">
        <flux:heading size="lg" class="mb-4">{{ $isEditing ? 'Edit Role' : 'Add New Role' }}</flux:heading>

        <flux:separator class="mb-4" />

        <form wire:submit="save" class="space-y-6">
            {{-- Name Field --}}
            <flux:field>
                <flux:label>Name</flux:label>
                <flux:input wire:model="name" type="text" />
                <flux:error name="name" />
            </flux:field>

            {{-- Permissions Selection Checkbox Group --}}
            <flux:field>
                <flux:label>Permissions</flux:label>
                <flux:checkbox.group>
                    @foreach($permissions as $permission)
                        <flux:checkbox label="{{ $permission->name }}" id="permission_{{ $permission->id }}" wire:model="selectedPermissions" value="{{ $permission->id }}" />
                    @endforeach
                </flux:checkbox.group>
                <flux:error name="selectedPermissions" />
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
                <flux:heading size="lg">Delete role?</flux:heading>
                <flux:text class="mt-2">
                    <p>You're about to delete this role.</p>
                    <p>This action cannot be reversed.</p>
                </flux:text>
            </div>
            <div class="flex gap-2">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost">Cancel</flux:button>
                </flux:modal.close>
                <flux:button wire:click="deleteRole" variant="danger">Delete Role</flux:button>
            </div>
        </div>
    </flux:modal>

</flux:container>