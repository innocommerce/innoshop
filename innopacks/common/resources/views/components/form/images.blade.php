<x-panel::form.row :title="$title">
  <div class="multi-images-upload-wrapper">
    <div class="is-up-file text-start" data-img-max="{{ $max }}" data-type="{{ $type }}">
    <!-- Add sortable container -->
    <div class="img-sortable-container d-flex flex-wrap" data-sortable="true">
      @foreach ($values as $v)
      <div
        class="img-upload-item wh-80 position-relative d-flex justify-content-center rounded overflow-hidden align-items-center border border-1 mb-1 me-1"
        data-sortable-item>
        <div class="position-absolute tool-wrap d-flex top-0 start-0 w-100 bg-primary bg-opacity-75">
          <div class="show-img w-100 text-center"><i class="bi bi-eye text-white"></i></div>
          <div class="w-100 delete-img text-center"><i class="bi bi-trash text-white"></i></div>
        </div>
        <!-- Drag handle positioned at the bottom of the image -->
        <div class="position-absolute drag-handle bottom-0 start-0 w-100 bg-primary bg-opacity-25 text-center">
          <i class="bi bi-arrows-move text-black"></i>
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
      <i class="bi bi-info-circle"></i> {{ __('common/upload.upload_hint') }}
      @if ($max)
      <br>
        {{ __('common/upload.max_upload_count') }}(<span class="imgs-count">{{ count($values) }}</span>/{{ $max }}){{ __('common/upload.image_unit') }}
      @endif
    </span>
    {{ $slot }}
  </div>
  </div>
</x-panel::form.row>

@pushOnce('footer')
<!-- Add SortableJS library -->

<div class="modal fade" id="modal-show-imgs">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-body"></div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('common/upload.close') }}</button>
      </div>
    </div>
  </div>
</div>

<script>
  const ImageUpload = {
    init() {
      this.initSortable();
      this.bindUploadTrigger();
      this.bindDelete();
      this.bindPreview();
    },
    initSortable() {
      $('.multi-images-upload-wrapper .img-sortable-container').each((_, el) => {
        new Sortable(el, {
          animation: 150,
          handle: '.drag-handle',
          filter: '.img-upload-trigger',
          onEnd: () => {}
        });
      });
    },
    bindUploadTrigger() {
      $('.multi-images-upload-wrapper .img-upload-trigger').off('click').on('click', function () {
        const _self = $(this);
        const upFile = _self.parents('.is-up-file');
        const imgMax = upFile.data('img-max');
        const currentCount = upFile.find('.img-upload-item').length - 1;

        window.inno.fileManagerIframe((files) => {
          const fileArray = Array.isArray(files) ? files : [files];
          fileArray.forEach((file, index) => {
            if (imgMax && (currentCount + index) >= imgMax) return;
            let val = file.path;
            let url = file.url;
            let item = '' +
              '<div class="img-upload-item wh-80 position-relative d-flex justify-content-center rounded overflow-hidden align-items-center border border-1 mb-1 me-1" data-sortable-item>' +
                '<div class="position-absolute tool-wrap d-flex top-0 start-0 w-100 bg-primary bg-opacity-75">' +
                  '<div class="show-img w-100 text-center"><i class="bi bi-eye text-white"></i></div>' +
                  '<div class="w-100 delete-img text-center"><i class="bi bi-trash text-white"></i></div>' +
                '</div>' +
                '<div class="position-absolute drag-handle bottom-0 start-0 w-100 bg-primary bg-opacity-75 text-center">' +
                  '<i class="bi bi-arrows-move text-white"></i>' +
                '</div>' +
                '<div class="img-info d-flex justify-content-center align-items-center h-100 w-80 bg-white">' +
                  '<img src="' + url + '" class="img-fluid" data-origin-img="' + url + '">' +
                '</div>' +
                '<input class="d-none" name="{{ $name }}[]" value="' + val + '">' +
              '</div>';
            _self.before(item);
          });
          upFile.find('.imgs-count').text(upFile.find('.img-upload-item').length - 1);
          ImageUpload.initSortable();
        }, {
          multiple: true,
          type: 'image'
        });
      });
    },
    bindDelete() {
      $('.multi-images-upload-wrapper .is-up-file').off('click.delete-img').on('click.delete-img', '.delete-img', function () {
        let count = $(this).parents('.is-up-file').find('.imgs-count');
        count.text(count.text() * 1 - 1);
        $(this).parent().parent().remove();
      });
    },
    bindPreview() {
      $('.multi-images-upload-wrapper .is-up-file').off('click.show-img').on('click.show-img', '.show-img', function () {
        let src = $(this).parent().next().find('img').data('origin-img');
        let img = '<img src="' + src + '" class="img-fluid">';
        $('#modal-show-imgs .modal-body').html(img);
        $('#modal-show-imgs').modal('show');
      });
    }
  };

  $(document).ready(function() {
    ImageUpload.init();
  });
</script>
@endPushOnce
