<?php

namespace App\Livewire\Admin;

use App\Models\Product;
use Livewire\Component;

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

        // Update just this product’s status
        Product::where('product_id', $this->productId)->update(['status' => $next]);

        // Reflect the change in the UI
        $this->status = $next;

        // (optional) notify parent/listeners
        $this->dispatch('product-status-updated', id: $this->productId, status: $next);
    }

    public function render()
    {
        return view('livewire.admin.status-toggle');
    }
}
