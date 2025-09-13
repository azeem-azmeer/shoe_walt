{{-- resources/views/auth/register.blade.php --}}
<x-guest-layout>
  <div class="min-h-screen bg-gray-50 flex items-center justify-center p-4 sm:p-8">
    <div class="w-full max-w-6xl mx-auto grid grid-cols-1 md:grid-cols-2 gap-8">
      {{-- LEFT: Register card (same style as login) --}}
      <div class="bg-white rounded-2xl shadow border p-6 sm:p-10">
        <div class="mb-8">
          <img src="{{ asset('storage/products/logo.png') }}" alt="Logo" class="h-8">
        </div>

        <h1 class="text-4xl font-extrabold tracking-tight">Create your account</h1>
        <p class="text-gray-500 mt-1">Join us to get started.</p>

        <x-validation-errors class="mt-6 mb-2" />
        @if (session('status'))
          <div class="mt-4 font-medium text-sm text-green-600">
            {{ session('status') }}
          </div>
        @endif

        {{-- Register form --}}
        <form method="POST" action="{{ route('register') }}" class="space-y-4">
          @csrf

          <div>
            <x-label for="name" value="{{ __('Name') }}" />
            <x-input id="name" type="text" name="name" class="block mt-1 w-full"
                     :value="old('name')" required autofocus autocomplete="name" />
          </div>

          <div>
            <x-label for="email" value="{{ __('Email address') }}" />
            <x-input id="email" type="email" name="email" class="block mt-1 w-full"
                     :value="old('email')" required autocomplete="username" />
          </div>

          <div>
            <x-label for="password" value="{{ __('Password') }}" />
            <x-input id="password" type="password" name="password" class="block mt-1 w-full"
                     required autocomplete="new-password" />
          </div>

          <div>
            <x-label for="password_confirmation" value="{{ __('Confirm Password') }}" />
            <x-input id="password_confirmation" type="password" name="password_confirmation"
                     class="block mt-1 w-full" required autocomplete="new-password" />
          </div>

          @if (Laravel\Jetstream\Jetstream::hasTermsAndPrivacyPolicyFeature())
            <div class="pt-2">
              <x-label for="terms">
                <div class="flex items-start gap-2">
                  <x-checkbox name="terms" id="terms" required />
                  <div class="text-sm text-gray-600">
                    {!! __('I agree to the :terms_of_service and :privacy_policy', [
                      'terms_of_service' => '<a target="_blank" href="'.route('terms.show').'" class="underline font-medium hover:text-gray-900">'.__('Terms of Service').'</a>',
                      'privacy_policy' => '<a target="_blank" href="'.route('policy.show').'" class="underline font-medium hover:text-gray-900">'.__('Privacy Policy').'</a>',
                    ]) !!}
                  </div>
                </div>
              </x-label>
            </div>
          @endif

          <x-button class="w-full justify-center mt-2">
            {{ __('Create account') }}
          </x-button>
        </form>
       <div class="my-6 flex items-center justify-center">
        <div class="w-full max-w-sm flex items-center">
            <div class="flex-1 h-px bg-gray-200"></div>
            <span class="mx-3 text-xs text-gray-400">or</span>
            <div class="flex-1 h-px bg-gray-200"></div>
        </div>
        </div>


     
        <div class="mt-6">
          <button
                id="googleRegisterBtn"
                type="button"
                class="w-full inline-flex items-center justify-center gap-3 rounded-xl border h-12 px-5 text-[15px] hover:bg-gray-50 transition"
                >
            <svg class="h-5 w-5" viewBox="0 0 48 48" aria-hidden="true">
              <path fill="#FFC107" d="M43.6 20.5H42V20H24v8h11.3C33.8 31.9 29.3 35 24 35c-7.2 0-13-5.8-13-13s5.8-13 13-13c3.1 0 6 1.1 8.2 2.9l5.7-5.7C34.4 3.9 29.5 2 24 2 11.8 2 2 11.8 2 24s9.8 22 22 22c11 0 21-8 21-22 0-1.2-.1-2.3-.4-3.5z"/>
              <path fill="#FF3D00" d="M6.3 14.7l6.6 4.8C14.5 16.2 18.9 13 24 13c3.1 0 6 1.1 8.2 2.9l5.7-5.7C34.4 3.9 29.5 2 24 2 15.2 2 7.7 7.1 4.2 14.2z"/>
              <path fill="#4CAF50" d="M24 46c5.2 0 10-1.9 13.6-5.1l-6.3-5.2C29 37.2 26.6 38 24 38c-5.3 0-9.8-3.1-11.7-7.6l-6.6 5C9.1 41 16 46 24 46z"/>
              <path fill="#1976D2" d="M43.6 20.5H42V20H24v8h11.3c-1.1 3.2-3.6 5.7-6.7 7.2l6.3 5.2C38 37.3 42 31.2 42 24c0-1.2-.1-2.3-.4-3.5z"/>
            </svg>
            <span class="font-medium">Sign up with Google</span>
          </button>

          
        </div>

        <p class="text-sm text-gray-500 mt-6">
          Already have an account?
          <a href="{{ route('login') }}" class="text-indigo-600 font-semibold hover:text-indigo-700">Sign in</a>
        </p>
      </div>

      {{-- RIGHT: Hero image (same as login) --}}
      <div class="hidden md:block">
        <div class="h-full rounded-2xl overflow-hidden bg-gray-200 relative md:min-h-[560px]">
          <img src="{{ asset('storage/products/register.jpg') }}" alt="Register visual" class="w-full h-full object-cover">
          <div class="absolute inset-0 bg-gradient-to-t from-black/40 to-transparent"></div>
          <div class="absolute bottom-6 left-6 right-6 text-white">
            <h3 class="text-3xl font-extrabold drop-shadow">Join Shoe Walt.</h3>
            <p class="text-sm text-white/90 mt-1 drop-shadow">
              Create an account to enjoy a faster checkout and track your orders.
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</x-guest-layout>
