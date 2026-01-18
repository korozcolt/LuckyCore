<?php

use App\Concerns\PasswordValidationRules;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

new class extends Component {
    use PasswordValidationRules;

    public string $current_password = '';
    public string $password = '';
    public string $password_confirmation = '';

    /**
     * Update the password for the currently authenticated user.
     */
    public function updatePassword(): void
    {
        try {
            $validated = $this->validate([
                'current_password' => $this->currentPasswordRules(),
                'password' => $this->passwordRules(),
            ]);
        } catch (ValidationException $e) {
            $this->reset('current_password', 'password', 'password_confirmation');

            throw $e;
        }

        Auth::user()->update([
            'password' => $validated['password'],
        ]);

        $this->reset('current_password', 'password', 'password_confirmation');

        $this->dispatch('password-updated');
    }
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <flux:heading class="sr-only">Configuración de contraseña</flux:heading>

    <x-pages::settings.layout heading="Contraseña" subheading="Actualiza tu contraseña para mantener tu cuenta segura.">
        <form method="POST" wire:submit="updatePassword" class="mt-6 space-y-6">
            <flux:input
                wire:model="current_password"
                label="Contraseña actual"
                type="password"
                required
                autocomplete="current-password"
                class="w-full"
            />
            <flux:input
                wire:model="password"
                label="Nueva contraseña"
                type="password"
                required
                autocomplete="new-password"
                class="w-full"
            />
            <flux:input
                wire:model="password_confirmation"
                label="Confirmar contraseña"
                type="password"
                required
                autocomplete="new-password"
                class="w-full"
            />

            <div class="flex items-center gap-4">
                <div class="flex items-center justify-end">
                    <flux:button variant="primary" type="submit" class="w-full" data-test="update-password-button">
                        Guardar
                    </flux:button>
                </div>

                <x-action-message class="me-3" on="password-updated">
                    Guardado.
                </x-action-message>
            </div>
        </form>
    </x-pages::settings.layout>
</section>
