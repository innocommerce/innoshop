<div class="accordion accordion-flush locales-accordion" id="data-locales-{{ $id }}">
  @foreach ($locales as $locale => $localeName)
    <div class="accordion-item">
      <h2 class="accordion-header">
        <button class="accordion-button {{ $loop->first ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#data-locale-{{ $locale }}" aria-expanded="{{ $loop->first ? 'true' : 'false' }}" aria-controls="data-locale-{{ $locale }}">
          <div class="wh-20 me-2"><img src="{{ asset('images/flag/'. $locale . '.png') }}" class="img-fluid"></div>
          {{ $localeName }}
        </button>
      </h2>
      <div id="data-locale-{{ $locale }}" class="accordion-collapse collapse {{ $loop->first ? 'show' : '' }}" data-bs-parent="#data-locales-{{ $id }}">
        <div class="accordion-body">
          {{ $slot }}
        </div>
      </div>
    </div>
  @endforeach
</div>