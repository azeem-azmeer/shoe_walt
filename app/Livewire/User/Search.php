<?php

namespace App\Livewire\User;

use Livewire\Component;
use App\Models\Product;

class Search extends Component
{
    public string $q = '';
    public bool $open = false;       
    public int $highlight = -1;

    public bool $showMobile = false; // mobile fullscreen overlay
    public array $results = [];

    /**
     * Live-updated when query changes.
     */
    public function updatedQ(): void
    {
        $this->fetch();
        $this->open = strlen(trim($this->q)) > 0;
    }

    /**
     * Fetch results from DB based on search term.
     */
    public function fetch(): void
    {
        $term = trim($this->q);

        if ($term === '') {
            $this->results = [];
            return;
        }

        $this->results = Product::query()
            ->select('product_id', 'product_name', 'price', 'main_image')
            ->where('status', 'Active')
            ->where(function ($q) use ($term) {
                $q->where('product_name', 'like', "%{$term}%")
                  ->orWhere('category', 'like', "%{$term}%")
                  ->orWhereRaw('LOWER(product_name) like ?', ['%' . strtolower($term) . '%']);
            })
            ->orderByDesc('product_id')
            ->take(10)
            ->get()
            ->map(function ($p) {
                $path = str_replace('\\', '/', ltrim((string)$p->main_image, '/'));
                $img = $path
                    ? (
                        preg_match('~^https?://~i', $path)
                            ? $path
                            : (
                                (str_starts_with($path, '/storage/') || str_starts_with($path, 'storage/'))
                                    ? url(ltrim($path, '/'))
                                    : \Storage::url($path)
                            )
                    )
                    : asset('storage/products/placeholder.webp');


                return [
                    'id'    => $p->product_id,
                    'name'  => $p->product_name,
                    'price' => (float) $p->price,
                    'img'   => $img,
                    'url'   => route('user.product.preview', $p->product_id),
                ];
            })
            ->values()
            ->toArray();
    }

    /**
     * Move highlight index up/down in desktop suggestions.
     */
    public function move(int $delta): void
    {
        if (!count($this->results)) return;
        $this->highlight = ($this->highlight + $delta + count($this->results)) % count($this->results);
    }

    /**
     * Go to highlighted or chosen result.
     */
    public function go(?int $index = null)
    {
        if ($index === null) $index = $this->highlight;
        if ($index < 0 || $index >= count($this->results)) return;

        return redirect()->to($this->results[$index]['url']);
    }

    /**
     * Clear query and reset results.
     */
    public function clear(): void
    {
        $this->q = '';
        $this->results = [];
        $this->open = false;
        $this->highlight = -1;
    }

    // =================== Mobile overlay controls ===================

    public function openMobile(): void
    {
        $this->showMobile = true;
        $this->dispatch('focus-mobile-search'); // JS hook to focus input
    }

    public function closeMobile(): void
    {
        $this->showMobile = false;
        $this->clear(); // reset when closing
    }

    public function render()
    {
        return view('livewire.user.search');
    }
}
