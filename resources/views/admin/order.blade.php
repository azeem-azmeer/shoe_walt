<x-app-layout>
  <x-slot name="header">
    <h2 class="font-bold text-xl text-gray-900">Customer Orders</h2>
  </x-slot>

  {{-- Full page wallpaper --}}
  <div class="min-h-screen bg-cover bg-center bg-fixed"
       style="background-image: url('{{ asset('storage/products/dashboard.jpg') }}')">

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <livewire:admin.orders-table />
    </div>
  </div>

  {{-- Toast script --}}
  <script>
    document.addEventListener('livewire:init', () => {
      Livewire.on('order-status-updated', ({ id, status }) => {
        // Example toast — you can swap with Alpine.js or SweetAlert
        const toast = document.createElement('div');
        toast.innerText = `✅ Order #${id} updated to ${status}`;
        toast.className = "fixed bottom-5 right-5 bg-green-600 text-white px-4 py-2 rounded-lg shadow-lg animate-bounce";
        document.body.appendChild(toast);

        setTimeout(() => toast.remove(), 3000);
      });
    });
  </script>
</x-app-layout>
