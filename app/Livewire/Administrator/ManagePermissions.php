<?php

namespace App\Livewire\Administrator;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Permission;

/**
 * Manage Permissions Livewire Component.
 *
 * Handles CRUD operations for permission management including:
 * - Listing permissions with pagination and search
 * - Creating new permissions
 * - Updating existing permissions
 * - Deleting permissions
 * - Permission activity logging
 * - Transaction handling for database operations
 */
class ManagePermissions extends Component
{
    use WithPagination;

    /**
     * Form field for permission's name.
     *
     * @var string
     */
    public $name = '';

    /**
     * ID of the permission being edited.
     *
     * @var int|null
     */
    public $permissionId = null;

    /**
     * Whether the form is in edit mode.
     *
     * @var bool
     */
    public $isEditing = false;

    /**
     * ID of the permission to be deleted.
     *
     * @var int|null
     */
    public $permissionToDelete = null;

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
     * Search term for filtering permissions.
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
    protected $listeners = ['deleteConfirmed' => 'deletePermission'];

    /**
     * Initialize the component state when opening the add permission modal.
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
        $this->reset(['name', 'permissionId', 'isEditing']);
        $this->resetValidation();
    }

    /**
     * Load permission data for editing.
     * Sets form fields with permission's current values and marks form as in editing mode.
     *
     * @param  int  $permissionId The ID of the permission to edit
     * @return void
     */
    public function editPermission(int $permissionId): void
    {
        $this->isEditing = true;
        $this->permissionId = $permissionId;

        $permission = Permission::findOrFail($permissionId);
        $this->name = $permission->name;

        // Log permission edit action
        Log::info('Permission edit form opened', [
            'edited_permission_id' => $permissionId,
            'edited_by' => Auth::id(),
        ]);
    }

    /**
     * Prepare to delete a permission by setting the permission ID for confirmation.
     * Used to populate the delete confirmation modal.
     *
     * @param  int  $permissionId The ID of the permission to delete
     * @return void
     */
    public function confirmDelete(int $permissionId): void
    {
        $this->permissionToDelete = $permissionId;
    }

    /**
     * Delete the confirmed permission from the database.
     * Prevents deleting system-critical permissions.
     * Uses database transactions for data integrity.
     *
     * @return void
     */
    public function deletePermission(): void
    {
        if ($this->permissionToDelete) {
            $permission = Permission::findOrFail($this->permissionToDelete);

            // Prevent deleting core permissions
            $corePermissions = ['manage users', 'manage roles', 'manage permissions'];
            if (in_array($permission->name, $corePermissions)) {
                $this->notification = "You cannot delete core system permissions.";
                $this->notificationType = "warning";
                Log::warning('User attempted to delete core permission', [
                    'user_id' => Auth::id(),
                    'permission_name' => $permission->name,
                    'action' => 'delete_permission'
                ]);
                return;
            }

            // Log the action before deletion
            Log::info('Permission deleted', [
                'deleted_permission_id' => $permission->id,
                'deleted_by' => Auth::id(),
                'permission_name' => $permission->name
            ]);

            DB::beginTransaction();
            try {
                $permission->delete();
                DB::commit();
                $this->permissionToDelete = null;

                $this->notification = "Permission deleted successfully.";
                $this->notificationType = "success";
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Failed to delete permission', [
                    'permission_id' => $permission->id,
                    'error' => $e->getMessage()
                ]);
                $this->notification = "Failed to delete permission.";
                $this->notificationType = "danger";
            }

            $this->modal('delete')->close();
        }
    }

    /**
     * Save permission data - creates new permission or updates existing one.
     * Validates input data and logs the operation.
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
                Rule::unique('permissions')->ignore($this->permissionId),
            ],
        ]);

        DB::beginTransaction();
        try {
            if ($this->isEditing) {
                $permission = Permission::findOrFail($this->permissionId);
                $oldName = $permission->name; // Store old name for logging
                $permission->name = $this->name;
                $permission->save();

                // Log permission update activity
                Log::info('Permission updated', [
                    'permission_id' => $permission->id,
                    'updated_by' => Auth::id(),
                    'old_name' => $oldName,
                    'new_name' => $permission->name,
                ]);

                $this->notification = "Permission updated successfully.";
                $this->notificationType = "success";
            } else {
                $permission = Permission::create([
                    'name' => $this->name,
                    'guard_name' => 'web', // Default guard
                ]);

                // Log permission creation activity
                Log::info('Permission created', [
                    'permission_id' => $permission->id,
                    'created_by' => Auth::id(),
                    'name' => $permission->name,
                ]);

                $this->notification = "Permission created successfully.";
                $this->notificationType = "success";
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to save permission', [
                'error' => $e->getMessage(),
                'permission_id' => $this->permissionId ?? 'new permission',
                'action' => $this->isEditing ? 'update' : 'create'
            ]);

            $this->notification = "Failed to save permission: " . $e->getMessage();
            $this->notificationType = "danger";
        }

        // Reset form and close modal
        $this->resetForm();
        $this->modal('form')->close();
    }

    /**
     * Render the component view with paginated permissions data.
     * Applies search filtering.
     * Logs page view with search parameters.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        // Use DB query builder for better performance
        $query = Permission::query();

        // Apply search filter if provided
        if ($this->search) {
            $query->where('name', 'like', "%{$this->search}%");
        }

        // Get permissions
        $permissions = $query->orderBy('created_at', 'desc')
                           ->paginate(10);

        // Log page view with search parameters
        Log::info('Permission management page viewed', [
            'user_id' => Auth::id(),
            'search' => $this->search,
            'results_count' => $permissions->total()
        ]);

        return view('livewire.administrator.manage-permissions', [
            'permissions' => $permissions,
        ]);
    }
}
