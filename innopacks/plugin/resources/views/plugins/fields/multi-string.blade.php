@php
    $value = $field['value'] ?? '';
    if (is_string($value)) {
        $value = json_decode($value, true) ?? [];
    }
@endphp
<x-common-form-input
    :name="$field['name']"
    :title="$field['label']"
    :placeholder="$field['placeholder'] ?? ''"
    :description="$field['description'] ?? ''"
    :error="$errors->first($field['name'])"
    :required="(bool)$field['required']"
    :is-locales="true"
    :multiple="true"
    :value="$value" /> 