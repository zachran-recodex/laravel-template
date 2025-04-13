<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('Meta Tags')" :subheading="__('Manage your website meta tags to improve SEO and social sharing')">
        <div class="mt-6 space-y-6">

            {{-- Notification Area - Auto-dismisses after 5 seconds --}}
            @if($notification)
            <div x-data="{shown: true}" x-init="setTimeout(() => {shown = false; $wire.set('notification', null);}, 5000)" x-show="shown">
                <flux:callout variant="{{ $notificationType }}"
                             icon="{{ $notificationType === 'success' ? 'check-circle' :
                                    ($notificationType === 'warning' ? 'exclamation-circle' :
                                    ($notificationType === 'danger' ? 'x-circle' : 'information-circle')) }}"
                             heading="{{ $notification }}" />
            </div>
            @endif

            {{-- Search Box with Live Debounce --}}
            <div class="mb-6 flex justify-between">
                <flux:input class="w-fit!" wire:model.live.debounce.300ms="search" placeholder="Search pages..." icon="magnifying-glass" />

                <flux:modal.trigger name="form" wire:click="openModal">
                    <flux:button icon="plus" variant="primary">
                        Add New
                    </flux:button>
                </flux:modal.trigger>
            </div>

            <div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-lg shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                        <thead class="bg-zinc-50 dark:bg-zinc-800">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-300 uppercase tracking-wider">
                                    No
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-300 uppercase tracking-wider">
                                    Page
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-300 uppercase tracking-wider">
                                    Title
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Description
                                </th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-300 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-zinc-900 divide-y divide-zinc-200 dark:divide-zinc-700">
                            @forelse ($metaTags as $metaTag)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">
                                    {{ $loop->iteration }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-zinc-900 dark:text-white">
                                    {{ $metaTag->page }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">
                                    {{ $metaTag->title }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-300 truncate max-w-xs">
                                    {{ $metaTag->description }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex justify-end gap-2">
                                        {{-- Edit Button --}}
                                        <flux:modal.trigger name="form" wire:click="editMetaTag({{ $metaTag->id }})">
                                            <flux:button icon="pencil" variant="filled" size="sm"></flux:button>
                                        </flux:modal.trigger>

                                        {{-- Delete Button --}}
                                        <flux:modal.trigger name="delete" wire:click="confirmDelete({{ $metaTag->id }})">
                                            <flux:button icon="trash" variant="danger" size="sm"></flux:button>
                                        </flux:modal.trigger>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-sm text-zinc-500 dark:text-zinc-400">
                                    No meta tags found.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="bg-zinc-50 dark:bg-zinc-800">
                            <tr>
                                <td colspan="5" class="px-4 py-3">
                                    {{ $metaTags->links() }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            {{-- Create/Edit Meta Tag Modal Form --}}
            <flux:modal name="form" class="min-w-xl md:min-w-2xl lg:min-w-4xl">
                <flux:heading size="lg" class="mb-4">{{ $isEditing ? 'Edit Meta Tag' : 'Add New Meta Tag' }}</flux:heading>

                <flux:separator class="mb-4" />

                <form wire:submit="save" class="space-y-6">
                    <flux:fieldset>
                        <flux:legend>Meta Tags</flux:legend>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <flux:field class="col-span-2">
                                <flux:label>Page</flux:label>
                                <flux:input wire:model="page" type="text" placeholder="Contoh: home, about, contact" />
                                <flux:error name="page" />
                            </flux:field>

                            <flux:field>
                                <flux:label>Title</flux:label>
                                <flux:input wire:model="title" type="text" />
                                <flux:error name="title" />
                            </flux:field>

                            <flux:field>
                                <flux:label>Author</flux:label>
                                <flux:input wire:model="author" type="text" />
                                <flux:error name="author" />
                            </flux:field>

                            <flux:field class="col-span-2">
                                <flux:label>Description</flux:label>
                                <flux:textarea wire:model="description" rows="2" />
                                <flux:error name="description" />
                            </flux:field>

                            <flux:field class="col-span-2">
                                <flux:label>Keywords</flux:label>
                                <flux:textarea wire:model="keywords" rows="2" />
                                <flux:error name="keywords" />
                            </flux:field>
                        </div>
                    </flux:fieldset>

                    <flux:separator />

                    <flux:fieldset>
                        <flux:legend>Open Graph Meta Tags</flux:legend>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <flux:field class="col-span-2">
                                <flux:label>OG Title</flux:label>
                                <flux:input wire:model="og_title" type="text" />
                                <flux:error name="og_title" />
                            </flux:field>

                            <flux:field class="col-span-2">
                                <flux:label>OG Description</flux:label>
                                <flux:textarea wire:model="og_description" rows="2" />
                                <flux:error name="og_description" />
                            </flux:field>

                            <flux:field>
                                <flux:label>OG Image URL</flux:label>
                                <flux:input wire:model="og_image" type="text" />
                                <flux:error name="og_image" />
                            </flux:field>

                            <flux:field>
                                <flux:label>OG Type</flux:label>
                                <flux:input wire:model="og_type" type="text" />
                                <flux:error name="og_type" />
                            </flux:field>
                        </div>
                    </flux:fieldset>

                    <flux:separator />

                    <flux:fieldset>
                        <flux:legend>Twitter Meta Tags</flux:legend>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <flux:field class="col-span-2">
                                <flux:label>Twitter Title</flux:label>
                                <flux:input wire:model="twitter_title" type="text" />
                                <flux:error name="twitter_title" />
                            </flux:field>

                            <flux:field class="col-span-2">
                                <flux:label>Twitter Description</flux:label>
                                <flux:textarea wire:model="twitter_description" rows="2" />
                                <flux:error name="twitter_description" />
                            </flux:field>

                            <flux:field>
                                <flux:label>Twitter Image URL</flux:label>
                                <flux:input wire:model="twitter_image" type="text" />
                                <flux:error name="twitter_imagetwitter_image" />
                            </flux:field>

                            <flux:field>
                                <flux:label>Twitter Card</flux:label>
                                <flux:input wire:model="twitter_card" type="text" />
                                <flux:error name="twitter_card" />
                            </flux:field>
                        </div>
                    </flux:fieldset>

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
                        <flux:heading size="lg">Delete meta tag?</flux:heading>
                        <flux:text class="mt-2">
                            <p>You're about to delete this meta tag.</p>
                            <p>This action cannot be reversed.</p>
                        </flux:text>
                    </div>
                    <div class="flex gap-2">
                        <flux:spacer />
                        <flux:modal.close>
                            <flux:button variant="ghost">Cancel</flux:button>
                        </flux:modal.close>
                        <flux:button wire:click="deleteMetaTag" variant="danger">Delete</flux:button>
                    </div>
                </div>
            </flux:modal>
        </div>
    </x-settings.layout>

</section>
