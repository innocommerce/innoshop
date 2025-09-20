@extends('panel::layouts.app')
@section('body-class', 'page-product-option-value')
@section('title', '选项值管理')

@section('content')
  <div class="card h-min-600" id="app">
    <div class="card-body">
      <!-- 导航链接 -->
      <ul class="nav nav-tabs mb-4">
        <li class="nav-item">
          <a class="nav-link" href="{{ panel_route('options.index') }}">
            <i class="bi bi-collection"></i> 选项组管理
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link active" href="{{ panel_route('option_values.index') }}">
            <i class="bi bi-list-ul"></i> 选项值管理
          </a>
        </li>
      </ul>

      <!-- 选项值管理内容 -->
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">选项值管理</h5>
        <button type="button" class="btn btn-primary" onclick="openCreateModal()">
          <i class="bi bi-plus-square"></i> {{ __('panel/common.create') }}
        </button>
      </div>

      <x-panel-data-criteria :criteria="$criteria ?? []" :action="panel_route('option_values.index')" />

      <!-- 选项值列表 -->
      @if ($optionValues->count())
        <div class="table-responsive">
          <table class="table align-middle">
            <thead>
            <tr>
              <th>{{ __('panel/common.id') }}</th>
              <th>{{ __('panel/common.name') }}</th>
              <th>选项组</th>
              <th>图片</th>
              <th>排序</th>
              <th>{{ __('panel/common.active') }}</th>
              <th>{{ __('panel/common.created_at') }}</th>
              <th>{{ __('panel/common.actions') }}</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($optionValues as $optionValue)
              <tr>
                <td>{{ $optionValue->id }}</td>
                <td>{{ $optionValue->currentName }}</td>
                <td>
                  @if($optionValue->option)
                    <span class="badge bg-secondary">{{ $optionValue->option->currentName }}</span>
                  @else
                    <span class="text-muted">-</span>
                  @endif
                </td>
                <td>
                  @if($optionValue->image)
                    <img src="{{ $optionValue->getImageUrl() }}" alt="选项值图片" class="img-thumbnail" style="width: 40px; height: 40px;">
                  @else
                    <span class="text-muted">-</span>
                  @endif
                </td>
                <td>{{ $optionValue->position }}</td>
                <td>
                  @if ($optionValue->active)
                    <span class="badge bg-success">{{ __('panel/common.active') }}</span>
                  @else
                    <span class="badge bg-secondary">{{ __('panel/common.inactive') }}</span>
                  @endif
                </td>
                <td>{{ $optionValue->created_at }}</td>
                <td>
                  <button type="button" class="btn btn-outline-primary btn-sm" onclick="openEditModal({{ $optionValue->id }})">
                    <i class="bi bi-pencil-square"></i> {{ __('panel/common.edit') }}
                  </button>
                  <button type="button" class="btn btn-outline-danger btn-sm" onclick="if(confirm('确定要删除这个选项值吗？')) { document.getElementById('delete-form-{{ $optionValue->id }}').submit(); }">
                    <i class="bi bi-trash"></i> {{ __('panel/common.delete') }}
                  </button>
                  <form id="delete-form-{{ $optionValue->id }}" action="{{ panel_route('option_values.destroy', $optionValue) }}" method="POST" style="display: none;">
                    @csrf
                    @method('DELETE')
                  </form>
                </td>
              </tr>
            @endforeach
            </tbody>
          </table>
        </div>

        <div class="d-flex justify-content-center">
          {{ $optionValues->withQueryString()->links('panel::vendor/pagination/bootstrap-4') }}
        </div>
      @else
        <x-common-no-data />
      @endif
    </div>
  </div>

  <!-- 选项值编辑模态框 -->
  <div class="modal fade" id="optionValueModal" tabindex="-1" aria-labelledby="optionValueModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="optionValueModalLabel">添加选项值</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form id="optionValueForm" method="POST">
          @csrf
          <input type="hidden" name="_method" value="POST" id="form-method">
          
          <!-- 错误信息显示区域 -->
          <div id="form-errors" class="alert alert-danger d-none mx-3 mt-3" role="alert">
            <ul class="mb-0" id="error-list"></ul>
          </div>
          
          <div class="modal-body">
            <div class="row">
              <!-- 基本信息 -->
              <div class="col-12">
                <h6 class="mb-3">基本信息</h6>
              </div>
              
              <div class="col-12 col-md-6">
                <div class="mb-3">
                  <label for="option_id" class="form-label">选项组 <span class="text-danger">*</span></label>
                  <select name="option_id" id="option_id" class="form-select" required>
                    @foreach($allOptionGroups as $group)
                      <option value="{{ $group->id }}">{{ $group->currentName }} ({{ $group->type }})</option>
                    @endforeach
                  </select>
                </div>
              </div>
              
              <div class="col-12 col-md-6">
                <div class="mb-3">
                  <label for="image" class="form-label">图片</label>
                  <div class="is-up-file" data-type="image">
                    <div class="img-upload-item bg-light wh-80 rounded border d-flex justify-content-center align-items-center me-2 mb-2 position-relative cursor-pointer overflow-hidden">
                      <div class="position-absolute tool-wrap d-none d-flex top-0 start-0 w-100 bg-primary bg-opacity-75">
                        <div class="show-img w-100 text-center"><i class="bi bi-eye text-white"></i></div>
                        <div class="w-100 delete-img text-center"><i class="bi bi-trash text-white"></i></div>
                      </div>
                      <div class="img-info rounded h-100 w-100 d-flex justify-content-center align-items-center">
                        <i class="bi bi-plus fs-1 text-secondary opacity-75"></i>
                      </div>
                      <input type="hidden" value="" name="image" id="image">
                    </div>
                  </div>
                  <div class="form-text">可选，选项值的图片</div>
                </div>
              </div>
              
              <div class="col-12 col-md-6">
                <div class="mb-3">
                  <label for="position" class="form-label">排序</label>
                  <input type="number" name="position" id="position" class="form-control" value="0">
                </div>
              </div>
              
              <div class="col-12 col-md-6">
                <div class="mb-3">
                  <label class="form-label">状态</label>
                  <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="active" id="active" value="1" checked>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- 多语言信息 -->
            <div class="mt-4">
              <h6 class="mb-3">多语言信息</h6>
              <ul class="nav nav-tabs" id="languageTab" role="tablist">
                @foreach (locales() as $index => $locale)
                  <li class="nav-item" role="presentation">
                    <button class="nav-link d-flex align-items-center {{ $index === 0 ? 'active' : '' }}" 
                            id="lang-{{ $locale['code'] }}-tab" 
                            data-bs-toggle="tab" 
                            data-bs-target="#lang-{{ $locale['code'] }}-pane"
                            type="button" role="tab">
                      <div class="wh-20 me-2">
                        <img src="{{ asset('images/flag/'. $locale['code'].'.png') }}" 
                             class="img-fluid" 
                             alt="{{ $locale['name'] }}">
                      </div>
                      {{ $locale['name'] }}
                    </button>
                  </li>
                @endforeach
              </ul>
              
              <div class="tab-content mt-3" id="languageTabContent">
                @foreach (locales() as $index => $locale)
                  <div class="tab-pane fade {{ $index === 0 ? 'show active' : '' }}" 
                       id="lang-{{ $locale['code'] }}-pane" 
                       role="tabpanel">
                    <div class="mb-3">
                      <label for="name_{{ $locale['code'] }}" class="form-label">
                        选项名称 ({{ $locale['name'] }})
                        @if($locale['code'] == locale_code())
                          <span class="text-danger">*</span>
                        @endif
                      </label>
                      <input type="text" 
                             name="name[{{ $locale['code'] }}]" 
                             id="name_{{ $locale['code'] }}" 
                             class="form-control"
                             {{ $locale['code'] == locale_code() ? 'required' : '' }}>
                    </div>
                  </div>
                @endforeach
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
            <button type="submit" class="btn btn-primary">保存</button>
          </div>
        </form>
      </div>
    </div>
  </div>

