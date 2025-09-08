<div>
  <button
    wire:click="toggle"
    class="px-2 py-1 text-xs rounded-full font-semibold
           {{ $status === 'Active'
                ? 'bg-green-100 text-green-700 hover:bg-green-200'
                : 'bg-yellow-100 text-yellow-700 hover:bg-yellow-200' }}">
    {{ $status }}
  </button>
</div>
