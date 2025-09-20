@extends('panel::layouts.app')
@section('body-class', 'page-product-option-form')
@section('title', $option->id ? '编辑选项' : '创建选项')

<x-panel::form.right-btns formid="option-form" />

@section('content')
  <form class="needs-validation no-load" novalidate
    action="{{ $option->id ? panel_route('option_values.update', [$option->id]) : panel_route('option_values.store') }}"
    method="POST" id="option-form" enctype="multipart/form-data">
    @csrf
    @method($option->id ? 'PUT' : 'POST')

    <div class="row">
      <div class="col-12 col-md-12">
        <div class="card mb-3">
          <div class="card-body">
            <ul class="nav nav-tabs" id="myTab" role="tablist">
              <li class="nav-item" role="presentation">
                <button class="nav-link active" id="basic-tab" data-bs-toggle="tab" data-bs-target="#basic-tab-pane"
                  type="button" role="tab" aria-controls="basic-tab-pane"
                  aria-selected="true">基本信息</button>
              </li>
              <li class="nav-item" role="presentation">
                <button class="nav-link" id="translation-tab" data-bs-toggle="tab" data-bs-target="#translation-tab-pane"
                  type="button" role="tab" aria-controls="translation-tab-pane"
                  aria-selected="false">多语言</button>
              </li>
            </ul>

            <div class="tab-content" id="myTabContent">
              <!-- 基本信息标签页 -->
              <div class="tab-pane fade show active" id="basic-tab-pane" role="tabpanel" aria-labelledby="basic-tab">
                <div class="row">
                  <div class="col-12 col-md-6">
                    <x-panel-form-input name="option_group_id" title="选项组" :value="old('option_group_id', $option->option_group_id ?? '')" required>
                      <select name="option_group_id" class="form-select" required>
                        <option value="">请选择选项组</option>
                        @foreach($optionGroups as $group)
                          <option value="{{ $group->id }}" {{ old('option_group_id', $option->option_group_id ?? '') == $group->id ? 'selected' : '' }}>
                            {{ $group->getCurrentName() }} ({{ $group->type }})
                          </option>
                        @endforeach
                      </select>
                    </x-panel-form-input>
                  </div>

                  <div class="col-12 col-md-6">
                    <x-panel-form-input name="price" title="价格调整" :value="old('price', $option->price ?? 0)" type="number" step="0.01">
                      <div class="input-group">
                        <input type="number" name="price" class="form-control" 
                               value="{{ old('price', $option->price ?? 0) }}" 
                               step="0.01" placeholder="0.00">
                        <span class="input-group-text">{{ currency_symbol() }}</span>
                      </div>
                      <div class="form-text">正数表示增加价格，负数表示减少价格</div>
                    </x-panel-form-input>
                  </div>

                  <div class="col-12 col-md-6">
                    <x-panel-form-input name="position" title="排序" :value="old('position', $option->position ?? 0)" type="number">
                    </x-panel-form-input>
                  </div>

                  <div class="col-12 col-md-6">
                    <x-panel-form-switch-radio name="active" title="{{ __('panel/common.active') }}" :value="old('active', $option->active ?? true)">
                    </x-panel-form-switch-radio>
                  </div>

                  <div class="col-12">
                    <x-panel-form-input name="image" title="选项图片" type="file">
                      @if($option->image ?? false)
                        <div class="mb-2">
                          <img src="{{ image_resize($option->image, 100, 100) }}" class="img-thumbnail" style="max-width: 100px;">
                          <div class="form-text">当前图片</div>
                        </div>
                      @endif
                      <input type="file" name="image" class="form-control" accept="image/*">
                      <div class="form-text">支持 JPG、PNG、GIF 格式，建议尺寸 200x200 像素</div>
                    </x-panel-form-input>
                  </div>
                </div>
              </div>

              <!-- 多语言标签页 -->
              <div class="tab-pane fade" id="translation-tab-pane" role="tabpanel" aria-labelledby="translation-tab">
                @foreach (locales() as $locale)
                  <div class="mb-4">
                    <h6 class="mb-3">{{ $locale['name'] }}</h6>
                    <div class="row">
                      <div class="col-12">
                        <x-panel-form-input 
                          name="translations[{{ $locale['code'] }}][name]" 
                          title="选项名称" 
                          :value="old('translations.' . $locale['code'] . '.name', $option->translate($locale['code'])->name ?? '')"
                          :required="$locale['code'] == locale_code()">
                        </x-panel-form-input>
                      </div>
                    </div>
                  </div>
                @endforeach
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </form>
@endsection

@push('footer')
  <script>
    $(document).ready(function() {
      // Form validation
      $('#option-form').on('submit', function(e) {
        const form = this;
        if (!form.checkValidity()) {
          e.preventDefault();
          e.stopPropagation();
        }
        form.classList.add('was-validated');
      });

      // Image preview
      $('input[name="image"]').on('change', function(e) {
        const file = e.target.files[0];
        if (file) {
          const reader = new FileReader();
          reader.onload = function(e) {
            const preview = $('<div class="mt-2"><img src="' + e.target.result + '" class="img-thumbnail" style="max-width: 100px;"><div class="form-text">预览图片</div></div>');
            $('input[name="image"]').parent().find('.mt-2').remove();
            $('input[name="image"]').parent().append(preview);
          };
          reader.readAsDataURL(file);
        }
      });
    });
  </script>
@endpush