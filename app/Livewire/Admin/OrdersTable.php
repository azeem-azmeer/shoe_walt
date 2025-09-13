<?php

namespace App\Livewire\Admin;

use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class OrdersTable extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'tailwind';

    // Filters
    public string $status = '';
    public string $q = '';
    public int $perPage = 20;

    // Allowed statuses (matches your DB enum)
    private const ALLOWED = ['Pending', 'Confirmed', 'Cancelled'];

    // Persist filters in the URL
    protected $queryString = [
        'status'  => ['except' => ''],
        'q'       => ['except' => ''],
        'perPage' => ['except' => 20],
        'page'    => ['except' => 1],
    ];

    public function mount(): void
    {
        $user = Auth::user();
        if (!$user || ($user->role ?? null) !== 'admin') {
            abort(403, 'Admins only');
        }
    }

    // Reset pagination when filters change
    public function updatingStatus(): void { $this->resetPage(); }
    public function updatedPerPage(): void { $this->resetPage(); }

    /** Triggered by Enter key / Search button */
    public function go(): void
    {
        $this->q = trim($this->q);
        $this->resetPage();
    }

    /** Inline status update */
    public function updateStatus(int $orderId, string $status): void
    {
        $status = ucfirst(strtolower(trim($status)));

        if (!in_array($status, self::ALLOWED, true)) {
            $this->addError('status', 'Invalid status selected.');
            return;
        }

        $order = Order::findOrFail($orderId);
        $order->update(['status' => $status]);

        $this->dispatch('order-status-updated', id: $orderId, status: $status);
    }

    public function render()
    {
        $orders = Order::query()
            ->with(['user:id,name,email'])
            ->withCount('items')
            ->when($this->status !== '', fn ($q) => $q->where('status', $this->status))
            ->when($this->q !== '', function ($q) {
                $term = "%{$this->q}%";
                $q->where(function ($qq) use ($term) {
                    if (ctype_digit($this->q)) {
                        $qq->orWhere('id', (int) $this->q);
                    }
                    $qq->orWhereHas('user', fn ($uq) =>
                        $uq->where('email', 'like', $term)
                           ->orWhere('name', 'like', $term)
                    );
                });
            })
            ->latest()
            ->paginate($this->perPage);

        $orderCount = Order::count();

        return view('livewire.admin.orders-table', compact('orders', 'orderCount'));
    }
}