@endsection

@push('footer')
<script>
  let isEditMode = false;
  let currentOptionValueId = null;

  /**
   * 打开创建选项值模态框
   */
  function openCreateModal() {
    isEditMode = false;
    currentOptionValueId = null;
    
    // 重置表单
    $('#optionValueForm')[0].reset();
    
    // 设置模态框标题
    $('#optionValueModalLabel').text('创建选项值');
    
    // 设置表单action和method
    $('#optionValueForm').attr('action', '{{ panel_route("option_values.store") }}');
    $('#form-method').val('POST'); // 设置为POST方法
    
    // 清空表单字段
    $('#option_group_id').val('');
    $('#price').val('');
    $('#position').val('0');
    $('#active').prop('checked', true);
    
    // 清空多语言字段
    @foreach (locales() as $locale)
      $('#name_{{ $locale['code'] }}').val('');
    @endforeach
    
    // 显示模态框
    $('#optionValueModal').modal('show');
  }

  /**
   * 打开编辑选项值模态框
   * @param {number} optionValueId - 选项值ID
   */
  function openEditModal(optionValueId) {
    isEditMode = true;
    currentOptionValueId = optionValueId;
    
    // 设置模态框标题
    $('#optionValueModalLabel').text('编辑选项值');
    
    // 设置表单action和method
    $('#optionValueForm').attr('action', '{{ panel_route("option_values.update", ":id") }}'.replace(':id', optionValueId));
    $('#form-method').val('PUT'); // 设置为PUT方法
    
    // 显示加载状态
    $('#optionValueModal').modal('show');
    
    // 获取选项值数据
    $.get('{{ panel_route("option_values.show", ":id") }}'.replace(':id', optionValueId))
      .done(function(data) {
        // 调试：打印获取到的数据
        console.log('获取到的选项值数据:', data);
        
        // 填充基本信息
        $('#option_id').val(data.option_id);
        $('#position').val(data.position);
        $('#active').prop('checked', data.active == 1);
        
        // 填充图片字段
        if (data.image) {
          const imageContainer = $('.is-up-file .img-upload-item');
          const imageUrl = data.image.indexOf('http') === 0 ? data.image : '{{ asset("") }}' + data.image;
          imageContainer.find('input[name="image"]').val(data.image);
          imageContainer.find('.tool-wrap').removeClass('d-none');
          imageContainer.find('.img-info').html('<img src="' + imageUrl + '" class="img-fluid" data-origin-img="' + imageUrl + '">');
        } else {
          // 重置图片组件为默认状态
          const imageContainer = $('.is-up-file .img-upload-item');
          imageContainer.find('input[name="image"]').val('');
          imageContainer.find('.tool-wrap').addClass('d-none');
          imageContainer.find('.img-info').html('<i class="bi bi-plus fs-1 text-secondary opacity-75"></i>');
        }
        
        // 填充多语言数据
        if (data.name) {
          console.log('多语言数据:', data.name);
          @foreach (locales() as $locale)
            $('#name_{{ $locale['code'] }}').val(data.name['{{ $locale['code'] }}'] || '');
          @endforeach
        }
      })
      .fail(function() {
        layer.msg('获取选项值数据失败', {icon: 2});
        $('#optionValueModal').modal('hide');
      });
  }

  /**
   * 处理表单提交
   */
  $('#optionValueForm').on('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = $(this).find('button[type="submit"]');
    const originalText = submitBtn.text();
    
    // 显示加载状态
    submitBtn.prop('disabled', true).text('保存中...');
    
    $.ajax({
      url: $(this).attr('action'),
      method: 'POST',
      data: formData,
      processData: false,
      contentType: false,
      success: function(response) {
        layer.msg(isEditMode ? '选项值更新成功' : '选项值创建成功', {icon: 1});
        $('#optionValueModal').modal('hide');
        
        // 刷新页面
        setTimeout(function() {
          window.location.reload();
        }, 1000);
      },
      error: function(xhr) {
        // 清空之前的错误信息
        $('#form-errors').addClass('d-none');
        $('#error-list').empty();
        
        let errorMessage = '操作失败';
        let hasErrors = false;
        
        if (xhr.responseJSON && xhr.responseJSON.errors) {
          const errors = xhr.responseJSON.errors;
          hasErrors = true;
          
          // 显示所有验证错误
          Object.keys(errors).forEach(function(field) {
            const fieldErrors = errors[field];
            if (Array.isArray(fieldErrors)) {
              fieldErrors.forEach(function(error) {
                $('#error-list').append('<li>' + error + '</li>');
              });
            }
          });
          
          $('#form-errors').removeClass('d-none');
        } else if (xhr.responseJSON && xhr.responseJSON.message) {
          errorMessage = xhr.responseJSON.message;
          $('#error-list').append('<li>' + errorMessage + '</li>');
          $('#form-errors').removeClass('d-none');
          hasErrors = true;
        }
        
        // 如果没有具体错误信息，使用layer.msg显示通用错误
        if (!hasErrors) {
          layer.msg(errorMessage, {icon: 2});
        }
      },
      complete: function() {
        // 恢复按钮状态
        submitBtn.prop('disabled', false).text(originalText);
      }
    });
  });

  /**
   * 模态框关闭时重置表单
   */
  $('#optionValueModal').on('hidden.bs.modal', function() {
    $('#optionValueForm')[0].reset();
    $('#form-errors').addClass('d-none');
    $('#error-list').empty();
    isEditMode = false;
    currentOptionValueId = null;
    
    // 重置图片组件
    $('.is-up-file .img-upload-item .img-info').html('<i class="bi bi-plus fs-1 text-secondary opacity-75"></i>');
    $('.is-up-file .img-upload-item .tool-wrap').addClass('d-none');
    $('.is-up-file input[name="image"]').val('');
  });

  /**
   * 文件管理器组件事件处理
   */
  $(document).on('click', '.is-up-file .img-upload-item', function () {
    const _self = $(this);

    // 调用文件管理器
    window.inno.fileManagerIframe((file) => {
      // 处理选中的文件
      let val = file.path;
      let url = file.url;
      _self.find('input').val(val);
      _self.find('.tool-wrap').removeClass('d-none');
      _self.find('.img-info').html('<img src="' + url + '" class="img-fluid" data-origin-img="' + url + '">');
      
      // 手动触发 change 事件
      _self.find('input').trigger('change');
    }, {
      multiple: false,
      type: 'image'
    });
  });

  // 删除图片
  $(document).on('click', '.is-up-file .delete-img', function (e) {
    e.stopPropagation();
    let _self = $(this).parent().parent();
    _self.find('input').val('');
    _self.find('.tool-wrap').addClass('d-none');
    _self.find('.img-info').html('<i class="bi bi-plus fs-1 text-secondary opacity-75"></i>');
  });

  // 预览图片
  $(document).on('click', '.is-up-file .show-img', function (e) {
    e.stopPropagation();
    let src = $(this).parent().siblings('.img-info').find('img').data('origin-img');
    if (src) {
      let img = '<img src="' + src + '" class="img-fluid">';
      // 创建预览模态框（如果不存在）
      if ($('#modal-show-img').length === 0) {
        $('body').append(`
          <div class="modal fade" id="modal-show-img">
            <div class="modal-dialog modal-dialog-centered">
              <div class="modal-content">
                <div class="modal-body"></div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">关闭</button>
                </div>
              </div>
            </div>
          </div>
        `);
      }
      $('#modal-show-img .modal-body').html(img);
      $('#modal-show-img').modal('show');
    }
  });
</script>
@endpush