<x-common-form-textarea
    :name="$field['name']"
    :title="$field['label']"
    :required="(bool)$field['required']"
    :value="old($field['name'], $field['value'] ?? '')">
    @if (isset($field['description']))
        <div class="text-secondary"><small>{{ $field['description'] }}</small></div>
    @endif
</x-common-form-textarea> 