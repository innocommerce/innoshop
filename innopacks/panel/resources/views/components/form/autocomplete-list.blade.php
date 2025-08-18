<x-panel::form.row :title="$title" :required="$required">
  <div class="autocomplete-group-wrapper autocomplete-wrapper-{{ $id }}">
    <div class="autocomplete-input-box">
      <input type="text" class="form-control input-autocomplete input-autocomplete-{{ $id }}"
      placeholder="{{ $placeholder }}" @if ($required) required @endif />
      <div class="invalid-feedback text-start">{{ $placeholder }}</div>
    </div>
    <div class="autocomplete-list p-2 autocomplete-list-{{ $id }}">
      <ul class="list-group list-group-flush">
        {{-- 如果传入了已选项的完整数据，直接循环输出，避免API调用 --}}
        @if(!empty($selectedItems))
          @foreach($selectedItems as $item)
            <li class="list-group list-group-item">
              <span class="autocomplete-name">{{ $item['name'] ?? $item['title'] ?? '' }}</span>
              <button type="button" class="btn-close"></button>
              <input name="{{ $name }}" type="hidden" value="{{ $item['id'] }}" />
            </li>
          @endforeach
        @endif
      </ul>
    </div>
  </div>
</x-panel::form.row>

@push('footer')
<script>
  $(function () {
    // 为每个组件使用唯一的选择器，避免多个组件冲突
    $('.input-autocomplete-{{ $id }}').autocomplete({
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
        $(this).closest('.autocomplete-wrapper-{{ $id }}').find('.autocomplete-list ul').append(`
          <li class="list-group list-group-item">
            <span class="autocomplete-name">${item['label']}</span>
            <button type="button" class="btn-close"></button>
            <input name="{{ $name }}" type="hidden" value="${item['value']}" />
          </li>
        `);
      }
    });

    // 为每个组件的删除按钮绑定事件
    $(document).on('click', '.autocomplete-wrapper-{{ $id }} .btn-close', function() {
      $(this).closest('li').remove();
    });
  });
</script>
@endpush

@push('footer')
<script>
  {{-- 只有在没有传入完整数据时才通过API获取已选项名称 --}}
  @if(empty($selectedItems))
    var values = @json($value);
    if (values && values.length > 0) {
      // 提取基础API路径，移除可能的 /autocomplete 后缀
      var baseApi = '{{ $api }}'.replace(/\/autocomplete$/, '');
      axios.get(baseApi + '/names?ids=' + values.join(',')).then(function(res) {
        var data = res.data;
        var list = $('.autocomplete-list-{{ $id }} ul');
        for (var i = 0; i < values.length; i++) {
          var value = values[i];
          for (var j = 0; j < data.length; j++) {
            var item = data[j];
            if (item['id'] === value) {
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
      }).catch(function(error) {
        console.warn('Failed to load selected items:', error);
      });
    }
  @endif
</script>
@endpush
