<?php

namespace App\Livewire\Administrator;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Manage Roles Livewire Component.
 *
 * Handles CRUD operations for role management including:
 * - Listing roles with pagination and search
 * - Creating new roles
 * - Updating existing roles
 * - Deleting roles
 * - Assigning permissions to roles
 * - Role activity logging
 * - Transaction handling for database operations
 */
class ManageRoles extends Component
{
    use WithPagination;

    /**
     * Form field for role's name.
     *
     * @var string
     */
    public $name = '';

    /**
     * Selected permission IDs for the role.
     *
     * @var array<int>
     */
    public $selectedPermissions = [];

    /**
     * ID of the role being edited.
     *
     * @var int|null
     */
    public $roleId = null;

    /**
     * Whether the form is in edit mode.
     *
     * @var bool
     */
    public $isEditing = false;

    /**
     * ID of the role to be deleted.
     *
     * @var int|null
     */
    public $roleToDelete = null;

    /**
     * Notification message to display to the user.
     *
     * @var string|null
     */
    public $notification = null;

    /**
     * Type of notification: success, warning, danger, or secondary.
     *
     * @var string|null
     */
    public $notificationType = null;

    /**
     * Search term for filtering roles.
     *
     * @var string
     */
    public $search = '';

    /**
     * Define URL query parameters that persist in the URL.
     *
     * @var array<string, array<string, string>>
     */
    protected $queryString = ['search' => ['except' => '']];

    /**
     * Define the event listeners for the component.
     *
     * @var array<string, string>
     */
    protected $listeners = ['deleteConfirmed' => 'deleteRole'];

    /**
     * Initialize the component state when opening the add role modal.
     * Resets all form fields to their default values.
     *
     * @return void
     */
    public function openModal(): void
    {
        $this->resetForm();
    }

    /**
     * Reset all form fields and validation errors.
     *
     * @return void
     */
    public function resetForm(): void
    {
        $this->reset(['name', 'selectedPermissions', 'roleId', 'isEditing']);
        $this->resetValidation();
    }

    /**
     * Load role data for editing.
     * Sets form fields with role's current values and marks form as in editing mode.
     *
     * @param  int  $roleId The ID of the role to edit
     * @return void
     */
    public function editRole(int $roleId): void
    {
        $this->isEditing = true;
        $this->roleId = $roleId;

        $role = Role::findOrFail($roleId);
        $this->name = $role->name;
        $this->selectedPermissions = $role->permissions->pluck('id')->toArray();

        // Log role edit action
        Log::info('Role edit form opened', [
            'edited_role_id' => $roleId,
            'edited_by' => Auth::id(),
            'permissions' => $role->permissions->pluck('name')->toArray()
        ]);
    }

    /**
     * Prepare to delete a role by setting the role ID for confirmation.
     * Used to populate the delete confirmation modal.
     *
     * @param  int  $roleId The ID of the role to delete
     * @return void
     */
    public function confirmDelete(int $roleId): void
    {
        $this->roleToDelete = $roleId;
    }

    /**
     * Delete the confirmed role from the database.
     * Prevents deleting system-critical roles.
     * Uses database transactions for data integrity.
     *
     * @return void
     */
    public function deleteRole(): void
    {
        if ($this->roleToDelete) {
            $role = Role::findOrFail($this->roleToDelete);

            // Prevent deleting super-admin role
            if ($role->name === 'super-admin') {
                $this->notification = "You cannot delete the super-admin role.";
                $this->notificationType = "warning";
                Log::warning('User attempted to delete super-admin role', [
                    'user_id' => Auth::id(),
                    'action' => 'delete_role'
                ]);
                return;
            }

            // Log the action before deletion
            Log::info('Role deleted', [
                'deleted_role_id' => $role->id,
                'deleted_by' => Auth::id(),
                'role_name' => $role->name
            ]);

            DB::beginTransaction();
            try {
                $role->delete();
                DB::commit();
                $this->roleToDelete = null;

                $this->notification = "Role deleted successfully.";
                $this->notificationType = "success";
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Failed to delete role', [
                    'role_id' => $role->id,
                    'error' => $e->getMessage()
                ]);
                $this->notification = "Failed to delete role.";
                $this->notificationType = "danger";
            }

            $this->modal('delete')->close();
        }
    }

    /**
     * Save role data - creates new role or updates existing one.
     * Validates input data, assigns permissions, and logs the operation.
     * Uses database transactions for data integrity.
     *
     * @return void
     */
    public function save(): void
    {
        $this->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('roles')->ignore($this->roleId),
            ],
        ]);

        DB::beginTransaction();
        try {
            if ($this->isEditing) {
                $role = Role::findOrFail($this->roleId);
                $oldName = $role->name; // Store old name for logging
                $role->name = $this->name;
                $role->save();

                // Sync permissions - using permission objects instead of IDs
                $permissions = Permission::whereIn('id', $this->selectedPermissions)->get();
                $role->syncPermissions($permissions);

                // Log role update activity
                Log::info('Role updated', [
                    'role_id' => $role->id,
                    'updated_by' => Auth::id(),
                    'old_name' => $oldName,
                    'new_name' => $role->name,
                    'permissions' => $permissions->pluck('name')->toArray()
                ]);

                $this->notification = "Role updated successfully.";
                $this->notificationType = "success";
            } else {
                $role = Role::create([
                    'name' => $this->name,
                ]);

                // Assign permissions - using permission objects instead of IDs
                $permissions = Permission::whereIn('id', $this->selectedPermissions)->get();
                $role->syncPermissions($permissions);

                // Log role creation activity
                Log::info('Role created', [
                    'role_id' => $role->id,
                    'created_by' => Auth::id(),
                    'name' => $role->name,
                    'permissions' => $permissions->pluck('name')->toArray()
                ]);

                $this->notification = "Role created successfully.";
                $this->notificationType = "success";
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to save role', [
                'error' => $e->getMessage(),
                'role_id' => $this->roleId ?? 'new role',
                'action' => $this->isEditing ? 'update' : 'create'
            ]);

            $this->notification = "Failed to save role: " . $e->getMessage();
            $this->notificationType = "danger";
        }

        // Reset form and close modal
        $this->resetForm();
        $this->modal('form')->close();
    }

    /**
     * Get all available permissions for the role assignment form.
     * Made available as a computed property.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, Permission>
     */
    public function getPermissionsProperty()
    {
        return Permission::all();
    }

    /**
     * Render the component view with paginated roles and permissions data.
     * Applies search filtering and eager loads permission relationships.
     * Logs page view with search parameters.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        // Use DB query builder with proper eager loading for better performance
        $query = Role::query();

        // Apply search filter if provided
        if ($this->search) {
            $query->where('name', 'like', "%{$this->search}%");
        }

        // Get roles with permissions using eager loading
        $roles = $query->with('permissions')
                      ->orderBy('created_at', 'desc')
                      ->paginate(10);

        // Log page view with search parameters
        Log::info('Role management page viewed', [
            'user_id' => Auth::id(),
            'search' => $this->search,
            'results_count' => $roles->total()
        ]);

        return view('livewire.administrator.manage-roles', [
            'roles' => $roles,
            'permissions' => $this->getPermissionsProperty(),
        ]);
    }
}