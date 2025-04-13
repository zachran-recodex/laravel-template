<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('View Log')" :subheading="__('View and manage system logs')">
        <div class="my-6 w-full space-y-6">
            <div class="flex flex-col md:flex-row gap-4 justify-between">
                <flux:input
                    wire:model.live="searchTerm"
                    type="text"
                    placeholder="Search in logs..."
                    class="w-fit!"
                />

                <div class="flex items-center gap-4">
                    <flux:button
                        wire:click="readLog"
                        variant="primary"
                        icon="arrow-path"
                    >
                        Refresh
                    </flux:button>

                    <flux:button
                        wire:click="clearLog"
                        variant="danger"
                        icon="trash"
                        wire:confirm="Are you sure you want to clear the log file? This action cannot be undone."
                    >
                        Clear Log
                    </flux:button>
                </div>
            </div>

            <div class="mt-4">
                <div class="bg-gray-100 dark:bg-zinc-900 rounded-lg p-4 overflow-auto max-h-[600px]">
                    @if(empty($logContent))
                        <flux:text class="text-center py-4">No log entries found.</flux:text>
                    @else
                        <pre class="text-xs font-mono whitespace-pre-wrap break-words">{{ $logContent }}</pre>
                    @endif
                </div>
            </div>
        </div>
    </x-settings.layout>
</section>
