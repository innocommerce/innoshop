<x-panel::form.row :title="$title" :required="$required" :column="$column" :generate="$generate">
  @if (!$multiple)
  <div>
    <textarea rows="4" type="text" name="{{ $name }}" class="form-control" @if ($required) required @endif placeholder="{{ $title }}">{{ $value }}</textarea>
    <span class="invalid-feedback" role="alert">
      请填写 {{ $title }}
    </span>
  </div>
  @else
  <ul class="nav nav-tabs w-max-1000 mb-2" id="myTab" role="tablist">
    @foreach (locales() as $locale)
      <li class="nav-item" role="presentation">
        <button class="nav-link {{ $loop->first ? 'active' : ''}}" id="{{ $locale['code'] }}" data-bs-toggle="tab" data-bs-target="#{{ $name }}-{{ $locale['code'] }}-pane" type="button">
          <img src="{{ asset('images/flag/'. $locale['code'] .'.png') }}" class="me-2" style="width: 20px;">
          {{ $locale['name'] }}
        </button>
      </li>
    @endforeach
  </ul>

  <div class="tab-content w-max-1000" id="">
    @foreach (locales() as $locale)
    <div class="tab-pane fade {{ $loop->first ? 'show active' : ''}}" id="{{ $name }}-{{ $locale['code'] }}-pane" role="tabpanel" aria-labelledby="{{ $locale['code'] }}">
      <textarea rows="4" type="text" name="{{ $name }}[{{ $locale['code'] }}]" class="form-control" @if ($required) required @endif placeholder="{{ $title }}">{{ $value[$locale['code']] ?? '' }}</textarea>
    </div>
    @endforeach
  </div>
@endif
  {{ $slot }}
</x-panel::form.row>
