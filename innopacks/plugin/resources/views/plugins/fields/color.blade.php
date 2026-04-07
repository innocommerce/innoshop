<div class="mb-3">
    <label class="form-label">{{ $field['label'] ?? $field['label_key'] ?? $field['name'] }}</label>
    <div class="d-flex align-items-center gap-2">
        <input type="color"
               class="form-control form-control-color"
               name="{{ $field['name'] }}"
               id="field-{{ $field['name'] }}"
               value="{{ old($field['name'], $field['value'] ?? '#4f46e5') }}"
               oninput="document.getElementById('field-{{ $field['name'] }}-text').value=this.value"
               {{ isset($field['required']) && $field['required'] ? 'required' : '' }}>
        <input type="text"
               class="form-control form-control-sm"
               id="field-{{ $field['name'] }}-text"
               value="{{ old($field['name'], $field['value'] ?? '#4f46e5') }}"
               maxlength="7"
               style="width:100px"
               oninput="if(/^#[0-9a-fA-F]{6}$/.test(this.value)){document.getElementById('field-{{ $field['name'] }}').value=this.value}">
    </div>
    @if(isset($field['hint']))
        <small class="text-muted">{{ $field['hint'] }}</small>
    @endif
</div>
