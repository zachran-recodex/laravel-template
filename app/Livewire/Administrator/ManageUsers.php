<?php

namespace App\Livewire\Administrator;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

/**
 * Manage Users Livewire Component.
 *
 * Handles CRUD operations for user management including:
 * - Listing users with pagination and search
 * - Creating new users
 * - Updating existing users
 * - Deleting users
 * - Assigning roles to users
 * - User activity logging
 * - Transaction handling for database operations
 */
class ManageUsers extends Component
{
    use WithPagination;

    /**
     * Form field for user's name.
     *
     * @var string
     */
    public $name = '';

    /**
     * Form field for user's email.
     *
     * @var string
     */
    public $email = '';

    /**
     * Form field for user's password.
     *
     * @var string
     */
    public $password = '';

    /**
     * Form field for password confirmation.
     *
     * @var string
     */
    public $password_confirmation = '';

    /**
     * Selected role IDs for the user.
     *
     * @var array<int>
     */
    public $selectedRoles = [];

    /**
     * ID of the user being edited.
     *
     * @var int|null
     */
    public $userId = null;

    /**
     * Whether the form is in edit mode.
     *
     * @var bool
     */
    public $isEditing = false;

    /**
     * ID of the user to be deleted.
     *
     * @var int|null
     */
    public $userToDelete = null;

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
     * Search term for filtering users.
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
    protected $listeners = ['deleteConfirmed' => 'deleteUser'];

    /**
     * Initialize the component state when opening the add user modal.
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
        $this->reset(['name', 'email', 'password', 'password_confirmation', 'selectedRoles', 'userId', 'isEditing']);
        $this->resetValidation();
    }

    /**
     * Load user data for editing.
     * Sets form fields with user's current values and marks form as in editing mode.
     *
     * @param  int  $userId The ID of the user to edit
     * @return void
     */
    public function editUser(int $userId): void
    {
        $this->isEditing = true;
        $this->userId = $userId;

        $user = User::findOrFail($userId);
        $this->name = $user->name;
        $this->email = $user->email;
        $this->selectedRoles = $user->roles->pluck('id')->toArray();

        // Log user edit action
        Log::info('User edit form opened', [
            'edited_user_id' => $userId,
            'edited_by' => Auth::id(),
            'roles' => $user->roles->pluck('name')->toArray()
        ]);
    }

    /**
     * Prepare to delete a user by setting the user ID for confirmation.
     * Used to populate the delete confirmation modal.
     *
     * @param  int  $userId The ID of the user to delete
     * @return void
     */
    public function confirmDelete(int $userId): void
    {
        $this->userToDelete = $userId;
    }

    /**
     * Delete the confirmed user from the database.
     * Prevents users from deleting their own account.
     * Uses database transactions for data integrity.
     *
     * @return void
     */
    public function deleteUser(): void
    {
        if ($this->userToDelete) {
            $user = User::findOrFail($this->userToDelete);

            // Prevent deleting your own account
            if (Auth::user()->id === $user->id) {
                $this->notification = "You cannot delete your own account.";
                $this->notificationType = "warning";
                Log::warning('User attempted to delete their own account', [
                    'user_id' => Auth::id(),
                    'action' => 'delete_user'
                ]);
                return;
            }

            // Log the action before deletion
            Log::info('User deleted', [
                'deleted_user_id' => $user->id,
                'deleted_by' => Auth::id(),
                'user_email' => $user->email
            ]);

            DB::beginTransaction();
            try {
                $user->delete();
                DB::commit();
                $this->userToDelete = null;

                $this->notification = "User deleted successfully.";
                $this->notificationType = "success";
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Failed to delete user', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage()
                ]);
                $this->notification = "Failed to delete user.";
                $this->notificationType = "danger";
            }

            $this->modal('delete')->close();
        }
    }

    /**
     * Save user data - creates new user or updates existing one.
     * Validates input data, handles password updates conditionally,
     * assigns roles, and logs the operation.
     * Uses database transactions for data integrity.
     *
     * @return void
     */
    public function save(): void
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users')->ignore($this->userId),
            ],
            'password' => $this->isEditing
                ? 'nullable|min:8|confirmed'
                : 'required|min:8|confirmed',
        ]);

        DB::beginTransaction();
        try {
            if ($this->isEditing) {
                $user = User::findOrFail($this->userId);
                $oldEmail = $user->email; // Store old email for logging
                $user->name = $this->name;
                $user->email = $this->email;

                if ($this->password) {
                    $user->password = Hash::make($this->password);
                }

                $user->save();

                // Sync roles - using role objects instead of IDs
                $roles = Role::whereIn('id', $this->selectedRoles)->get();
                $user->syncRoles($roles);

                // Log user update activity
                Log::info('User updated', [
                    'user_id' => $user->id,
                    'updated_by' => Auth::id(),
                    'old_email' => $oldEmail,
                    'new_email' => $user->email,
                    'roles' => $roles->pluck('name')->toArray()
                ]);

                $this->notification = "User updated successfully.";
                $this->notificationType = "success";
            } else {
                $user = User::create([
                    'name' => $this->name,
                    'email' => $this->email,
                    'password' => Hash::make($this->password),
                ]);

                // Assign roles - using role objects instead of IDs
                $roles = Role::whereIn('id', $this->selectedRoles)->get();
                $user->syncRoles($roles);

                // Log user creation activity
                Log::info('User created', [
                    'user_id' => $user->id,
                    'created_by' => Auth::id(),
                    'email' => $user->email,
                    'roles' => $roles->pluck('name')->toArray()
                ]);

                $this->notification = "User created successfully.";
                $this->notificationType = "success";
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to save user', [
                'error' => $e->getMessage(),
                'user_id' => $this->userId ?? 'new user',
                'action' => $this->isEditing ? 'update' : 'create'
            ]);

            $this->notification = "Failed to save user: " . $e->getMessage();
            $this->notificationType = "danger";
        }

        // Reset form and close modal
        $this->resetForm();
        $this->modal('form')->close();
    }

    /**
     * Get all available roles for the user assignment form.
     * Made available as a computed property.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, Role>
     */
    public function getRolesProperty()
    {
        return Role::all();
    }

    /**
     * Render the component view with paginated users and roles data.
     * Applies search filtering and eager loads role relationships.
     * Logs page view with search parameters.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        // Use DB query builder with proper eager loading for better performance
        $query = User::query();

        // Apply search filter if provided
        if ($this->search) {
            $query->where(function($q) {
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('email', 'like', "%{$this->search}%");
            });
        }

        // Get users with roles using eager loading
        $users = $query->with('roles')
                      ->orderBy('created_at', 'desc')
                      ->paginate(10);

        // Log page view with search parameters
        Log::info('User management page viewed', [
            'user_id' => Auth::id(),
            'search' => $this->search,
            'results_count' => $users->total()
        ]);

        return view('livewire.administrator.manage-users', [
            'users' => $users,
            'roles' => $this->getRolesProperty(),
        ]);
    }
}
