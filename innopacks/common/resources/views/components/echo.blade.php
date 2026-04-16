@if(config('broadcasting.default') === 'reverb')
<script src="{{ asset('vendor/pusher-js/pusher.min.js') }}"></script>
<script src="{{ asset('vendor/laravel-echo/echo.iife.js') }}"></script>
<script>
  (function() {
    try {
      window.Echo = new Echo.default({
        broadcaster: 'reverb',
        key: '{{ config('broadcasting.connections.reverb.key') }}',
        wsHost: '{{ config('broadcasting.connections.reverb.options.host', 'localhost') }}',
        wsPort: {{ config('broadcasting.connections.reverb.options.port', 8080) }},
        wssPort: {{ config('broadcasting.connections.reverb.options.port', 8080) }},
        forceTLS: {{ config('broadcasting.connections.reverb.options.scheme') === 'https' ? 'true' : 'false' }},
        enabledTransports: ['ws', 'wss'],
        disabledTransports: ['sockjs'],
      });
    } catch (e) {
      console.warn('[Reverb] Echo initialization failed:', e.message);
    }
  })();
</script>
@endif
