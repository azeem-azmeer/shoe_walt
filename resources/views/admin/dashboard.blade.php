<x-app-layout>
  <x-slot name="header">
    <h2 class="font-bold text-2xl text-gray-900 tracking-tight">
      Admin Dashboard
    </h2>
  </x-slot>

  {{-- Wallpaper applied to full page --}}
  <div class="min-h-screen bg-cover bg-center bg-fixed p-6"
       style="background-image: url('{{ asset('storage/products/dashboard.jpg') }}')">

        {{-- Welcome Section --}}
        <div>
          <p class="text-lg font-semibold text-gray-800">
            Welcome <span class="text-indigo-600">{{ Auth::user()->name }}</span> 🎉
          </p>
          <p class="text-base text-gray-600">
            You are logged in as <span class="font-bold text-emerald-600">Admin</span>.
          </p>
        </div>

        {{-- Livewire dashboard cards --}}
        <livewire:admin.dashboard-cards />
      
  </div>
</x-app-layout>
