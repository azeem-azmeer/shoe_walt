<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\Product;

class StatusToggle extends Component
{
    public int $productId;
    public string $status; // "Active" | "Inactive"

    public function mount(int $productId, string $status): void
    {
        $this->productId = $productId;
        $this->status    = $status;
    }

    public function toggle(): void
    {
        $next = $this->status === 'Active' ? 'Inactive' : 'Active';

        // Update DB
        Product::where('product_id', $this->productId)->update(['status' => $next]);

        // Reflect immediately in UI
        $this->status = $next;

        
        $this->dispatch('product-status-updated', productId: $this->productId, status: $next);
    }

    public function render()
    {
        return view('livewire.admin.status-toggle');
    }
}
