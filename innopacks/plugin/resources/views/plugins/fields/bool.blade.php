<x-common-form-switch-radio
    :name="$field['name']"
    :title="$field['label']"
    :description="$field['description'] ?? ''"
    :value="old($field['name'], $field['value'] ?? '')" />
