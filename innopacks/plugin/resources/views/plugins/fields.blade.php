@foreach ($fields as $field)
    @includeIf('plugin::plugins.fields.' . $field['type'], ['field' => $field, 'errors' => $errors, 'plugin' => $plugin ?? null])
    
    @if(isset($field['divider_after']) && $field['divider_after'])
        @include('plugin::plugins.fields.divider_after', ['field' => $field, 'plugin' => $plugin ?? null])
    @endif
@endforeach 