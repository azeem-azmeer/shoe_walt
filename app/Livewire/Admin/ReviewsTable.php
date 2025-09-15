<?php

namespace App\Livewire\Admin;

use App\Models\Review;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Carbon;
use MongoDB\BSON\ObjectId;

class ReviewsTable extends Component
{
    use WithPagination;

    public string $search = '';
    public ?int $rating = null;
    public ?int $orderId = null;
    public ?int $userId  = null;
    public string $sort  = 'created_at';
    public string $dir   = 'desc';
    public ?string $from = null;
    public ?string $to   = null;

    public ?string $confirmingDelete = null;

    protected $queryString = [
        'search'  => ['except' => ''],
        'rating'  => ['except' => null],
        'orderId' => ['except' => null, 'as' => 'order'],
        'userId'  => ['except' => null, 'as' => 'user'],
        'sort'    => ['except' => 'created_at'],
        'dir'     => ['except' => 'desc'],
        'from'    => ['except' => null],
        'to'      => ['except' => null],
    ];

    public function updating($name, $value)
    {
        if (in_array($name, ['search','rating','orderId','userId','from','to'])) {
            $this->resetPage();
        }
    }

    public function sortBy(string $field): void
    {
        if ($this->sort === $field) {
            $this->dir = $this->dir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sort = $field;
            $this->dir  = 'asc';
        }
        $this->resetPage();
    }

    public function confirmDelete(string $id): void
    {
        $this->confirmingDelete = $id;
    }

    public function delete(string $id): void
    {
        try {
            // Try find by plain id; fallback to Mongo ObjectId
            $rev = Review::find($id);
            if (!$rev) {
                try {
                    $rev = Review::where('_id', new ObjectId($id))->first();
                } catch (\Throwable $e) {
                    // ignore invalid ObjectId
                }
            }

            if ($rev) {
                $rev->delete();
                $this->dispatch('flash', message: 'Review deleted.', type: 'success');
            } else {
                $this->dispatch('flash', message: 'Review not found.', type: 'error');
            }
        } catch (\Throwable $e) {
            $this->dispatch('flash', message: 'Delete failed: '.$e->getMessage(), type: 'error');
        }

        $this->confirmingDelete = null;
        $this->resetPage();
    }

    /** Base query with all filters applied (reused for rows + stats) */
    protected function baseQuery()
    {
        $q = Review::query();

        if ($this->search !== '') {
            $q->where('feedback', 'like', '%'.$this->search.'%');
        }
        if ($this->rating)  $q->where('rating', (int) $this->rating);
        if ($this->orderId) $q->where('order_id', (int) $this->orderId);
        if ($this->userId)  $q->where('user_id',  (int) $this->userId);

        if ($this->from) $q->where('created_at', '>=', Carbon::parse($this->from));
        if ($this->to)   $q->where('created_at', '<=', Carbon::parse($this->to)->endOfDay());

        return $q;
    }

    public function getRowsProperty()
    {
        $sortable = ['created_at','updated_at','rating','order_id','user_id'];
        $sort = in_array($this->sort, $sortable) ? $this->sort : 'created_at';
        $dir  = $this->dir === 'asc' ? 'asc' : 'desc';

        return $this->baseQuery()
            ->orderBy($sort, $dir)
            ->paginate(12);
    }

    /** Stats for header: total, avg, and star breakdown */
    public function getStatsProperty(): array
    {
        $q = $this->baseQuery();
        $total = (clone $q)->count();
        $avg   = $total ? round((clone $q)->avg('rating'), 2) : 0.0;

        $byStar = [];
        for ($s = 1; $s <= 5; $s++) {
            $byStar[$s] = (clone $q)->where('rating', $s)->count();
        }

        return [
            'total'   => $total,
            'avg'     => $avg,
            'by_star' => $byStar,
        ];
    }

    public function render()
    {
        return view('livewire.admin.reviews-table', [
            'rows'  => $this->rows,
            'stats' => $this->stats,
        ]);
    }
}
