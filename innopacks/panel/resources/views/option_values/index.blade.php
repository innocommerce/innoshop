@extends('panel::layouts.app')
@section('body-class', 'page-product-option-value')
@section('title', panel_trans('options.option_value_management'))

@section('content')
  <div class="card h-min-600" id="app">
    <div class="card-body">
      <!-- Navigation links -->
      <ul class="nav nav-tabs mb-4">
        <li class="nav-item">
          <a class="nav-link" href="{{ panel_route('options.index') }}">
            <i class="bi bi-collection"></i> {{ panel_trans('options.option_group_management') }}
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link active" href="{{ panel_route('option_values.index') }}">
            <i class="bi bi-list-ul"></i> {{ panel_trans('options.option_value_management') }}
          </a>
        </li>
      </ul>

      <!-- Option value management content -->
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">{{ panel_trans('options.option_value_management') }}</h5>
        <button type="button" class="btn btn-primary" onclick="openCreateModal()">
          <i class="bi bi-plus-square"></i> {{ __('panel/common.create') }}
        </button>
      </div>

      <x-panel-data-criteria :criteria="$criteria ?? []" :action="panel_route('option_values.index')" />

      <!-- Option value list -->
      @if ($optionValues->count())
        <div class="table-responsive">
          <table class="table align-middle">
            <thead>
            <tr>
              <th>{{ __('panel/common.id') }}</th>
              <th>{{ __('panel/common.name') }}</th>
              <th>{{ panel_trans('options.option_group') }}</th>
              <th>{{ panel_trans('options.image') }}</th>
              <th>{{ panel_trans('options.sort') }}</th>
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
                    <img src="{{ $optionValue->getImageUrl() }}" alt="{{ panel_trans('options.option_value_image') }}" class="img-thumbnail" style="width: 40px; height: 40px;">
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
                  <button type="button" class="btn btn-outline-danger btn-sm" onclick="if(confirm('{{ panel_trans('options.confirm_delete_option_value') }}')) { document.getElementById('delete-form-{{ $optionValue->id }}').submit(); }">
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

  <!-- Option value edit modal -->
  <div class="modal fade" id="optionValueModal" tabindex="-1" aria-labelledby="optionValueModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="optionValueModalLabel">{{ panel_trans('options.add_option_value') }}</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form id="optionValueForm" method="POST">
          @csrf
          <input type="hidden" name="_method" value="POST" id="form-method">
          
          <!-- Error message display area -->
          <div id="form-errors" class="alert alert-danger d-none mx-3 mt-3" role="alert">
            <ul class="mb-0" id="error-list"></ul>
          </div>
          
          <div class="modal-body">
            <div class="row">
              <!-- Basic information -->
              <div class="col-12">
                <h6 class="mb-3">{{ panel_trans('options.basic_info') }}</h6>
              </div>
              
              <div class="col-12 col-md-6">
                <div class="mb-3">
                  <label for="option_id" class="form-label">{{ panel_trans('options.option_group') }} <span class="text-danger">*</span></label>
                  <select name="option_id" id="option_id" class="form-select" required>
                    @foreach($allOptionGroups as $group)
                      <option value="{{ $group->id }}">{{ $group->currentName }} ({{ $group->type }})</option>
                    @endforeach
                  </select>
                </div>
              </div>
              
              <div class="col-12 col-md-6">
                <div class="mb-3">
                  <label for="image" class="form-label">{{ panel_trans('options.image') }}</label>
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
                  <div class="form-text">{{ panel_trans('options.optional_option_value_image') }}</div>
                </div>
              </div>
              
              <div class="col-12 col-md-6">
                <div class="mb-3">
                  <label for="position" class="form-label">{{ panel_trans('options.sort') }}</label>
                  <input type="number" name="position" id="position" class="form-control" value="0">
                </div>
              </div>
              
              <div class="col-12 col-md-6">
                <div class="mb-3">
                  <label class="form-label">{{ panel_trans('options.status') }}</label>
                  <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="active" id="active" value="1" checked>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Multilingual information -->
            <div class="mt-4">
              <h6 class="mb-3">{{ panel_trans('options.multilingual_info') }}</h6>
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
                        {{ panel_trans('options.option_name') }} ({{ $locale['name'] }})
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
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ panel_trans('options.cancel') }}</button>
            <button type="submit" class="btn btn-primary">{{ panel_trans('options.save') }}</button>
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
   * Open create option value modal
   */
  function openCreateModal() {
    isEditMode = false;
    currentOptionValueId = null;
    
    // Reset form
    $('#optionValueForm')[0].reset();
    
    // Set modal title
    $('#optionValueModalLabel').text('{{ panel_trans('options.create_option_value') }}');
    
    // Set form action and method
    $('#optionValueForm').attr('action', '{{ panel_route("option_values.store") }}');
    $('#form-method').val('POST'); // Set to POST method
    
    // Clear form fields
    $('#option_group_id').val('');
    $('#price').val('');
    $('#position').val('0');
    $('#active').prop('checked', true);
    
    // Clear multilingual fields
    @foreach (locales() as $locale)
      $('#name_{{ $locale['code'] }}').val('');
    @endforeach
    
    // Show modal
    $('#optionValueModal').modal('show');
  }

  /**
   * Open edit option value modal
   * @param {number} optionValueId - Option value ID
   */
  function openEditModal(optionValueId) {
    isEditMode = true;
    currentOptionValueId = optionValueId;
    
    // Set modal title
    $('#optionValueModalLabel').text('{{ panel_trans('options.edit_option_value') }}');
    
    // Set form action and method
    $('#optionValueForm').attr('action', '{{ panel_route("option_values.update", ":id") }}'.replace(':id', optionValueId));
    $('#form-method').val('PUT'); // Set to PUT method
    
    // Show loading state
    $('#optionValueModal').modal('show');
    
    // Get option value data
    $.get('{{ panel_route("option_values.show", ":id") }}'.replace(':id', optionValueId))
      .done(function(data) {
        // Debug: print retrieved data
        console.log('Retrieved option value data:', data);
        
        // Fill basic information
        $('#option_id').val(data.option_id);
        $('#position').val(data.position);
        $('#active').prop('checked', data.active == 1);
        
        // Fill image field
        if (data.image) {
          const imageContainer = $('.is-up-file .img-upload-item');
          const imageUrl = data.image.indexOf('http') === 0 ? data.image : '{{ asset("") }}' + data.image;
          imageContainer.find('input[name="image"]').val(data.image);
          imageContainer.find('.tool-wrap').removeClass('d-none');
          imageContainer.find('.img-info').html('<img src="' + imageUrl + '" class="img-fluid" data-origin-img="' + imageUrl + '">');
        } else {
          // Reset image component to default state
          const imageContainer = $('.is-up-file .img-upload-item');
          imageContainer.find('input[name="image"]').val('');
          imageContainer.find('.tool-wrap').addClass('d-none');
          imageContainer.find('.img-info').html('<i class="bi bi-plus fs-1 text-secondary opacity-75"></i>');
        }
        
        // Fill multilingual data
        if (data.name) {
          console.log('Multilingual data:', data.name);
          @foreach (locales() as $locale)
            $('#name_{{ $locale['code'] }}').val(data.name['{{ $locale['code'] }}'] || '');
          @endforeach
        }
      })
      .fail(function() {
        layer.msg('{{ panel_trans('options.get_option_value_data_failed') }}', {icon: 2});
        $('#optionValueModal').modal('hide');
      });
  }

  /**
   * Handle form submission
   */
  $('#optionValueForm').on('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = $(this).find('button[type="submit"]');
    const originalText = submitBtn.text();
    
    // Show loading state
    submitBtn.prop('disabled', true).text('{{ panel_trans('options.saving') }}');
    
    $.ajax({
      url: $(this).attr('action'),
      method: 'POST',
      data: formData,
      processData: false,
      contentType: false,
      success: function(response) {
        layer.msg(isEditMode ? '{{ panel_trans('options.option_value_update_success') }}' : '{{ panel_trans('options.option_value_create_success') }}', {icon: 1});
        $('#optionValueModal').modal('hide');
        
        // Refresh page
        setTimeout(function() {
          window.location.reload();
        }, 1000);
      },
      error: function(xhr) {
        // Clear previous error messages
        $('#form-errors').addClass('d-none');
        $('#error-list').empty();
        
        let errorMessage = '{{ panel_trans('options.operation_failed') }}';
        let hasErrors = false;
        
        if (xhr.responseJSON && xhr.responseJSON.errors) {
          const errors = xhr.responseJSON.errors;
          hasErrors = true;
          
          // Show all validation errors
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
        
        // If no specific error messages, use layer.msg to show generic error
        if (!hasErrors) {
          layer.msg(errorMessage, {icon: 2});
        }
      },
      complete: function() {
        // Restore button state
        submitBtn.prop('disabled', false).text(originalText);
      }
    });
  });

  /**
   * Reset form when modal is closed
   */
  $('#optionValueModal').on('hidden.bs.modal', function() {
    $('#optionValueForm')[0].reset();
    $('#form-errors').addClass('d-none');
    $('#error-list').empty();
    isEditMode = false;
    currentOptionValueId = null;
    
    // Reset image component
    $('.is-up-file .img-upload-item .img-info').html('<i class="bi bi-plus fs-1 text-secondary opacity-75"></i>');
    $('.is-up-file .img-upload-item .tool-wrap').addClass('d-none');
    $('.is-up-file input[name="image"]').val('');
  });

  /**
   * File manager component event handling
   */
  $(document).on('click', '.is-up-file .img-upload-item', function () {
    const _self = $(this);

    // Call file manager
    window.inno.fileManagerIframe((file) => {
      // Handle selected file
      let val = file.path;
      let url = file.url;
      _self.find('input').val(val);
      _self.find('.tool-wrap').removeClass('d-none');
      _self.find('.img-info').html('<img src="' + url + '" class="img-fluid" data-origin-img="' + url + '">');
      
      // Manually trigger change event
      _self.find('input').trigger('change');
    }, {
      multiple: false,
      type: 'image'
    });
  });

  // Delete image
  $(document).on('click', '.is-up-file .delete-img', function (e) {
    e.stopPropagation();
    let _self = $(this).parent().parent();
    _self.find('input').val('');
    _self.find('.tool-wrap').addClass('d-none');
    _self.find('.img-info').html('<i class="bi bi-plus fs-1 text-secondary opacity-75"></i>');
  });

  // Preview image
  $(document).on('click', '.is-up-file .show-img', function (e) {
    e.stopPropagation();
    let src = $(this).parent().siblings('.img-info').find('img').data('origin-img');
    if (src) {
      let img = '<img src="' + src + '" class="img-fluid">';
      // Create preview modal (if not exists)
      if ($('#modal-show-img').length === 0) {
        $('body').append(`
          <div class="modal fade" id="modal-show-img">
            <div class="modal-dialog modal-dialog-centered">
              <div class="modal-content">
                <div class="modal-body"></div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ panel_trans('options.close') }}</button>
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