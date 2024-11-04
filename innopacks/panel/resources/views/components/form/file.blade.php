<x-panel::form.row :title="$title">
  <div class="inno-common-file" data-type="{{ $type }}" data-file-type="{{ $fileType }}">
    <div class="img-upload-item bg-light wh-80 rounded border d-flex justify-content-center align-items-center me-2 mb-2 position-relative cursor-pointer overflow-hidden">
      <div class="position-absolute bg-white d-none img-loading"><div class="spinner-border opacity-50"></div></div>

      <div class="img-info rounded h-100 w-100 d-flex justify-content-center align-items-center">
        @if ($value)
        <i class="bi bi-folder"></i>
        @else
        <i class="bi bi-plus fs-1 text-secondary opacity-75"></i>
        @endif
      </div>
      <input type="hidden" value="{{ $value }}" name="{{ $name }}">
    </div>
    <span class="file-name d-none mt-n2 text-dark"></span>
  </div>


  <span class="text-muted" style="font-size: 12px">
      <i class="bi bi-info-circle"></i> {{ __('panel/common.up_image_text') }}
    </span>

  @if ($description)
  <div class="help-text font-size-12 lh-base">{!! $description !!}</div>
  @endif
  {{ $slot }}
</x-panel::form.row>


@pushOnce('footer')
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

<script>
  $('.inno-common-file .img-upload-item').click(function () {
      const _self = $(this);
      $('#form-upload').remove();
      $('body').prepend('<form enctype="multipart/form-data" id="form-upload" style="display: none;"><input type="file" accept="{{ $accept }}" name="file" /></form>');
      $('#form-upload input[name=\'file\']').trigger('click');
      $('#form-upload input[name=\'file\']').change(function () {
          let file = $(this).prop('files')[0];
          imgUploadAjax(file, _self);
      });
  })

  // 允许拖拽上传
  $('.inno-common-file .img-upload-item').on('dragover', function (e) {
      e.preventDefault();
      e.stopPropagation();
      $(this).addClass('border-primary');
  });

  // dragleave
  $('.inno-common-file .img-upload-item').on('dragleave', function (e) {
      e.preventDefault();
      e.stopPropagation();
      $(this).removeClass('border-primary');
  });

  $('.inno-common-file .img-upload-item').on('drop', function (e) {
      e.stopPropagation();
      let file = e.originalEvent.dataTransfer.files[0];
      imgUploadAjax(file, $(this));
      $(this).removeClass('border-primary');
  });

  function imgUploadAjax(file, _self) {
    const fileType = _self.parents('.inno-common-file').data('file-type')
      if (file.type.indexOf(fileType) === -1) {
          alert('请上传' + fileType);
          return;
      }

      let formData = new FormData();
      formData.append('file', file);
      formData.append('type', _self.parents('.inno-common-file').data('type'));
      _self.find('.img-loading').removeClass('d-none');
      axios.post('{{ front_route('upload.files') }}', formData, {}).then(function (res) {
          let val = res.data.value;
          let url = res.data.url;
          _self.find('input').val(val);
          _self.parents('.inno-common-file').find('.file-name').html(file.name).removeClass('d-none');
          _self.find('.img-info').html('<i class="bi bi-folder fs-3"></i>');

          if (typeof fileUploadSuccess === 'function') {
              fileUploadSuccess(res.data, _self);
          }
      }).catch(function (err) {
          inno.msg(err.response.data.message);
      }).finally(function () {
          _self.find('.img-loading').addClass('d-none');
      });
  }
</script>
@endPushOnce