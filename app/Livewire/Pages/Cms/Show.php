<?php

namespace App\Livewire\Pages\Cms;

use App\Models\CmsPage;
use Illuminate\View\View;
use Livewire\Component;

/**
 * CMS page display component.
 *
 * @see PANTALLAS.md §A3 - Acordeones: Terminos / Caracteristicas / FAQ
 * @see ALCANCE.md §3 - CMS: Como funciona, Terminos, FAQ
 */
class Show extends Component
{
    public CmsPage $page;

    public function mount(string $slug): void
    {
        $this->page = CmsPage::where('slug', $slug)
            ->where('is_published', true)
            ->firstOrFail();
    }

    public function render(): View
    {
        return view('livewire.pages.cms.show')
            ->layout('layouts.public', ['title' => $this->page->title]);
    }
}
