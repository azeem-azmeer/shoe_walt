<?php

namespace App\Livewire\Admin;

use Livewire\Component;

class ProductFilters extends Component
{
    public string $search = '';
    public string $category = '';

    public function mount(): void
    {
        $this->search   = (string) request('search', '');
        $this->category = (string) request('category', '');
    }

    /** Apply current filters by redirecting to the index route with query params */
    public function apply(): void
    {
        $params = ['per_page' => 12];

        if (trim($this->search)   !== '') $params['search']   = trim($this->search);
        if (trim($this->category) !== '') $params['category'] = trim($this->category);

        $this->redirectRoute('admin.products', $params, navigate: true);
    }

    /** When category changes, apply immediately */
    public function updatedCategory(): void
    {
        $this->apply();
    }

    /** Clear both filters */
    public function clear(): void
    {
        $this->reset(['search', 'category']);
        $this->redirectRoute('admin.products', ['per_page' => 12], navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.product-filters');
    }
}
