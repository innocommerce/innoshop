@foreach ($fields as $field)
    @includeIf('plugin::plugins.fields.' . $field['type'], ['field' => $field, 'errors' => $errors])
@endforeach 