<?php

namespace App\Livewire\User;

use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Filters extends Component
{
    public string $category = 'Men';
    public string $size = '';
    public string $sort = '';

    /** Sizes to show as chips */
    public array $sizes = [];

    public function mount(string $category = 'Men', string $size = '', string $sort = ''): void
    {
        $this->category = $category;
        $this->size     = $size;
        $this->sort     = $sort;

        $this->sizes = $this->loadSizes();
    }

    private function loadSizes(): array
    {
        $cat = mb_strtolower($this->category);

        return DB::table('product_sizes as ps')
            ->join('products as p', 'p.product_id', '=', 'ps.product_id')
            ->where('p.status', 'Active')
            ->whereRaw('LOWER(p.category) = ?', [$cat])
            ->where('ps.qty', '>', 0)
            ->distinct()
            ->orderByRaw('CAST(ps.size AS UNSIGNED)')
            ->pluck('ps.size')
            ->map(fn ($s) => (string) $s)
            ->values()
            ->all();
    }

    public function render()
    {
        // re-pull sizes in case category changes
        $this->sizes = $this->loadSizes();

        return view('livewire.user.filters');
    }
    public function setSort($sort = ''): void
    {
        $this->sort = (string) $sort;

        $this->apply(); 
    }

}
