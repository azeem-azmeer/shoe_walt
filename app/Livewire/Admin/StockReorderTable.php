<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Product;

class StockReorderTable extends Component
{
    use WithPagination;

    public string $search = '';
    public string $category = '';
    public int $perPage = 10;

    protected $queryString = [
        'search'   => ['except' => ''],
        'category' => ['except' => ''],
        'page'     => ['except' => 1],
    ];

    protected $paginationTheme = 'tailwind';

    public function updatingSearch()   { $this->resetPage(); }
    public function updatingCategory() { $this->resetPage(); }

    public function clear()
    {
        $this->reset(['search', 'category']);
        $this->resetPage();
    }

    public function render()
    {
        $q = Product::query()
            ->select('product_id','product_name','price','category','status','main_image')
            ->with(['sizes:product_id,size,qty'])
            // only products that have at least one zero-size
            ->whereExists(function ($sub) {
                $sub->selectRaw(1)
                    ->from('product_sizes as ps')
                    ->whereColumn('ps.product_id', 'products.product_id')
                    ->where('ps.qty', '=', 0);
            })
            ->orderByDesc('product_id');

        if ($this->category !== '') {
            $q->where('category', $this->category);
        }

        if ($this->search !== '') {
            $like = '%'.$this->search.'%';
            $q->where(function ($qq) use ($like) {
                $qq->where('product_name', 'like', $like)
                   ->orWhere('category', 'like', $like)
                   ->orWhere('description', 'like', $like);
            });
        }

        $products = $q->paginate($this->perPage);

        return view('livewire.admin.stock-reorder-table', compact('products'));
    }
    public function go()
{
    $this->resetPage(); // refresh pagination
}

}
