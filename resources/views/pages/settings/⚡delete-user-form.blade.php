<?php

use App\Concerns\PasswordValidationRules;
use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

new class extends Component {
    use PasswordValidationRules;

    public string $password = '';

    /**
     * Delete the currently authenticated user.
     */
    public function deleteUser(Logout $logout): void
    {
        $this->validate([
            'password' => $this->currentPasswordRules(),
        ]);

        tap(Auth::user(), $logout(...))->delete();

        $this->redirect('/', navigate: true);
    }
}; ?>

<section class="mt-10 space-y-6">
    <div class="relative mb-5">
        <flux:heading>Eliminar cuenta</flux:heading>
        <flux:subheading>Elimina tu cuenta y todos sus datos asociados.</flux:subheading>
    </div>

    <flux:modal.trigger name="confirm-user-deletion">
        <flux:button variant="danger" x-data="" x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')" data-test="delete-user-button">
            Eliminar cuenta
        </flux:button>
    </flux:modal.trigger>

    <flux:modal name="confirm-user-deletion" :show="$errors->isNotEmpty()" focusable class="max-w-lg">
        <form method="POST" wire:submit="deleteUser" class="space-y-6">
            <div>
                <flux:heading size="lg">¿Seguro que quieres eliminar tu cuenta?</flux:heading>

                <flux:subheading>
                    Una vez eliminada, todos tus datos se borrarán de forma permanente. Ingresa tu contraseña para confirmar.
                </flux:subheading>
            </div>

            <flux:input wire:model="password" label="Contraseña" type="password" class="w-full" />

            <div class="flex justify-end space-x-2 rtl:space-x-reverse">
                <flux:modal.close>
                    <flux:button variant="filled">Cancelar</flux:button>
                </flux:modal.close>

                <flux:button variant="danger" type="submit" data-test="confirm-delete-user-button">
                    Eliminar cuenta
                </flux:button>
            </div>
        </form>
    </flux:modal>
</section>
