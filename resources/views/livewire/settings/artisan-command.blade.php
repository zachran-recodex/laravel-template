<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('Artisan Command')" :subheading="__('Run Laravel Artisan commands from the web interface')">
        <div class="my-6 w-full space-y-6">
            <div class="flex flex-col gap-4">
                <flux:input.group>
                    <flux:input.group.prefix>php artisan</flux:input.group.prefix>
                    <flux:input
                        wire:model="command"
                        type="text"
                        class="w-full"
                    />
                </flux:input.group>

                <div class="flex items-center gap-4">
                    <flux:button
                        wire:click="runCommand"
                        wire:loading.attr="disabled"
                        wire:target="runCommand"
                        variant="primary"
                        icon="code-bracket"
                    >
                        Run Command
                    </flux:button>

                    <flux:button
                        wire:click="clearOutput"
                        variant="primary"
                        icon="trash"
                        wire:loading.attr="disabled"
                        wire:target="runCommand"
                    >
                        Clear Output
                    </flux:button>
                </div>
            </div>

            <div class="mt-4">
                <div class="bg-gray-100 dark:bg-zinc-900 rounded-lg p-4 overflow-auto max-h-[600px]">
                    @if(empty($output))
                        <flux:text class="text-center py-4">Output will appear here</flux:text>
                    @else
                        <pre class="text-xs font-mono whitespace-pre-wrap break-words">{{ $output }}</pre>
                    @endif
                </div>
            </div>
        </div>
    </x-settings.layout>
</section>
