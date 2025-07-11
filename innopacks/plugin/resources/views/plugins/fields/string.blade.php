<x-common-form-input
    :name="$field['name']"
    :title="$field['label']"
    :placeholder="$field['placeholder'] ?? ''"
    :description="$field['description'] ?? ''"
    :error="$errors->first($field['name'])"
    :required="(bool)($field['required'] ?? false)"
    :value="old($field['name'], $field['value'] ?? '')" /> 