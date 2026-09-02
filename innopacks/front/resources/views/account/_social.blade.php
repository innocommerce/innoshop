@if(collect(system_setting('social'))->where('active', true)->count())
  <div class="auth-divider">
    <span class="auth-divider-text">{{ theme_trans('front.auth_or_continue_with') }}</span>
  </div>

  <div class="social-login">
    @foreach(system_setting('social') as $provider)
      @if($provider['active'])
        @php
          $providerKey = strtolower($provider['provider']);
          $labelMap = [
            'github'   => 'GitHub',
            'google'   => 'Google',
            'facebook' => 'Facebook',
            'twitter'  => 'Twitter',
            'x'        => 'X',
            'linkedin' => 'LinkedIn',
            'apple'    => 'Apple',
          ];
          $label = $labelMap[$providerKey] ?? ucfirst($providerKey);
        @endphp
        <a href="javascript:void(0)"
           onclick="openSocialLogin('{{ front_root_route('social.redirect', ['provider' => $provider['provider']]) }}')"
           class="social-button"
           data-provider="{{ $providerKey }}">
          <span class="social-icon" aria-hidden="true">
            @switch($providerKey)
              @case('github')
                <svg viewBox="0 0 24 24" fill="#181717"><path d="M12 .5C5.7.5.5 5.7.5 12c0 5 3.3 9.3 7.8 10.8.6.1.8-.2.8-.5v-1.7c-3.2.7-3.9-1.5-3.9-1.5-.5-1.3-1.3-1.7-1.3-1.7-1.1-.7 0-.7 0-.7 1.2.1 1.8 1.2 1.8 1.2 1.1 1.8 2.8 1.3 3.5 1 .1-.8.4-1.3.8-1.6-2.5-.3-5.2-1.3-5.2-5.6 0-1.2.4-2.3 1.2-3.1 0-.4-.5-1.5.1-3.1 0 0 1-.3 3.2 1.2 1-.3 2-.4 3-.4s2 .1 3 .4c2.2-1.5 3.2-1.2 3.2-1.2.6 1.6.2 2.7.1 3.1.8.8 1.2 1.9 1.2 3.1 0 4.3-2.6 5.3-5.2 5.6.4.4.8 1.1.8 2.2v3.3c0 .3.2.7.8.5 4.5-1.6 7.8-5.9 7.8-10.8C23.5 5.7 18.3.5 12 .5z"/></svg>
                @break
              @case('google')
                <svg viewBox="0 0 24 24"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/></svg>
                @break
              @case('facebook')
                <svg viewBox="0 0 24 24" fill="#1877F2"><path d="M24 12c0-6.63-5.37-12-12-12S0 5.37 0 12c0 5.99 4.39 10.95 10.13 11.85V15.47H7.08V12h3.05V9.36c0-3.01 1.79-4.67 4.53-4.67 1.31 0 2.69.23 2.69.23v2.95h-1.51c-1.49 0-1.95.93-1.95 1.87V12h3.32l-.53 3.47h-2.79v8.38C19.61 22.95 24 17.99 24 12z"/></svg>
                @break
              @case('twitter')
              @case('x')
                <svg viewBox="0 0 24 24" fill="#000000"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                @break
              @case('linkedin')
                <svg viewBox="0 0 24 24" fill="#0A66C2"><path d="M20.45 20.45h-3.55v-5.57c0-1.33-.02-3.04-1.85-3.04-1.85 0-2.14 1.45-2.14 2.95v5.66H9.36V9h3.41v1.56h.05c.48-.9 1.64-1.85 3.37-1.85 3.6 0 4.27 2.37 4.27 5.45v6.29zM5.34 7.43a2.06 2.06 0 11.01-4.12 2.06 2.06 0 010 4.12zM7.12 20.45H3.55V9h3.57v11.45zM22.22 0H1.77C.79 0 0 .77 0 1.72v20.56C0 23.23.79 24 1.77 24h20.45c.98 0 1.78-.77 1.78-1.72V1.72C24 .77 23.2 0 22.22 0z"/></svg>
                @break
              @case('apple')
                <svg viewBox="0 0 24 24" fill="#000000"><path d="M17.05 20.28c-.98.95-2.05.8-3.08.35-1.09-.46-2.09-.48-3.24 0-1.44.62-2.2.44-3.06-.35C2.79 15.25 3.51 7.59 9.05 7.31c1.35.07 2.29.74 3.08.8 1.18-.24 2.31-.93 3.57-.84 1.51.12 2.65.72 3.4 1.8-3.12 1.87-2.38 5.98.48 7.13-.57 1.5-1.31 2.99-2.54 4.09l.01-.01zM12.03 7.25c-.15-2.23 1.66-4.07 3.74-4.25.29 2.58-2.34 4.5-3.74 4.25z"/></svg>
                @break
              @default
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 8v8M8 12h8"/></svg>
            @endswitch
          </span>
          <span class="social-label">{{ $label }}</span>
        </a>
      @endif
    @endforeach
  </div>
@endif

@push('footer')
  <script>
    function openSocialLogin(url) {
      const width = 600;
      const height = 600;
      const left = (window.innerWidth / 2) - (width / 2);
      const top = (window.innerHeight / 2) - (height / 2);
      window.open(url, 'socialLogin', `width=${width},height=${height},top=${top},left=${left}`);
    }
  </script>
@endpush