<?php

namespace App\Livewire\Pages\Orders;

use Illuminate\View\View;
use Livewire\Component;

/**
 * Orders index page component.
 */
class Index extends Component
{
    public function render(): View
    {
        return view('livewire.pages.orders.index')
            ->layout('layouts.public', ['title' => 'Mis Compras']);
    }
}
