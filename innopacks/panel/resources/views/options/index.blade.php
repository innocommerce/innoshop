@extends('panel::layouts.app')
@section('body-class', 'page-product-option-group')
@section('title', '选项管理')

@section('content')
  <div class="card h-min-600" id="app">
    <div class="card-body">
      <!-- 导航链接 -->
      <ul class="nav nav-tabs mb-4">
        <li class="nav-item">
          <a class="nav-link active" href="{{ panel_route('options.index') }}">
            <i class="bi bi-collection"></i> 选项组管理
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="{{ panel_route('option_values.index') }}">
            <i class="bi bi-list-ul"></i> 选项值管理
          </a>
        </li>
      </ul>

      <!-- 选项组管理内容 -->
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">选项组管理</h5>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#optionGroupModal" onclick="openCreateModal()">
          <i class="bi bi-plus-square"></i> {{ __('panel/common.create') }}
        </button>
      </div>

      <x-panel-data-criteria :criteria="$criteria ?? []" :action="panel_route('options.index')" />

      <!-- 选项组列表 -->
      @if ($option_groups->count())
        <div class="table-responsive">
          <table class="table align-middle">
            <thead>
            <tr>
              <th>{{ __('panel/common.id') }}</th>
              <th>{{ __('panel/common.name') }}</th>
              <th>描述</th>
              <th>类型</th>
              <th>是否必填</th>
              <th>排序</th>
              <th>{{ __('panel/common.active') }}</th>
              <th>{{ __('panel/common.created_at') }}</th>
              <th>{{ __('panel/common.actions') }}</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($option_groups as $optionGroup)
              <tr>
                <td>{{ $optionGroup->id }}</td>
                <td>{{ $optionGroup->currentName }}</td>
                <td>
                  <div class="text-muted small" style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" 
                       title="{{ $optionGroup->getLocalizedDescription() }}">
                    {{ $optionGroup->getLocalizedDescription() ?: '暂无描述' }}
                  </div>
                </td>
                <td>
                  @switch($optionGroup->type)
                    @case('select')
                      <span class="badge bg-primary">下拉选择</span>
                      @break
                    @case('radio')
                      <span class="badge bg-info">单选按钮</span>
                      @break
                    @case('checkbox')
                      <span class="badge bg-success">多选框</span>
                      @break
                    @case('text')
                      <span class="badge bg-warning">文本输入</span>
                      @break
                    @case('textarea')
                      <span class="badge bg-secondary">文本域</span>
                      @break
                    @default
                      <span class="badge bg-light text-dark">{{ $optionGroup->type }}</span>
                  @endswitch
                </td>
                <td>
                  @if($optionGroup->required)
                    <span class="badge bg-danger">必填</span>
                  @else
                    <span class="badge bg-secondary">可选</span>
                  @endif
                </td>
                <td>{{ $optionGroup->position }}</td>
                <td>
                  @include('panel::shared.list_switch', [
                    'value' => $optionGroup->active, 
                    'url' => panel_route('options.active', $optionGroup->id)
                  ])
                </td>
                <td>{{ $optionGroup->created_at }}</td>
                <td>
                  <div class="d-flex gap-2">
                    <div>
                      <button type="button" class="btn btn-sm btn-outline-primary" 
                              data-bs-toggle="modal" data-bs-target="#optionGroupModal" 
                              onclick="openEditModal({{ $optionGroup->id }})">
                        {{ __('panel/common.edit') }}
                      </button>
                    </div>
                    <div>
                      <form action="{{ panel_route('options.destroy', [$optionGroup->id]) }}"
                            method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger" 
                                onclick="return confirm('确定要删除这个选项组吗？')">
                          {{ __('panel/common.delete') }}
                        </button>
                      </form>
                    </div>
                  </div>
                </td>
              </tr>
            @endforeach
            </tbody>
          </table>
        </div>

        {{ $option_groups->withQueryString()->links('panel::vendor/pagination/bootstrap-4') }}
      @else
        <x-common-no-data />
      @endif
    </div>
  </div>

  <!-- 选项组编辑模态框 -->
  <div class="modal fade" id="optionGroupModal" tabindex="-1" aria-labelledby="optionGroupModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="optionGroupModalLabel">创建选项组</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form id="optionGroupForm" method="POST">
          @csrf
          <input type="hidden" name="_method" value="POST" id="form-method">
          <div class="modal-body">
            <div class="row">
              <!-- 基本信息 -->
              <div class="col-12">
                <h6 class="mb-3">基本信息</h6>
              </div>
              
              <div class="col-12 col-md-6">
                <div class="mb-3">
                  <label for="type" class="form-label">选项类型 <span class="text-danger">*</span></label>
                  <select class="form-select" id="type" name="type" required>
                    <option value="select" selected>下拉选择</option>
                    <option value="radio">单选按钮</option>
                    <option value="checkbox">复选框</option>
                  </select>
                </div>
              </div>

              <div class="col-12 col-md-6">
                <div class="mb-3">
                  <label for="position" class="form-label">排序</label>
                  <input type="number" class="form-control" id="position" name="position" value="0">
                </div>
              </div>

              <div class="col-12 col-md-6">
                <div class="mb-3">
                  <label class="form-label">是否必填</label>
                  <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" id="required" name="required" value="1">
                  </div>
                </div>
              </div>

              <div class="col-12 col-md-6">
                <div class="mb-3">
                  <label class="form-label">是否启用</label>
                  <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" id="active" name="active" value="1" checked>
                  </div>
                </div>
              </div>

              <!-- 多语言信息 -->
              <div class="col-12">
                <h6 class="mb-3 mt-3">多语言信息</h6>
                
                <!-- 多语言Tab导航 -->
                <ul class="nav nav-tabs mb-3" id="languageTab" role="tablist">
                  @foreach (locales() as $locale)
                    <li class="nav-item" role="presentation">
                      <button class="nav-link d-flex align-items-center {{ $loop->first ? 'active' : '' }}" 
                              id="lang-{{ $locale['code'] }}-tab" 
                              data-bs-toggle="tab" 
                              data-bs-target="#lang-{{ $locale['code'] }}-pane" 
                              type="button" role="tab" 
                              aria-controls="lang-{{ $locale['code'] }}-pane" 
                              aria-selected="{{ $loop->first ? 'true' : 'false' }}">
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
                
                <!-- 多语言Tab内容 -->
                <div class="tab-content" id="languageTabContent">
                  @foreach (locales() as $locale)
                    <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" 
                         id="lang-{{ $locale['code'] }}-pane" 
                         role="tabpanel" 
                         aria-labelledby="lang-{{ $locale['code'] }}-tab">
                      <div class="row">
                        <div class="col-12">
                          <div class="mb-3">
                            <label for="name_{{ $locale['code'] }}" class="form-label">
                              选项组名称 
                              <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" 
                                   id="name_{{ $locale['code'] }}" 
                                   name="translations[{{ $locale['code'] }}][name]"
                                   required>
                          </div>
                        </div>
                        <div class="col-12">
                          <div class="mb-3">
                            <label for="description_{{ $locale['code'] }}" class="form-label">选项组描述</label>
                            <textarea class="form-control" 
                                      id="description_{{ $locale['code'] }}" 
                                      name="translations[{{ $locale['code'] }}][description]"
                                      rows="3" 
                                      placeholder="请输入选项组的描述信息，用于说明该选项的作用"></textarea>
                          </div>
                        </div>
                      </div>
                    </div>
                  @endforeach
                </div>
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
// Output option group data as JavaScript variable
const optionGroupsData = @json($option_groups_data);
// Extract actual data array (handle pagination structure)
const optionGroups = optionGroupsData.data || optionGroupsData;

