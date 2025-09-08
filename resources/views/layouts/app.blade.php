<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>{{ config('app.name', 'Laravel') }}</title>

  @vite(['resources/css/app.css', 'resources/js/app.js'])
  @livewireStyles

<script>
if (typeof window.api !== 'function') {
  let primed = false;
  const getCookie = (name) => {
    const m = document.cookie.split('; ').find(c => c.startsWith(name + '='));
    return m ? decodeURIComponent(m.split('=')[1]) : null;
  };

  window.api = async (url, options = {}) => {
    const method = (options.method || 'GET').toUpperCase();
    options.method = method;
    options.credentials = 'include'; // send session cookie
    options.headers = Object.assign(
      { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
      options.headers || {}
    );

    if (['POST','PUT','PATCH','DELETE'].includes(method)) {
      if (!primed || !getCookie('XSRF-TOKEN')) {
        await fetch('/sanctum/csrf-cookie', { credentials: 'include' });
        primed = true;
      }
      const xsrf = getCookie('XSRF-TOKEN');
      if (xsrf) options.headers['X-XSRF-TOKEN'] = xsrf;
    }
    return fetch(url, options);
  };
}
</script>
</head>
<body class="font-sans antialiased">
  {{-- SINGLE ROOT WRAPPER FOR LIVEWIRE FULL-PAGE COMPONENTS --}}
  <div id="layout-root">
    <x-banner />

    <div class="min-h-screen bg-gray-100">
      @livewire('navigation-menu')

      @if (false)
          {{-- header removed --}}
      @endif


      <main>
        {{ $slot }}
      </main>
    </div>
  </div>

  @stack('modals')
  @livewireScripts
</body>
</html>
