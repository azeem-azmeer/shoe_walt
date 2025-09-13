{{-- resources/views/auth/login.blade.php --}}
<x-guest-layout>
  <div class="min-h-screen bg-gray-50 flex items-center justify-center p-4 sm:p-8">
    <div class="w-full max-w-6xl mx-auto grid grid-cols-1 md:grid-cols-2 gap-8">
      {{-- LEFT: Login card --}}
      <div class="bg-white rounded-2xl shadow border p-6 sm:p-10">
        <div class="mb-8">
          <img src="{{ asset('storage/products/logo.png') }}" alt="Logo" class="h-8">
        </div>

        <h1 class="text-4xl font-extrabold tracking-tight">Welcome back</h1>
        <p class="text-gray-500 mt-1">Please enter your details</p>

        <x-validation-errors class="mt-6 mb-2" />
        @if (session('status'))
          <div class="mt-4 font-medium text-sm text-green-600">
            {{ session('status') }}
          </div>
        @endif

        {{-- Email/password form --}}
        <form method="POST" action="{{ route('login') }}" class="space-y-4 mt-6">
          @csrf

          <div>
            <x-label for="email" value="{{ __('Email address') }}" />
            <x-input id="email" type="email" name="email" class="block mt-1 w-full"
                     :value="old('email')" required autofocus autocomplete="username" />
          </div>

          <div>
            <div class="flex items-center justify-between">
              <x-label for="password" value="{{ __('Password') }}" />
              @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}"
                   class="text-sm text-indigo-600 hover:text-indigo-700 font-medium">
                  {{ __('Forgot password') }}
                </a>
              @endif
            </div>
            <x-input id="password" type="password" name="password"
                     class="block mt-1 w-full" required autocomplete="current-password" />
          </div>

          <label for="remember_me" class="flex items-center gap-2 pt-1">
            <x-checkbox id="remember_me" name="remember" />
            <span class="text-sm text-gray-600">Remember for 30 days</span>
          </label>

          <x-button class="w-full justify-center mt-2">
            {{ __('Sign in') }}
          </x-button>
        </form>

        <p class="text-sm text-gray-500 mt-6">
          Don’t have an account?
          <a href="{{ route('register') }}" class="text-indigo-600 font-semibold hover:text-indigo-700">Sign up</a>
        </p>
      </div>

      {{-- RIGHT: Hero image (visible from md and up) --}}
      <div class="hidden md:block">
        <div class="h-full rounded-2xl overflow-hidden bg-gray-200 relative md:min-h-[560px]">
          <img src="{{ asset('storage/products/login.jpg') }}" alt="Login visual" class="w-full h-full object-cover">
          <div class="absolute inset-0 bg-gradient-to-t from-black/40 to-transparent"></div>
          <div class="absolute bottom-6 left-6 right-6 text-white">
            <h3 class="text-3xl font-extrabold drop-shadow">Bring your ideas to life.</h3>
            <p class="text-sm text-white/90 mt-1 drop-shadow">
              Sign up for free and enjoy access to all features for 30 days. No credit card required.
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</x-guest-layout>
