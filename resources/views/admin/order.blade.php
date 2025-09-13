<x-app-layout>
  <x-slot name="header">
    <h2 class="font-bold text-xl">Customer Orders</h2>
  </x-slot>

  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <livewire:admin.orders-table />
  </div>

  <script>
    document.addEventListener('livewire:init', () => {
      Livewire.on('order-status-updated', ({ id, status }) => {
        // simple toast/log; replace with your own toast system if desired
        console.log(`Order #${id} updated to ${status}`);
      });
    });
  </script>
</x-app-layout>
