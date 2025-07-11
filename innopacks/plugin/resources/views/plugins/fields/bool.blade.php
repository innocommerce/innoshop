<x-common-form-switch-radio
    :name="$field['name']"
    :title="$field['label']"
    :value="old($field['name'], $field['value'] ?? '')">
    @if (isset($field['description']))
        <div class="text-secondary"><small>{{ $field['description'] }}</small></div>
    @endif
</x-common-form-switch-radio> 