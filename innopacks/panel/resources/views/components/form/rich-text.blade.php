@push('header')
<script src="{{ asset('vendor/tinymce/5.9.1/tinymce.min.js') }}"></script>
@endpush

<x-panel::form.row :title="$title" :required="$required" width="1000">
  @if (!$multiple)
    <textarea rows="4" type="text" name="{{ $name }}" class="tinymce" placeholder="{{ $title }}">{{ $value }}</textarea>
    {{ $slot }}
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
        <textarea rows="4" type="text" name="{{ $name }}[{{ $locale['code'] }}]" class="tinymce" placeholder="{{ $title }}">{{ $value[$locale['code']] ?? '' }}</textarea>
      </div>
      @endforeach
    </div>
  @endif
</x-panel::form.row>