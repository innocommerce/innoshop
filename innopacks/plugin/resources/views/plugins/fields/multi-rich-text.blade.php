<x-common-form-rich-text
    :name="$field['name']"
    :title="$field['label']"
    :value="old($field['name'], $field['value'] ?? '')"
    :required="(bool)$field['required']"
    :is-locales="true">
    @if (isset($field['description']))
        <div class="text-secondary"><small>{{ $field['description'] }}</small></div>
    @endif
</x-common-form-rich-text> 