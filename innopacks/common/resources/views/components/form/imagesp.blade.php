<x-panel::form.row :title="$title">
    <div class="multi-images-pure-upload-wrapper">
        <div class="is-up-file-pure text-start" data-img-max="{{ $max }}" data-type="{{ $type }}">
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
        <span class="text-muted" style="font-size: 12px">
      <i class="bi bi-info-circle"></i> {{ __('panel/common.up_image_text') }}
            @if ($max)
                ，最多上传(<span class="imgs-count">{{ count($values) }}</span>/{{ $max }})张
            @endif
    </span>
        {{ $slot }}
    </div>
    </div>
</x-panel::form.row>

@pushOnce('footer')
    <div class="modal fade" id="modal-show-imgs">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-body text-center p-4" style="min-height: 200px; display: flex; align-items: center; justify-content: center;"></div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('panel/common.close') }}</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        $('.multi-images-pure-upload-wrapper .img-upload-trigger').click(function () {
            const _self = $(this);
            $('#form-upload').remove();
            $('body').prepend('<form enctype="multipart/form-data" id="form-upload" style="display: none;"><input type="file" accept="image/*" multiple name="file" /></form>');
            $('#form-upload input[name=\'file\']').trigger('click');
            $('#form-upload input[name=\'file\']').change(function () {
                let file = $(this).prop('files');
                imgsUploadAjax(file, _self);
            });
        })

        // 允许拖拽上传
        $('.multi-images-pure-upload-wrapper .is-up-file-pure').on('dragover', function (e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).addClass('border-primary');
        });

        // dragleave
        $('.multi-images-pure-upload-wrapper .is-up-file-pure').on('dragleave', function (e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).removeClass('border-primary');
        });

        $('.multi-images-pure-upload-wrapper .is-up-file-pure').on('drop', function (e) {
            e.preventDefault();
            e.stopPropagation();
            let file = e.originalEvent.dataTransfer.files;
            imgsUploadAjax(file, $(this));
            $(this).removeClass('border-primary');
        });

        $('.multi-images-pure-upload-wrapper .is-up-file-pure').on('click', '.delete-img', function () {
            let count = $(this).parents('.is-up-file-pure').find('.imgs-count');
            count.text(count.text() * 1 - 1);
            $(this).parent().parent().remove();
        });

        $('.multi-images-pure-upload-wrapper .is-up-file-pure').on('click', '.show-img', function (e) {
            e.stopPropagation();
            const imgElement = $(this).closest('.img-upload-item').find('.img-info img');
            let src = imgElement.data('origin-img') || imgElement.attr('src');
            if (!src) {
              console.error('Image source not found');
              return;
            }
            let img = '<img src="' + src + '" class="img-fluid" style="max-width: 100%; max-height: 70vh; height: auto; border-radius: 4px;">';
            $('#modal-show-imgs .modal-body').html(img);
            $('#modal-show-imgs').modal('show');
        });

        function imgsUploadAjax(file, _self) {
            let imgsCount = _self.parents('.is-up-file-pure').find('.imgs-count').text() * 1;
            let imgMax = _self.parents('.is-up-file-pure').data('img-max');

            for (let i = 0; i < file.length; i++) {
                if (file[i].type.indexOf('image') === -1) {
                    continue;
                }

                if (imgMax && imgsCount >= imgMax) {
                    return;
                }

                let formData = new FormData();
                formData.append('image', file[i]);
                formData.append('type', _self.parents('.is-up-file-pure').data('type'));

                _self.find('.img-loading').removeClass('d-none');
                axios.post('{{ front_root_route('upload.images') }}', formData)
                    .then(function (response) {
                        if (response.success) {
                            const val = response.data.value;
                            const url = response.data.url; // Thumbnail for display
                            const originUrl = response.data.origin_url || response.data.url; // Original image for preview
                            let item = '<div class="img-upload-item wh-80 position-relative d-flex justify-content-center rounded overflow-hidden align-items-center border border-1 mb-1 me-1">';
                            item += '<div class="position-absolute tool-wrap d-flex top-0 start-0 w-100 bg-primary bg-opacity-75"><div class="show-img w-100 text-center"><i class="bi bi-eye text-white"></i></div><div class="w-100 delete-img text-center"><i class="bi bi-trash text-white"></i></div></div>';
                            item += '<div class="img-info d-flex justify-content-center align-items-center h-100 w-80 bg-white">';
                            item += '<img src="' + url + '" class="img-fluid" data-origin-img="' + originUrl + '">';
                            item += '</div>';
                            item += '<input class="d-none" name="{{ $name }}[]" value="' + val + '">';
                            item += '</div>';
                            _self.before(item);
                            _self.parents('.is-up-file-pure').find('.imgs-count').text(_self.parents('.is-up-file-pure').find('.img-upload-item').length - 1);
                        } else {
                            layer.msg(response.message || '上传失败', {icon: 2});
                        }
                    })
                    .catch(function (error) {
                        layer.msg(error.response?.message || '上传失败', {icon: 2});
                    })
                    .finally(function () {
                        _self.find('.img-loading').addClass('d-none');
                    });

                imgsCount++
            }
        }
    </script>
@endPushOnce
