<div class="form-check form-switch list-switch {{ $class ?? '' }}" data-url="{{ $url }}" data-reload="{{ $reload ?? false }}" @if(isset($data_code)) data-code="{{ $data_code }}" @endif>
  <input class="form-check-input" type="checkbox" role="switch" @if($value) checked @endif >
</div>

@pushOnce('footer')
  <script>
    $(function () {
      $(document).on('change', '.list-switch:not(.theme-switch) > input[role="switch"]', function () {
        var $input = $(this);
        var status = $input.prop('checked') ? 1 : 0;
        var url = $input.parent().data('url');
        var reload = $input.parent().data('reload');

        layer.load(2, {shade: [0.3,'#fff'] });
        axios.put(url, {status}).then(function (res) {
          inno.msg(res.message);
          if (reload) {
            location.reload();
          }
        }).catch(function (err) {
          $input.prop('checked', !status);
          var msg = (err.response && err.response.data && err.response.data.message) || (err.response && err.response.data && err.response.data.error) || '';
          if (msg) inno.msg(msg);
        }).finally(function () {
          layer.closeAll('loading');
        });
      });
    });
  </script>
@endPushOnce