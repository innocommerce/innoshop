@if(collect(system_setting('social'))->where('active', true)->count())
  <div class="d-flex align-items-center mt-3">
    <div class="line"></div>
    <div class="word fs-3 mb-1 mx-3">or</div>
    <div class="line"></div>
  </div>

  <div class="d-flex flex-wrap justify-content-center">
    @foreach(system_setting('social') as $provider)
      @if($provider['active'])
        <div class="social-button mt-4 mx-4 d-flex align-items-center justify-content-center">
          <a href="javascript:void(0)"
             onclick="openSocialLogin('{{ front_root_route('social.redirect', ['provider' => $provider['provider']]) }}')"
             class="d-flex align-items-center justify-content-center w-100 text-decoration-none text-white fs-4">
            <i class="bi bi-{{ $provider['provider'] }} fs-3"></i>
          </a>
        </div>
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