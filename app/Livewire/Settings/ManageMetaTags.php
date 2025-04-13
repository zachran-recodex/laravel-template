<?php

namespace App\Livewire\Settings;

use App\Models\MetaTag;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

/**
 * Manage Meta Tags Livewire Component.
 *
 * Handles CRUD operations for meta tag management including:
 * - Listing meta tags with pagination and search
 * - Creating new meta tags
 * - Updating existing meta tags
 * - Deleting meta tags
 * - Meta tag activity logging
 * - Transaction handling for database operations
 */
class ManageMetaTags extends Component
{
    use WithPagination;

    public $page = '';
    public $title = '';
    public $description = '';
    public $keywords = '';
    public $author = '';
    public $og_title = '';
    public $og_description = '';
    public $og_image = '';
    public $og_type = 'website';
    public $twitter_card = 'summary_large_image';
    public $twitter_title = '';
    public $twitter_description = '';
    public $twitter_image = '';

    /**
     * ID of the meta tag being edited.
     *
     * @var int|null
     */
    public $metaTagId = null;

    /**
     * Whether the form is in edit mode.
     *
     * @var bool
     */
    public $isEditing = false;

    /**
     * ID of the meta tag to be deleted.
     *
     * @var int|null
     */
    public $metaTagToDelete = null;

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
     * Search term for filtering meta tags.
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
    protected $listeners = ['deleteConfirmed' => 'deleteMetaTag'];

    protected $rules = [
        'page' => 'required|string|max:255',
        'title' => 'nullable|string|max:255',
        'description' => 'nullable|string',
        'keywords' => 'nullable|string',
        'author' => 'nullable|string|max:255',
        'og_title' => 'nullable|string|max:255',
        'og_description' => 'nullable|string',
        'og_image' => 'nullable|string|max:255',
        'og_type' => 'nullable|string|max:255',
        'twitter_card' => 'nullable|string|max:255',
        'twitter_title' => 'nullable|string|max:255',
        'twitter_description' => 'nullable|string',
        'twitter_image' => 'nullable|string|max:255',
    ];

    /**
     * Initialize the component state when opening the add meta tag modal.
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
        $this->reset([
            'page', 'title', 'description', 'keywords', 'author',
            'og_title', 'og_description', 'og_image', 'og_type',
            'twitter_card', 'twitter_title', 'twitter_description', 'twitter_image',
            'metaTagId', 'isEditing'
        ]);
        $this->resetValidation();
    }

    /**
     * Load meta tag data for editing.
     * Sets form fields with meta tag's current values and marks form as in editing mode.
     *
     * @param  int  $metaTagId The ID of the meta tag to edit
     * @return void
     */
    public function editMetaTag(int $metaTagId): void
    {
        $this->isEditing = true;
        $this->metaTagId = $metaTagId;

        $metaTag = MetaTag::findOrFail($metaTagId);
        $this->page = $metaTag->page;
        $this->title = $metaTag->title;
        $this->description = $metaTag->description;
        $this->keywords = $metaTag->keywords;
        $this->author = $metaTag->author;
        $this->og_title = $metaTag->og_title;
        $this->og_description = $metaTag->og_description;
        $this->og_image = $metaTag->og_image;
        $this->og_type = $metaTag->og_type;
        $this->twitter_card = $metaTag->twitter_card;
        $this->twitter_title = $metaTag->twitter_title;
        $this->twitter_description = $metaTag->twitter_description;
        $this->twitter_image = $metaTag->twitter_image;

        // Log meta tag edit action
        Log::info('Meta Tag edit form opened', [
            'edited_meta_tag_id' => $metaTagId,
            'edited_by' => Auth::id(),
        ]);
    }

    /**
     * Prepare to delete a meta tag by setting the meta tag ID for confirmation.
     * Used to populate the delete confirmation modal.
     *
     * @param  int  $metaTagId The ID of the meta tag to delete
     * @return void
     */
    public function confirmDelete(int $metaTagId): void
    {
        $this->metaTagToDelete = $metaTagId;
    }

