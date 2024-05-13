<x-panel::form.row :title="$title" :required="$required">
  <select class="form-select me-3" name="{{ $name }}">
    @foreach ($options as $option)
      <option value="{{ $option[$key] }}" {{ $option[$key] == $value ? 'selected': '' }}>{{ $option[$label] }}</option>
    @endforeach
  </select>
  {{ $slot }}
</x-panel::form.row>
