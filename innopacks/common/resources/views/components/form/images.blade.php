<x-panel::form.row :title="$title">
  <div class="is-up-file text-start" data-img-max="{{ $max }}" data-type="{{ $type }}">
    <div class="d-flex flex-wrap">
      @foreach ($values as $v)
      <div
        class="img-upload-item wh-80 position-relative d-flex justify-content-center rounded overflow-hidden align-items-center border border-1 mb-1 me-1">
        <div class="position-absolute tool-wrap d-flex top-0 start-0 w-100 bg-primary bg-opacity-75">
          <div class="show-img w-100 text-center"><i class="bi bi-eye text-white"></i></div>
          <div class="w-100 delete-img text-center"><i class="bi bi-trash text-white"></i></div>
        </div>
        <div class="img-info d-flex justify-content-center align-items-center h-100 w-80 bg-white">
          @if ($v)
          <img src="{{ image_resize($v) }}" class="img-fluid"
            data-origin-img="{{ image_origin($v) }}">
          @else
          <i class="bi bi-plus-lg fs-3 text-secondary"></i>
          @endif
        </div>
        <input class="d-none" name="{{ $name }}[]" value="{{ $v }}">
      </div>
      @endforeach
      <div
        class="img-upload-item wh-80 img-upload-trigger d-flex overflow-hidden justify-content-center rounded align-items-center border border-1 mb-1">
        <div class="img-info d-flex justify-content-center align-items-center wh-80 bg-white cursor-pointer">
          <i class="bi bi-plus-lg fs-3 text-secondary"></i>
        </div>
      </div>
    </div>
    @hookinsert('panel.product.edit.img_upload.after')
    <span class="text-muted" style="font-size: 12px">
      <i class="bi bi-info-circle"></i> {{ __('panel/common.up_image_text') }}
      @if ($max)
      ，最多上传(<span class="imgs-count">{{ count($values) }}</span>/{{ $max }})张
      @endif
    </span>
    {{ $slot }}
  </div>
</x-panel::form.row>

@pushOnce('footer')
<div class="modal fade" id="modal-show-imgs">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-body"></div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('panel/common.close') }}</button>
      </div>
    </div>
  </div>
</div>

<script>
  $('.img-upload-trigger').click(function () {
    const _self = $(this);
    const upFile = _self.parents('.is-up-file');
    const imgMax = upFile.data('img-max');
    const currentCount = upFile.find('.img-upload-item').length - 1; // 减去触发器本身

    // 调用文件管理器
    window.inno.fileManagerIframe((files) => {
      // 确保files是数组
      const fileArray = Array.isArray(files) ? files : [files];

      // 处理选中的文件
      fileArray.forEach((file, index) => {
        if (imgMax && (currentCount + index) >= imgMax) {
          return;
        }

        let val = file.path;
        let url = file.url;

        let item = '<div class="img-upload-item wh-80 position-relative d-flex justify-content-center rounded overflow-hidden align-items-center border border-1 mb-1 me-1">';
        item += '<div class="position-absolute tool-wrap d-flex top-0 start-0 w-100 bg-primary bg-opacity-75"><div class="show-img w-100 text-center"><i class="bi bi-eye text-white"></i></div><div class="w-100 delete-img text-center"><i class="bi bi-trash text-white"></i></div></div>';
        item += '<div class="img-info d-flex justify-content-center align-items-center h-100 w-80 bg-white">';
        item += '<img src="' + url + '" class="img-fluid" data-origin-img="' + url + '">';
        item += '</div>';
        item += '<input class="d-none" name="{{ $name }}[]" value="' + val + '">';
        item += '</div>';

        _self.before(item);
      });

      // 更新计数
      upFile.find('.imgs-count').text(upFile.find('.img-upload-item').length - 1);
    }, {
      multiple: true,
      type: 'image'
    });
  });

  // 删除图片
  $('.is-up-file').on('click', '.delete-img', function () {
    let count = $(this).parents('.is-up-file').find('.imgs-count');
    count.text(count.text() * 1 - 1);
    $(this).parent().parent().remove();
  });

  // 预览图片
  $('.is-up-file').on('click', '.show-img', function () {
    let src = $(this).parent().next().find('img').data('origin-img');
    let img = '<img src="' + src + '" class="img-fluid">';
    $('#modal-show-imgs .modal-body').html(img);
    $('#modal-show-imgs').modal('show');
  });
</script>
@endPushOnce
