<x-common-form-select
    :name="$field['name']"
    :title="$field['label']"
    :value="old($field['name'], $field['value'] ?? '')"
    :options="$field['options']"
    :emptyOption="$field['emptyOption'] ?? true" >
    @if (isset($field['description']))
        <div class="text-secondary"><small>{{ $field['description'] }}</small></div>
    @endif
</x-common-form-select> 