@props([
    'generate'    => false,
    'column'      => '',
    'entityType'  => '',
    'entityId'    => 0,
])

@pushonce('header')
  <script src="{{ asset('vendor/tinymce/5.9.1/tinymce.min.js') }}"></script>
@endpushonce

<x-panel::form.row :title="$title" :required="$required" width="1000" :translate="$translate" :elID="$elID">
  @if (!$multiple)
    <textarea rows="4" type="text" name="{{ $name }}" class="tinymce" placeholder="{{ $title }}"
      @if ($elID) id="{{ $elID }}" @endif
      @if($maxlength ?? '') data-maxlength="{{ $maxlength }}" @endif
      @if($generate) data-column="{{ $column }}" data-entity-type="{{ $entityType }}" data-entity-id="{{ $entityId }}" @endif
      data-is-rich-text="true">{{ $value }}</textarea>
    {{ $slot }}
  @else
    <ul class="nav nav-tabs w-max-1000 mb-2" id="myTab" role="tablist">
      @foreach (locales() as $locale)
        <li class="nav-item" role="presentation">
          <button class="nav-link {{ $loop->first ? 'active' : '' }}" id="{{ $name }}-{{ $locale['code'] }}-tab" data-bs-toggle="tab"
            data-bs-target="#{{ $name }}-{{ $locale['code'] }}-pane" type="button">
            <img src="{{ asset('images/flags/' . $locale['code'] . '.svg') }}" class="me-2" style="width: 20px;">
            {{ $locale['name'] }}
          </button>
        </li>
      @endforeach
    </ul>

    <div class="tab-content w-max-1000" id="">
      @foreach (locales() as $locale)
        <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}"
          id="{{ $name }}-{{ $locale['code'] }}-pane" role="tabpanel"
          aria-labelledby="{{ $name }}-{{ $locale['code'] }}-tab">
          <textarea rows="4" type="text" name="translations[{{ $locale['code'] }}][{{ $name }}]"
            class="tinymce" placeholder="{{ $title }}" @if($maxlength ?? '') data-maxlength="{{ $maxlength }}" @endif
            @if($generate) data-column="{{ $column }}" data-entity-type="{{ $entityType }}" data-entity-id="{{ $entityId }}" @endif
            data-is-rich-text="true">{{ $value[$locale['code']] ?? '' }}</textarea>
        </div>
      @endforeach
    </div>
  @endif
</x-panel::form.row>
