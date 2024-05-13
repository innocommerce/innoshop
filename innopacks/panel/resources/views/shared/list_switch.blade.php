<div class="form-check form-switch list-switch" data-url="{{ $url }}" data-reload="{{ $reload ?? false }}">
  <input class="form-check-input" type="checkbox" role="switch" @if($value) checked @endif >
</div>

@pushOnce('footer')
  <script>
    $(function () {
      $('.list-switch > input[role="switch"]').on('change', function () {
        let status = $(this).prop('checked') ? 1 : 0;
        let url = $(this).parent().data('url');
        let reload = $(this).parent().data('reload');

        layer.load(2, {shade: [0.3,'#fff'] })
        axios.put(url, {status}).then((res) => {
          inno.msg(res.message)
          if (reload) {
            location.reload();
          }
        }).catch((err) => {
          $(this).prop('checked', !status);
          inno.msg(err.response.data.message)
        }).finally(() => {
          layer.closeAll('loading');
        });
      });
    });
  </script>
@endPushOnce