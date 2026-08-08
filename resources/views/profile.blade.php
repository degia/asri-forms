<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl leading-tight text-primary">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="py-6 sm:py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="glass-card p-4 sm:p-8">
                <div class="max-w-xl">
                    <livewire:profile.update-profile-information-form />
                </div>
            </div>

            <div class="glass-card p-4 sm:p-8">
                <h3 class="text-lg font-semibold text-primary mb-4">Tanda Tangan</h3>
                <div class="max-w-xl">
                    <livewire:profile.user-signature />
                </div>
            </div>

            <div class="glass-card p-4 sm:p-8">
                <div class="max-w-xl">
                    <livewire:profile.update-password-form />
                </div>
            </div>

            <div class="glass-card p-4 sm:p-8">
                <div class="max-w-xl">
                    <livewire:profile.delete-user-form />
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
