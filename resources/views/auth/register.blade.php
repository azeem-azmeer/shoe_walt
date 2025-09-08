<x-guest-layout>
    <x-authentication-card>
        <x-slot name="logo">
            <x-authentication-card-logo />
        </x-slot>

        <x-validation-errors class="mb-4" />

        <form method="POST" action="{{ route('register') }}">
            @csrf

            <div>
                <x-label for="name" value="{{ __('Name') }}" />
                <x-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            </div>

            <div class="mt-4">
                <x-label for="email" value="{{ __('Email') }}" />
                <x-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            </div>

            <div class="mt-4">
                <x-label for="password" value="{{ __('Password') }}" />
                <x-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" />
            </div>

            <div class="mt-4">
                <x-label for="password_confirmation" value="{{ __('Confirm Password') }}" />
                <x-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
            </div>

            @if (Laravel\Jetstream\Jetstream::hasTermsAndPrivacyPolicyFeature())
                <div class="mt-4">
                    <x-label for="terms">
                        <div class="flex items-center">
                            <x-checkbox name="terms" id="terms" required />
                            <div class="ms-2">
                                {!! __('I agree to the :terms_of_service and :privacy_policy', [
                                    'terms_of_service' => '<a target="_blank" href="'.route('terms.show').'" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">'.__('Terms of Service').'</a>',
                                    'privacy_policy' => '<a target="_blank" href="'.route('policy.show').'" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">'.__('Privacy Policy').'</a>',
                                ]) !!}
                            </div>
                        </div>
                    </x-label>
                </div>
            @endif

            <div class="flex items-center justify-end mt-4">
                <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('login') }}">
                    {{ __('Already registered?') }}
                </a>

                <x-button class="ms-4">
                    {{ __('Register') }}
                </x-button>
            </div>
        </form>

        {{-- Divider --}}
        <div class="my-6 flex items-center gap-3">
            <div class="h-px flex-1 bg-gray-200"></div>
            <span class="text-xs text-gray-500">or</span>
            <div class="h-px flex-1 bg-gray-200"></div>
        </div>

        {{-- Google button (same id your JS listens to) --}}
        <button
             id="googleRegisterBtn" type="button"
            class="w-full inline-flex items-center justify-center gap-2 px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
            {{-- Simple Google “G” icon --}}
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" class="h-5 w-5" aria-hidden="true">
                <path fill="#FFC107" d="M43.6 20.5H42V20H24v8h11.3C33.4 32.4 29.1 36 24 36c-7 0-12.8-5.8-12.8-12.8S17 10.5 24 10.5c3.1 0 6 .9 8.3 2.9l5.7-5.7C34.4 4.6 29.5 3 24 3 12.3 3 2.8 12.5 2.8 24.2S12.3 45.5 24 45.5c11.4 0 21.2-8.3 21.2-21.3 0-1.6-.2-3.2-.6-4.7z"/>
                <path fill="#FF3D00" d="M6.3 14.7l6.6 4.8C14.7 16 19 13.4 24 13.4c3.1 0 6 .9 8.3 2.9l5.7-5.7C34.4 4.6 29.5 3 24 3 15.1 3 7.4 8.2 6.3 14.7z"/>
                <path fill="#4CAF50" d="M24 45.5c5 0 9.7-1.9 13.2-5.1l-6.1-5.2C29.3 36.8 26.8 37.8 24 37.8c-5 0-9.3-3.3-10.8-7.9l-6.7 5.2C8 41.7 15.4 45.5 24 45.5z"/>
                <path fill="#1976D2" d="M45.2 24.2c0-1.6-.2-3.2-.6-4.7H24v8h11.3c-.8 3.4-3 6.3-6.1 8.1l6.1 5.2c3.7-3.4 6.1-8.4 6.1-16.6z"/>
            </svg>
            {{ __('Continue with Google') }}
        </button>
    </x-authentication-card>
</x-guest-layout>
