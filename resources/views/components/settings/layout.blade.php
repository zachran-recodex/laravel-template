<div class="flex items-start max-md:flex-col">
    <div class="me-10 w-full pb-4 md:w-[220px]">
        <flux:navlist>
            <flux:navlist.item :href="route('settings.profile')" wire:navigate>{{ __('Profile') }}</flux:navlist.item>
            <flux:navlist.item :href="route('settings.password')" wire:navigate>{{ __('Password') }}</flux:navlist.item>
            <flux:navlist.item :href="route('settings.appearance')" wire:navigate>{{ __('Appearance') }}</flux:navlist.item>
            <flux:navlist.item :href="route('settings.meta-tags')" wire:navigate>{{ __('Meta Tags') }}</flux:navlist.item>
            <flux:navlist.item :href="route('settings.view-log')" wire:navigate>{{ __('View Log') }}</flux:navlist.item>
            <flux:navlist.item :href="route('settings.user-activity')" wire:navigate>{{ __('User Activity') }}</flux:navlist.item>
            <flux:navlist.item :href="route('settings.artisan-command')" wire:navigate>{{ __('Artisan Command') }}</flux:navlist.item>
        </flux:navlist>
    </div>

    <flux:separator class="md:hidden" />

    <div class="flex-1 self-stretch max-md:pt-6">
        <flux:heading>{{ $heading ?? '' }}</flux:heading>
        <flux:subheading>{{ $subheading ?? '' }}</flux:subheading>

        <div class="mt-5 w-full max-w-full">
            {{ $slot }}
        </div>
    </div>
</div>
