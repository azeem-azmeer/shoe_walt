<div x-data="{ toast: null }" x-on:flash.window="toast = $event.detail; setTimeout(()=> toast=null, 2500)">
  {{-- ===== Top stats card ===== --}}
  <div class="rounded-2xl border bg-gradient-to-r from-indigo-50 via-sky-50 to-blue-50 p-5 mb-5">
    <div class="flex flex-wrap items-center gap-6">
      {{-- Title + total --}}
      <div class="flex-1 min-w-[220px]">
        <h2 class="text-xl md:text-2xl font-black tracking-tight flex items-center gap-2">
          Customer Reviews
          <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-black text-white">
            {{ number_format($stats['total'] ?? 0) }}
          </span>
        </h2>
        <p class="text-sm text-gray-600 mt-1">
          Manage and moderate what customers say about their orders.
        </p>
      </div>

      {{-- Average rating --}}
      @php
        $avg = round(($stats['avg'] ?? 0), 1);
        $full = (int) floor($avg);
        $empty = 5 - $full;
      @endphp
      <div class="flex items-center gap-3 min-w-[220px]">
        <div class="text-4xl font-black">{{ $avg }}</div>
        <div class="text-2xl text-yellow-500 leading-none">
          {{ str_repeat('★', $full) }}<span class="text-gray-300">{{ str_repeat('☆', $empty) }}</span>
        </div>
        <div class="text-xs text-gray-500">(avg)</div>
      </div>

      {{-- Star breakdown --}}
      <div class="grid grid-cols-1 gap-1.5 w-full md:w-80">
        @for($s = 5; $s >= 1; $s--)
          @php
            $count = (int) ($stats['by_star'][$s] ?? 0);
            $total = max(1, (int) ($stats['total'] ?? 0));
            $pct   = round(($count / $total) * 100);
            $bar   = match(true) {
              $s >= 4 => 'bg-emerald-500',
              $s == 3 => 'bg-amber-500',
              default => 'bg-rose-500',
            };
          @endphp
          <div class="flex items-center gap-2">
            <span class="text-xs w-6 text-gray-600">{{ $s }}★</span>
            <div class="flex-1 h-2 rounded bg-white/70 border border-white/60 overflow-hidden">
              <div class="h-full {{ $bar }}" style="width: {{ $pct }}%"></div>
            </div>
            <span class="text-[11px] text-gray-600 w-12 text-right">{{ $count }}</span>
          </div>
        @endfor
      </div>
    </div>
  </div>

  {{-- ===== Filters ===== --}}
  <div class="flex flex-wrap gap-3 items-end mb-4">
    <div>
      <label class="block text-xs text-gray-500 mb-1">Search</label>
      <input type="text" wire:model.debounce.400ms="search"
             class="border rounded-lg px-3 py-2 w-56 focus:outline-none focus:ring-2 focus:ring-gray-900/10"
             placeholder="feedback…">
    </div>

    <div>
      <label class="block text-xs text-gray-500 mb-1">Rating</label>
      <select wire:model="rating" class="border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-gray-900/10">
        <option value="">Any</option>
        @for($i=1;$i<=5;$i++)
          <option value="{{ $i }}">{{ $i }}★</option>
        @endfor
      </select>
    </div>

    <div>
      <label class="block text-xs text-gray-500 mb-1">Order ID</label>
      <input type="number" wire:model.defer="orderId"
             class="border rounded-lg px-3 py-2 w-28 focus:outline-none focus:ring-2 focus:ring-gray-900/10">
    </div>

    <div>
      <label class="block text-xs text-gray-500 mb-1">User ID</label>
      <input type="number" wire:model.defer="userId"
             class="border rounded-lg px-3 py-2 w-28 focus:outline-none focus:ring-2 focus:ring-gray-900/10">
    </div>

    <div>
      <label class="block text-xs text-gray-500 mb-1">From</label>
      <input type="date" wire:model="from"
             class="border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-gray-900/10">
    </div>
    <div>
      <label class="block text-xs text-gray-500 mb-1">To</label>
      <input type="date" wire:model="to"
             class="border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-gray-900/10">
    </div>

    <button wire:click="$refresh"
            class="ml-auto px-3 py-2 border rounded-lg hover:bg-gray-50 transition">
      Refresh
    </button>
  </div>

  {{-- ===== Table ===== --}}
  <div class="overflow-hidden border rounded-xl">
    <div class="overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead class="bg-gray-50 text-gray-600 sticky top-0 z-10">
          <tr>
            <th class="px-3 py-2 text-left">
              <button wire:click="sortBy('created_at')" class="font-semibold hover:opacity-80">Date</button>
            </th>
            <th class="px-3 py-2 text-left">
              <button wire:click="sortBy('user_id')" class="font-semibold hover:opacity-80">User</button>
            </th>
            <th class="px-3 py-2 text-left">
              <button wire:click="sortBy('order_id')" class="font-semibold hover:opacity-80">Order</button>
            </th>
            <th class="px-3 py-2 text-left">
              <button wire:click="sortBy('rating')" class="font-semibold hover:opacity-80">Rating</button>
            </th>
            <th class="px-3 py-2 text-left">Feedback</th>
            <th class="px-3 py-2"></th>
          </tr>
        </thead>
        <tbody class="divide-y">
          @forelse ($rows as $r)
            @php
              $score = (int)($r->rating ?? 0);
              $chip = $score >= 4
                      ? 'bg-emerald-100 text-emerald-700'
                      : ($score === 3 ? 'bg-amber-100 text-amber-700'
                                      : 'bg-rose-100 text-rose-700');
            @endphp
            <tr class="hover:bg-gray-50 transition">
              <td class="px-3 py-2 text-gray-700">
                {{ optional($r->created_at)->timezone(config('app.timezone'))->format('Y-m-d H:i') }}
              </td>
              <td class="px-3 py-2">
                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-gray-100 text-gray-700 text-xs">
                  User #{{ $r->user_id }}
                </span>
              </td>
              <td class="px-3 py-2">
                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-gray-100 text-gray-700 text-xs">
                  Order #{{ $r->order_id }}
                </span>
              </td>
              <td class="px-3 py-2">
                <span class="inline-flex items-center gap-2">
                  <span class="font-medium text-yellow-500">
                    {{ str_repeat('★', $score) }}<span class="text-gray-300">{{ str_repeat('☆', 5 - $score) }}</span>
                  </span>
                  <span class="px-2 py-0.5 rounded-full text-[11px] {{ $chip }}">{{ $score }} / 5</span>
                </span>
              </td>
              <td class="px-3 py-2 max-w-xl">
                <div class="line-clamp-2 text-gray-800" title="{{ $r->feedback }}">
                  {{ $r->feedback }}
                </div>
              </td>
              <td class="px-3 py-2 text-right">
                <button class="inline-flex items-center gap-1 text-red-600 hover:bg-red-50 px-2 py-1 rounded transition"
                        wire:click="confirmDelete('{{ (string)($r->_id ?? $r->id) }}')">
                  {{-- Trash icon --}}
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M9 3h6a1 1 0 0 1 1 1v1h4v2H4V5h4V4a1 1 0 0 1 1-1Zm1 6h2v9h-2V9Zm4 0h2v9h-2V9ZM7 9h2v9H7V9Z"/>
                  </svg>
                  Delete
                </button>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="6" class="px-3 py-6 text-center text-gray-500">
                No reviews found.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  {{-- Pager + “showing” --}}
  <div class="mt-4 flex items-center justify-between gap-3">
    <div class="text-xs text-gray-500">
      @if($rows->total() > 0)
        Showing {{ $rows->firstItem() }}–{{ $rows->lastItem() }} of {{ $rows->total() }} reviews
      @else
        0 reviews
      @endif
    </div>
    <div>{{ $rows->links() }}</div>
  </div>

  {{-- Delete confirm --}}
  <div x-cloak x-show="$wire.confirmingDelete"
       class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
    <div class="bg-white rounded-xl p-5 w-full max-w-md shadow-xl">
      <h3 class="font-semibold text-lg mb-1">Delete review?</h3>
      <p class="text-sm text-gray-600 mb-4">
        This action cannot be undone. The review will be permanently removed.
      </p>
      <div class="flex justify-end gap-2">
        <button class="px-3 py-2 rounded border hover:bg-gray-50"
                @click="$wire.confirmingDelete=null">Cancel</button>
        <button class="px-3 py-2 rounded bg-red-600 text-white hover:bg-red-700"
                @click="$wire.delete($wire.confirmingDelete)">
          Delete
        </button>
      </div>
    </div>
  </div>

  {{-- Toast --}}
  <div x-cloak x-show="toast"
       class="fixed bottom-6 right-6 bg-gray-900 text-white text-sm px-4 py-2 rounded-lg shadow">
    <span x-text="toast?.message || ''"></span>
  </div>
</div>
