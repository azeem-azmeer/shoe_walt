<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class CustomersTable extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'tailwind';

    // Filters
    public string $q = '';
    public string $verified = '';   // '', 'yes', 'no'
    public string $withOrders = ''; // '', 'yes'
    public string $sort = 'recent'; // 'recent', 'orders'
    public int $perPage = 20;

    // Persist filters in URL
    protected $queryString = [
        'q'         => ['except' => ''],
        'verified'  => ['except' => ''],
        'withOrders'=> ['except' => ''],
        'sort'      => ['except' => 'recent'],
        'perPage'   => ['except' => 20],
        'page'      => ['except' => 1],
    ];

    public function mount(): void
    {
        $user = Auth::user();
        if (!$user || ($user->role ?? null) !== 'admin') {
            abort(403, 'Admins only');
        }
    }

    // Reset pagination when filters change
    public function updatedPerPage(): void { $this->resetPage(); }
    public function updatedVerified(): void { $this->resetPage(); }
    public function updatedWithOrders(): void { $this->resetPage(); }
    public function updatedSort(): void { $this->resetPage(); }

    /** Enter / button search */
    public function go(): void
    {
        $this->q = trim($this->q);
        $this->resetPage();
    }

    public function render()
    {
        $base = User::query()
            ->where('role', '!=', 'admin')
            ->withCount('orders')
            ->when($this->q !== '', function ($q) {
                $term = "%{$this->q}%";
                $q->where(function ($qq) use ($term) {
                    if (ctype_digit($this->q)) {
                        $qq->orWhere('id', (int) $this->q);
                    }
                    $qq->orWhere('name', 'like', $term)
                       ->orWhere('email', 'like', $term);
                });
            })
            ->when($this->verified === 'yes', fn($q) => $q->whereNotNull('email_verified_at'))
            ->when($this->verified === 'no',  fn($q) => $q->whereNull('email_verified_at'))
            ->when($this->withOrders === 'yes', fn($q) => $q->has('orders'));

        $users = (clone $base)
            ->when($this->sort === 'orders',
                fn($q) => $q->orderByDesc('orders_count'),
                fn($q) => $q->latest('created_at')
            )
            ->paginate($this->perPage);

        $totalCustomers = (clone $base)->count(); // count AFTER filters except sort/perPage

        return view('livewire.admin.customers-table', [
            'users'          => $users,
            'totalCustomers' => $totalCustomers,
        ]);
    }
}
