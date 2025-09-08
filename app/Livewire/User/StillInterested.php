<?php

namespace App\Livewire\User;

use Livewire\Component;
use App\Models\Product;

class StillInterested extends Component
{
    public int $limit = 12;

    public function mount(int $limit = 12): void
    {
        $this->limit = $limit;
    }

    public function render()
    {
        $products = Product::query()
            ->select('product_id','product_name','price','category','status','main_image','created_at')
            ->whereIn('category', ['Kids','Women'])
            ->where('status', 'Active')
            ->orderByDesc('product_id')
            ->take($this->limit)
            ->get(); // <- pass models directly

        return view('livewire.user.still-interested', compact('products'));
    }
}
