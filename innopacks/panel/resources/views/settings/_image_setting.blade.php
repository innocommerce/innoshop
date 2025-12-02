<!-- Image Settings -->
<div class="tab-pane fade" id="tab-setting-image">
<div class="card mb-4">
  <div class="card-header">
    <h5 class="card-title mb-0">{{ __('panel/setting.image_settings') }}</h5>
    <p class="text-muted small mb-0">{{ __('panel/setting.image_settings_desc') }}</p>
  </div>
  <div class="card-body">
    <x-common-form-select 
      title="{{ __('panel/setting.image_resize_mode') }}" 
      name="image_resize_mode"
      :value="old('image_resize_mode', system_setting('image_resize_mode', 'cover'))"
      :options="[
        ['value' => 'cover', 'label' => __('panel/setting.image_resize_mode_cover')],
        ['value' => 'contain', 'label' => __('panel/setting.image_resize_mode_contain')],
        ['value' => 'pad', 'label' => __('panel/setting.image_resize_mode_pad')],
        ['value' => 'resize', 'label' => __('panel/setting.image_resize_mode_resize')],
        ['value' => 'width-cover', 'label' => __('panel/setting.image_resize_mode_width_cover')],
        ['value' => 'height-cover', 'label' => __('panel/setting.image_resize_mode_height_cover')]
      ]"
      key="value"
      label="label"
      :empty-option="false"
    />
    
    <x-panel::form.row :title="__('panel/setting.image_pad_color')">
      <div class="d-flex align-items-center">
        <input type="color" 
               id="image_pad_color_picker"
               value="{{ old('image_pad_color', system_setting('image_pad_color', '#ffffff')) }}"
               class="form-control form-control-color me-2" 
               style="width: 60px; height: 38px; cursor: pointer;"
               title="{{ __('panel/setting.image_pad_color') }}"
               onchange="document.getElementById('image_pad_color_input').value = this.value">
        <input type="text" 
               id="image_pad_color_input"
               name="image_pad_color" 
               value="{{ old('image_pad_color', system_setting('image_pad_color', '#ffffff')) }}"
               class="form-control" 
               style="max-width: 120px;"
               pattern="^#[0-9A-Fa-f]{6}$"
               placeholder="#ffffff"
               oninput="document.getElementById('image_pad_color_picker').value = this.value">
        <span class="text-muted ms-2 small">{{ __('panel/setting.image_pad_color_desc') }}</span>
      </div>
    </x-panel::form.row>
    
    <div class="text-secondary mt-3">
      <small>
        <strong>{{ __('panel/setting.image_resize_mode_cover') }}:</strong> {{ __('panel/setting.image_resize_mode_cover_desc') }}<br>
        <strong>{{ __('panel/setting.image_resize_mode_contain') }}:</strong> {{ __('panel/setting.image_resize_mode_contain_desc') }}<br>
        <strong>{{ __('panel/setting.image_resize_mode_pad') }}:</strong> {{ __('panel/setting.image_resize_mode_pad_desc') }}<br>
        <strong>{{ __('panel/setting.image_resize_mode_resize') }}:</strong> {{ __('panel/setting.image_resize_mode_resize_desc') }}<br>
        <strong>{{ __('panel/setting.image_resize_mode_width_cover') }}:</strong> {{ __('panel/setting.image_resize_mode_width_cover_desc') }}<br>
        <strong>{{ __('panel/setting.image_resize_mode_height_cover') }}:</strong> {{ __('panel/setting.image_resize_mode_height_cover_desc') }}
      </small>
    </div>
  </div>
</div>
</div>

