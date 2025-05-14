<x-panel::form.row :title="$title" :required="$required" :locale-code="$localeCode ?? ''" :column="$column" :generate="$generate" :translate="$translate">
  @if (!$multiple)
  <div>
    <textarea rows="4" type="text" name="{{ $name }}" class="form-control" @if ($required) required @endif placeholder="{{ $title }}" data-column="{{ $column ?? '' }}" data-lang="{{ $localeCode ?? '' }}">{{ $value }}</textarea>
    <span class="invalid-feedback" role="alert">
      请填写 {{ $title }}
    </span>
    @if ($description ?? '')
    <div class="text-secondary"><small>{!! $description !!}</small></div>
    @endif
  </div>
  @else
  <ul class="nav nav-tabs w-max-1000 mb-2" id="myTab" role="tablist">
    @foreach (locales() as $locale)
      <li class="nav-item" role="presentation">
        <button class="nav-link d-flex {{ $loop->first ? 'active' : ''}}" id="{{ $locale['code'] }}" data-bs-toggle="tab" data-bs-target="#{{ $name }}-{{ $locale['code'] }}-pane" type="button">
          <div class="wh-20 me-2">
            <img src="{{ asset('images/flag/'. $locale['code'].'.png') }}" class="img-fluid {{ default_locale_class($locale->code) }}">
          </div>
          {{ $locale['name'] }}
        </button>
      </li>
    @endforeach
  </ul>

  <div class="tab-content w-max-1000" id="">
    @foreach (locales() as $locale)
      <div class="tab-pane fade {{ $loop->first ? 'show active' : ''}}" id="{{ $name }}-{{ $locale['code'] }}-pane" role="tabpanel" aria-labelledby="{{ $locale['code'] }}">
      @if(is_object($value))
        @php ($o_value = $value->where('locale', $locale['code'])->first())
        <input type="hidden" name="translations[{{ $locale['code'] }}][locale]" value="{{ $locale['code'] }}">
        <textarea rows="4" type="text" name="translations[{{ $locale['code'] }}][{{ $name }}]" class="form-control" @if ($required) required @endif placeholder="{{ $title }}" data-column="{{ $column ?? '' }}" data-lang="{{ $locale['code'] }}">{{ $o_value->description ?? '' }}</textarea>
      @else
        <textarea rows="4" type="text" name="{{ $name }}[{{ $locale['code'] }}]" class="form-control" @if ($required) required @endif placeholder="{{ $title }}" data-column="{{ $column ?? '' }}" data-lang="{{ $locale['code'] }}">{{ $value[$locale['code']] ?? '' }}</textarea>
      @endif
      </div>
    @endforeach
  </div>
@endif
  {{ $slot }}
</x-panel::form.row>