$(document).ready(function() {
    let isEditMode = false;
    let currentOptionId = null;

    /**
     * Open create option group modal
     */
    window.openCreateModal = function() {
        isEditMode = false;
        currentOptionId = null;
        
        // Reset form
        $('#optionGroupForm')[0].reset();
        $('#optionGroupModalLabel').text('创建选项组');
        $('#optionGroupForm').attr('action', '{{ panel_route("options.store") }}');
        $('#form-method').val('POST'); // Set to POST method
        
        // Clear all form fields
        $('#product_id').val('');
        $('#type').val('select');
        $('#position').val('0');
        $('#required').prop('checked', false);
        $('#active').prop('checked', true);
        
        // Clear multilingual fields
        $('[id^="name_"]').val('');
        $('[id^="description_"]').val('');
        
        // Show modal
        $('#optionGroupModal').modal('show');
    };

    /**
     * Open edit option group modal
     */
    window.openEditModal = function(optionId) {
        isEditMode = true;
        currentOptionId = optionId;
        
        // Get option group info from local data (using corrected data array)
        const optionGroup = optionGroups.find(group => group.id == optionId);
        
        // Set form action - use correct route parameter format
        $('#optionGroupForm').attr('action', `/panel/options/${optionId}`);
        $('#form-method').val('PUT'); // Set to PUT method for update
        
        // Fill basic info
        $('#product_id').val(optionGroup.product_id || '');
        $('#type').val(optionGroup.type || 'select');
        $('#position').val(optionGroup.position || 0);
        $('#required').prop('checked', optionGroup.required == 1);
        $('#active').prop('checked', optionGroup.active == 1);
        
        // Fill multilingual info - name and description are JSON fields in database
        if (optionGroup.name) {
            try {
                const names = typeof optionGroup.name === 'string' ? JSON.parse(optionGroup.name) : optionGroup.name;
                Object.keys(names).forEach(locale => {
                    $(`#name_${locale}`).val(names[locale] || '');
                });
            } catch (e) {
                console.error('Error parsing name JSON:', e);
            }
        }
        
        if (optionGroup.description) {
            try {
                const descriptions = typeof optionGroup.description === 'string' ? JSON.parse(optionGroup.description) : optionGroup.description;
                Object.keys(descriptions).forEach(locale => {
                    $(`#description_${locale}`).val(descriptions[locale] || '');
                });
            } catch (e) {
                console.error('Error parsing description JSON:', e);
            }
        }
        
        $('#optionGroupModalLabel').text('编辑选项组');
        // Show modal
        $('#optionGroupModal').modal('show');
    };

    // Form submission handling
    $('#optionGroupForm').on('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        // If in edit mode, add PUT method
        if (isEditMode) {
            formData.append('_method', 'PUT');
        }
        
        // Add AJAX identifier
        formData.append('_ajax', '1');
        
        // Show loading state
        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.text();
        submitBtn.prop('disabled', true).text('处理中...');
        
        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                // Close loading state
                submitBtn.prop('disabled', false).text(originalText);
                
                // Close modal
                $('#optionGroupModal').modal('hide');
                
                // Use layer to show success message
                layer.msg(response.message || '操作成功', {
                    icon: 1,
                    time: 2000
                }, function() {
                    // Refresh page
                    window.location.reload();
                });
            },
            error: function(xhr) {
                // Close loading state (if operation fails but no error is thrown)
                submitBtn.prop('disabled', false).text(originalText);
                
                let errorMessage = '操作失败';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                    const errors = xhr.responseJSON.errors;
                    errorMessage = Object.values(errors).flat().join('<br>');
                }
                
                // Close loading state
                layer.msg(errorMessage, {
                    icon: 2,
                    time: 3000
                });
            }
        });
    });
});
</script>
@endpush