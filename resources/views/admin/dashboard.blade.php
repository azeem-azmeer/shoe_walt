<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800">
      Admin Dashboard
    </h2>
  </x-slot>

  <div class="flex">
    <div class="flex-1 p-6 bg-white rounded shadow ml-4 space-y-6">
      <div>
        <p>Welcome {{ Auth::user()->name }} 🎉</p>
        <p>You are logged in as <b>Admin</b>.</p>
      </div>

      {{-- Livewire dashboard cards --}}
      <livewire:admin.dashboard-cards />
    </div>
  </div>
</x-app-layout>
