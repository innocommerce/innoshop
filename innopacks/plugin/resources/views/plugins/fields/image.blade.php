<x-common-form-image
    :name="$field['name']"
    :title="$field['label']"
    :description="$field['description'] ?? ''"
    :error="$errors->first($field['name'])"
    :required="(bool)$field['required']"
    :value="old($field['name'], $field['value'] ?? '')">
    @if ($field['recommend_size'] ?? false)
        <div class="text-secondary"><small>{{ __('common.recommend_size') }} {{ $field['recommend_size'] }}</small></div>
    @endif
</x-common-form-image> 