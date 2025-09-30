<?php

namespace App\Livewire\Admin;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class DashboardCards extends Component
{
    public function mount(): void
    {
        $user = Auth::user();
        if (!$user || ($user->role ?? null) !== 'admin') {
            abort(403, 'Admins only');
        }
    }

    private function categoryCount(array $aliases): int
    {
        $aliases = array_map('strtolower', $aliases);

        return Product::query()
            ->whereIn(\DB::raw('LOWER(category)'), $aliases)
            ->count();
    }

    public function render()
    {
        // Product totals (adjust if you only want active products)
        $totalProducts = Product::count();
        $menProducts   = $this->categoryCount(['men', 'man', 'mens']);
        $womenProducts = $this->categoryCount(['women', 'woman', 'womens', 'womans']);
        $kidsProducts  = $this->categoryCount(['kids', 'kid', 'children', 'child']);

        // Orders
        $totalOrders = Order::count();

        // ✅ TOTAL SALES (based on orders):
        // Count only money you actually booked. Your enum is: Pending, Confirmed, Cancelled
        // If you later add "Completed", include it in the whereIn below.
        $totalSales = (float) Order::whereIn('status', ['Confirmed'])->sum('total');

        // Optional breakdowns:
        $todaySales = (float) Order::where('status', 'Confirmed')
        ->whereDate('updated_at', today())
        ->sum('total');

        $monthSales = (float) Order::where('status', 'Confirmed')
            ->whereBetween('updated_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->sum('total');

        return view('livewire.admin.dashboard-cards', [
            'totalProducts' => $totalProducts,
            'menProducts'   => $menProducts,
            'womenProducts' => $womenProducts,
            'kidsProducts'  => $kidsProducts,
            'totalOrders'   => $totalOrders,
            'totalSales'    => $totalSales,
            'todaySales'    => $todaySales,
            'monthSales'    => $monthSales,
        ]);
    }
}
