<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('Profile')" :subheading="__('Update your name and email address')">
        <form wire:submit="updateProfileInformation" class="my-6 w-full space-y-6">
            <flux:input wire:model="name" :label="__('Name')" type="text" required autofocus autocomplete="name" />

            <div>
                <flux:input wire:model="email" :label="__('Email')" type="email" required autocomplete="email" />

                @if (auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail &&! auth()->user()->hasVerifiedEmail())
                    <div>
                        <flux:text class="mt-4">
                            {{ __('Your email address is unverified.') }}

                            <flux:link class="text-sm cursor-pointer" wire:click.prevent="resendVerificationNotification">
                                {{ __('Click here to re-send the verification email.') }}
                            </flux:link>
                        </flux:text>

                        @if (session('status') === 'verification-link-sent')
                            <flux:text class="mt-2 font-medium !dark:text-green-400 !text-green-600">
                                {{ __('A new verification link has been sent to your email address.') }}
                            </flux:text>
                        @endif
                    </div>
                @endif
            </div>

            <div class="flex items-center gap-4">
                <div class="flex items-center justify-end">
                    <flux:button variant="primary" type="submit" class="w-full">{{ __('Save') }}</flux:button>
                </div>

                <x-action-message class="me-3" on="profile-updated">
                    {{ __('Saved.') }}
                </x-action-message>
            </div>
        </form>

        <!-- Session Information Section -->
        <div class="mt-8 border-t pt-6">
            <h3 class="text-lg font-medium">{{ __('Session Information') }}</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">{{ __('Your current session details') }}</p>

            @if ($userSession)
                <div class="space-y-4">
                    <div>
                        <flux:text class="font-medium">{{ __('IP Address') }}:</flux:text>
                        <flux:text>{{ $ipAddress }}</flux:text>
                    </div>

                    <div>
                        <flux:text class="font-medium">{{ __('User Agent') }}:</flux:text>
                        <flux:text class="break-all">{{ $userAgent }}</flux:text>
                    </div>

                    <div>
                        <flux:text class="font-medium">{{ __('Last Activity') }}:</flux:text>
                        <flux:text>{{ $lastActivity }}</flux:text>
                    </div>
                </div>
            @else
                <flux:text>{{ __('No session information available.') }}</flux:text>
            @endif
        </div>

        <livewire:settings.delete-user-form />
    </x-settings.layout>
</section>