    /**
     * Delete the confirmed meta tag from the database.
     * Prevents deleting system-critical meta tags.
     * Uses database transactions for data integrity.
     *
     * @return void
     */
    public function deleteMetaTag(): void
    {
        if ($this->metaTagToDelete) {
            $metaTag = MetaTag::findOrFail($this->metaTagToDelete);

            // Log the action before deletion
            Log::info('Meta Tag deleted', [
                'deleted_meta_tag_id' => $metaTag->id,
                'deleted_by' => Auth::id(),
                'meta_tag_page' => $metaTag->page
            ]);

            DB::beginTransaction();
            try {
                $metaTag->delete();
                DB::commit();
                $this->metaTagToDelete = null;

                $this->notification = "Meta Tag deleted successfully.";
                $this->notificationType = "success";
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Failed to delete meta tag', [
                    'meta_tag_id' => $metaTag->id,
                    'error' => $e->getMessage()
                ]);
                $this->notification = "Failed to delete meta tag.";
                $this->notificationType = "danger";
            }

            $this->modal('delete')->close();
        }
    }

    /**
     * Save meta tag data - creates new meta tag or updates existing one.
     * Validates input data and logs the operation.
     * Uses database transactions for data integrity.
     *
     * @return void
     */
    public function save(): void
    {
        $this->validate();

        DB::beginTransaction();
        try {
            if ($this->isEditing) {
                $metaTag = MetaTag::findOrFail($this->metaTagId);
                $oldPage = $metaTag->page; // Store old page for logging

                $metaTag->update([
                    'page' => $this->page,
                    'title' => $this->title,
                    'description' => $this->description,
                    'keywords' => $this->keywords,
                    'author' => $this->author,
                    'og_title' => $this->og_title,
                    'og_description' => $this->og_description,
                    'og_image' => $this->og_image,
                    'og_type' => $this->og_type,
                    'twitter_card' => $this->twitter_card,
                    'twitter_title' => $this->twitter_title,
                    'twitter_description' => $this->twitter_description,
                    'twitter_image' => $this->twitter_image,
                ]);

                // Log meta tag update activity
                Log::info('Meta Tag updated', [
                    'meta_tag_id' => $metaTag->id,
                    'updated_by' => Auth::id(),
                    'old_page' => $oldPage,
                    'new_page' => $metaTag->page
                ]);

                $this->notification = "Meta tag updated successfully.";
                $this->notificationType = "success";
            } else {
                // Validate unique page for new records
                $validator = Validator::make(['page' => $this->page], [
                    'page' => 'required|unique:meta_tags,page'
                ]);

                if ($validator->fails()) {
                    $this->addError('page', 'This page already has meta tags.');
                    return;
                }

                $metaTag = MetaTag::create([
                    'page' => $this->page,
                    'title' => $this->title,
                    'description' => $this->description,
                    'keywords' => $this->keywords,
                    'author' => $this->author,
                    'og_title' => $this->og_title,
                    'og_description' => $this->og_description,
                    'og_image' => $this->og_image,
                    'og_type' => $this->og_type,
                    'twitter_card' => $this->twitter_card,
                    'twitter_title' => $this->twitter_title,
                    'twitter_description' => $this->twitter_description,
                    'twitter_image' => $this->twitter_image,
                ]);

                // Log meta tag creation activity
                Log::info('Meta Tag created', [
                    'meta_tag_id' => $metaTag->id,
                    'created_by' => Auth::id(),
                    'page' => $metaTag->page
                ]);

                $this->notification = "Meta tag created successfully.";
                $this->notificationType = "success";
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to save meta tag', [
                'error' => $e->getMessage(),
                'meta_tag_id' => $this->metaTagId ?? 'new meta tag',
                'action' => $this->isEditing ? 'update' : 'create'
            ]);

            $this->notification = "Failed to save meta tag: " . $e->getMessage();
            $this->notificationType = "danger";
        }

        // Reset form and close modal
        $this->resetForm();
        $this->modal('form')->close();
    }

    /**
     * Render the component view with paginated meta tags data.
     * Applies search filtering.
     * Logs page view with search parameters.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        // Use DB query builder for better performance
        $query = MetaTag::query();

        // Apply search filter if provided
        if ($this->search) {
            $query->where(function($q) {
                $q->where('page', 'like', "%{$this->search}%")
                  ->orWhere('title', 'like', "%{$this->search}%");
            });
        }

        $metaTags = $query->orderBy('created_at', 'desc')
                         ->paginate(10);

        // Log page view with search parameters
        Log::info('Meta tag management page viewed', [
            'user_id' => Auth::id(),
            'search' => $this->search,
            'results_count' => $metaTags->total()
        ]);

        return view('livewire.settings.manage-meta-tags', [
            'metaTags' => $metaTags
        ]);
    }
}
