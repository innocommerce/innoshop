<x-panel::form.row :title="$title" :required="$required">
  <div class="autocomplete-group-wrapper">
    <div class="autocomplete-input-box">
      <input type="text" class="form-control input-autocomplete input-autocomplete"
      placeholder="{{ $placeholder }}" @if ($required) required @endif />
      <div class="invalid-feedback text-start">{{ $placeholder }}</div>
    </div>
    <div class="autocomplete-list p-2 autocomplete-list-{{ $id }}">
      <ul class="list-group list-group-flush"></ul>
    </div>
  </div>
</x-panel::form.row>

@pushOnce('footer')
<script>
  $(function () {
    $('.input-autocomplete').autocomplete({
      'source': function(request, response) {
        axios.get('{{ $api }}/autocomplete?keyword=' + encodeURIComponent(request)).then(function(res) {
          response($.map(res.data, function(item) {
            return {
              label: item['name'],
              value: item['id']
            }
          }));
        });
      },
      'select': function(item) {
        $(this).closest('.autocomplete-group-wrapper').find('.autocomplete-list ul').append(`
          <li class="list-group list-group-item">
            <span class="autocomplete-name">${item['label']}</span>
            <button type="button" class="btn-close"></button>
            <input name="{{ $name }}" type="hidden" value="${item['value']}" />
          </li>
        `);
      }
    });

    $(document).on('click', '.autocomplete-list .btn-close', function() {
      $(this).closest('li').remove();
    });
  });
</script>

@endPushOnce

@push('footer')
<script>
  var values = @json($value);
  axios.get('{{ $api }}?tag_ids=' + values.join(',')).then(function(res) {
    var data = res.data;
    var list = $('.autocomplete-list-{{ $id }} ul');
    for (var i = 0; i < values.length; i++) {
      var value = values[i];
      for (var j = 0; j < data.length; j++) {
        var item = data[j];
        if (item['id'] == value) {
          list.append(`
            <li class="list-group list-group-item">
              <span class="autocomplete-name">${item['name']}</span>
              <button type="button" class="btn-close"></button>
              <input name="{{ $name }}" type="hidden" value="${item['id']}" />
            </li>
          `);
          break;
        }
      }
    }
  });
</script>
@endpush