<x-panel::form.row :title="$field['label']" :required="(bool)($field['required'] ?? false)">
    <div class="form-checkbox">
        @php
            $checkedValues = old($field['name'], $field['value'] ?? []);
            if (is_string($checkedValues)) {
                $checkedValues = json_decode($checkedValues, true);
            }
            if (!is_array($checkedValues)) {
                $checkedValues = [];
            }
        @endphp
        @foreach ($field['options'] as $item)
            <div class="form-check d-inline-block mt-2 me-3">
                <input
                    class="form-check-input"
                    name="{{ $field['name'] }}[]"
                    type="checkbox"
                    value="{{ $item['value'] }}"
                    {{ in_array($item['value'], $checkedValues) ? 'checked' : '' }}
                    id="flexCheck-{{ $field['name'] }}-{{ $loop->index }}">
                <label class="form-check-label" for="flexCheck-{{ $field['name'] }}-{{ $loop->index }}">
                    {{ $item['label'] }}
                </label>
            </div>
        @endforeach
    </div>
    @if (isset($field['description']))
        <div class="text-secondary"><small>{{ $field['description'] }}</small></div>
    @endif
</x-panel::form.row> 