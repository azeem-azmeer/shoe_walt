{{-- resources/views/livewire/user/filters.blade.php --}}
<div> {{-- SINGLE ROOT ELEMENT --}}
  {{-- spacing after hero --}}
  <div class="h-6 md:h-8"></div>

  <section class="mb-4 px-4 sm:px-6 lg:px-8">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">

      {{-- Sizes (chips as links) --}}
      <div class="flex items-center gap-2 overflow-x-auto">
        <span class="text-sm text-gray-600 shrink-0">Size:</span>

        @php
          $baseUrl   = url()->current();
          $qs        = request()->query();
          $qsNoPage  = collect($qs)->except('page')->all();
          $link = function(array $overrides) use ($baseUrl, $qsNoPage) {
              $params = array_filter(array_merge($qsNoPage, $overrides), fn($v) => $v !== null && $v !== '');
              return $params ? $baseUrl.'?'.http_build_query($params) : $baseUrl;
          };
          $activeSize = (string) request('size','');
          $activeSort = (string) request('sort','');
        @endphp

        {{-- All --}}
        <a href="{{ $link(['size' => null]) }}"
        class="text-xs px-3 py-1.5 rounded-full border transition whitespace-nowrap
                {{ $activeSize==='' 
                    ? 'bg-gray-700 text-white border-gray-700' 
                    : 'bg-gray-100 text-gray-700 border-gray-300 hover:bg-gray-200' }}">
        All
        </a>

        {{-- Dynamic sizes from DB (provided by the component) --}}
        @foreach ($sizes as $s)
        @php $isActive = $activeSize === (string) $s; @endphp
        <a href="{{ $link(['size' => $s]) }}"
            class="text-xs px-3 py-1.5 rounded-full border transition whitespace-nowrap
                    {{ $isActive 
                        ? 'bg-gray-700 text-white border-gray-700' 
                        : 'bg-gray-100 text-gray-700 border-gray-300 hover:bg-gray-200' }}">
            {{ $s }}
        </a>
        @endforeach

      </div>

      {{-- Sort --}}
      <div class="flex items-center gap-2">
        <label class="text-sm text-gray-600">Sort:</label>
        <select class="border rounded px-2 py-1.5 text-sm"
            onchange="window.location.href=this.options[this.selectedIndex].dataset.url">
            <option data-url="{{ $link(['sort' => null]) }}" {{ $activeSort==='' ? 'selected' : '' }}>
                Newest
            </option>
            <option data-url="{{ $link(['sort' => 'price_asc']) }}" {{ $activeSort==='price_asc' ? 'selected' : '' }}>
                Price: Low to High
            </option>
            <option data-url="{{ $link(['sort' => 'price_desc']) }}" {{ $activeSort==='price_desc' ? 'selected' : '' }}>
                Price: High to Low
            </option>
        </select>


        <a href="{{ $baseUrl }}" class="text-xs px-3 py-1.5 rounded-full border border-gray-300 hover:border-black">
          Clear
        </a>
      </div>
    </div>
  </section>
</div>
