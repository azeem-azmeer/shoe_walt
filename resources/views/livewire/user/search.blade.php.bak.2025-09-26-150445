<div class="relative shrink-0"><!-- single root -->

  {{-- Desktop pill + suggestions --}}
  <div class="relative hidden sm:block" x-data @click.outside="$wire.clear()">
    <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400"
         viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="m21 21-4.3-4.3M17 10a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z"/>
    </svg>

    <input
      type="search"
      placeholder="Search"
      class="pl-10 pr-9 py-2 w-56 md:w-80 rounded-full border border-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-300"
      wire:model.debounce.250ms="q"
      wire:keydown.arrow-down.prevent="move(1)"
      wire:keydown.arrow-up.prevent="move(-1)"
      wire:keydown.enter.prevent="go"
      wire:keydown.escape="clear"
      aria-autocomplete="list"
      aria-expanded="{{ $open ? 'true' : 'false' }}"
      aria-controls="search-suggestions-desktop"
    />

    @if($open && count($results))
      <ul id="search-suggestions-desktop"
          class="absolute z-50 mt-2 w-[18rem] md:w-[22rem] bg-white border rounded-xl shadow-lg overflow-hidden">
        @foreach($results as $i => $r)
          <li wire:key="desk-{{ $i }}" class="{{ $highlight === $i ? 'bg-gray-100' : '' }}">
            <a href="{{ $r['url'] }}" wire:click.prevent="go({{ $i }})"
               class="flex items-center gap-3 p-2">
              <img src="{{ $r['img'] }}"
                   onerror="this.onerror=null;this.src='{{ asset('storage/products/placeholder.webp') }}'"
                   alt="" class="h-10 w-10 rounded object-cover">
              <div class="min-w-0">
                <div class="truncate text-sm font-medium text-gray-900">{{ $r['name'] }}</div>
                <div class="text-xs text-gray-500">${{ number_format($r['price'], 2) }}</div>
              </div>
            </a>
          </li>
        @endforeach
      </ul>
    @endif
  </div>

  {{-- Mobile trigger --}}
  <button type="button"
          class="sm:hidden inline-flex items-center justify-center h-9 w-9 rounded-full hover:bg-gray-100"
          aria-label="Search" wire:click="openMobile">
    <svg class="h-5 w-5 text-gray-700" viewBox="0 0 24 24" fill="none" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="m21 21-4.3-4.3M17 10a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z"/>
    </svg>
  </button>

  {{-- Mobile full-screen overlay --}}
  @if($showMobile)
    <div class="fixed inset-0 z-50 bg-white flex flex-col">
      <div class="flex items-center gap-2 px-3 py-3 border-b">
        <button type="button" class="h-9 w-9 rounded-full hover:bg-gray-100"
                aria-label="Back" wire:click="closeMobile">←</button>

        <div class="relative flex-1">
          <input id="mobileSearchInput" type="search" placeholder="Search products"
                 class="w-full rounded-full border border-gray-200 pl-4 pr-9 py-2 focus:outline-none focus:ring-2 focus:ring-gray-300"
                 wire:model.debounce.250ms="q" />
          {{-- keep ONLY the native clear (circled) --}}
        </div>

        <button type="button" class="text-sm text-gray-600" wire:click="closeMobile">Cancel</button>
      </div>

      <div class="p-3 overflow-y-auto">
        @if(count($results))
          <div class="grid grid-cols-2 gap-3">
            @foreach($results as $i => $r)
              <a wire:key="mob-{{ $i }}" href="{{ $r['url'] }}" class="block"
                 wire:click.prevent="go({{ $i }})">
                <div class="w-full aspect-square overflow-hidden rounded-lg bg-gray-50">
                  <img src="{{ $r['img'] }}"
                       onerror="this.onerror=null;this.src='{{ asset('storage/products/placeholder.webp') }}'"
                       alt="" class="w-full h-full object-cover">
                </div>
                <div class="mt-2 text-sm font-medium text-gray-900 line-clamp-2">{{ $r['name'] }}</div>
                <div class="text-xs text-gray-500">${{ number_format($r['price'], 2) }}</div>
              </a>
            @endforeach
          </div>
        @elseif($q === '')
          <p class="text-gray-500 text-sm">Start typing to search…</p>
        @else
          <p class="text-gray-500 text-sm">No results for “{{ $q }}”.</p>
        @endif
      </div>
    </div>

    <script>
      window.addEventListener('focus-mobile-search', () => {
        requestAnimationFrame(() => {
          const el = document.getElementById('mobileSearchInput');
          if (el) el.focus();
        });
      });
    </script>
  @endif

</div>
