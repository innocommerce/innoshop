<div class="mt-3">
  <label class="form-label">{{ __('panel/product.bundle_items') }}</label>
  <div class="bundle-items-list">
    @foreach($product->bundles ?? [] as $i => $bundle)
      <div class="bundle-item d-flex align-items-center mb-2">
        <div class="input-group flex-grow-1">
          <input type="text" class="form-control sku-autocomplete" 
                   name="bundles[{{ $i }}][sku_code_text]" 
                   value="{{ old("bundles.{$i}.sku_code_text", $bundle->sku->full_name ?? '') }}" 
                   placeholder="{{ __('panel/product.bundle_sku_placeholder') }}" 
                   autocomplete="off" required>
          <input type="hidden" 
                   name="bundles[{{ $i }}][sku_id]" 
                   value="{{ old("bundles.{$i}.sku_id", $bundle->sku_id) }}">
        </div>
        <input type="number" class="form-control mx-2" 
                 name="bundles[{{ $i }}][quantity]" 
                 value="{{ old("bundles.{$i}.quantity", $bundle->quantity) }}" 
                 min="1" required style="width:100px;">
        <button type="button" class="btn btn-danger btn-sm remove-bundle-item">{{ __('panel/common.delete') }}</button>
      </div>
    @endforeach
  </div>
  <button type="button" class="btn btn-primary btn-sm mt-2" id="add-bundle-item">{{ __('panel/product.add_bundle_item') }}</button>
</div>

@push('footer')
<script>
$(function () {
  function initSkuAutocomplete() {
    $('.sku-autocomplete').autocomplete({
      source: function(request, response) {
        let keyword = $(this).val();
        let url = '/api/products/skus?keyword=' + keyword + '&limit=10';
        axios.get(url).then(res => {
          response($.map(res.data, function(item) {
            return {
              label: item.product_name + (item.variant_label ? ' (' + item.variant_label + ')' : ''),
              value: item.code,
              id: item.id
            };
          }));
        });
      },
      select: function(item) {
        $(this).val(item.label);
        $(this).closest('.input-group').find('input[type=hidden]').val(item.id);
        return false;
      },
      minLength: 1
    });
  }
  // 初始化已有
  initSkuAutocomplete();
  // 动态添加后初始化
  $(document).on('focus', '.sku-autocomplete', function() {
    if (!$(this).data('ui-autocomplete')) {
      initSkuAutocomplete();
    }
  });

  // 动态添加组合明细
  $('#add-bundle-item').on('click', function() {
    const index = $('.bundle-item').length;
    const html = `
      <div class="bundle-item d-flex align-items-center mb-2">
        <div class="input-group flex-grow-1">
          <input type="text" class="form-control sku-autocomplete" name="bundles[${index}][sku_code_text]" placeholder="{{ __('panel/product.bundle_sku_placeholder') }}" autocomplete="off" required>
          <input type="hidden" name="bundles[${index}][sku_id]">
        </div>
        <input type="number" class="form-control mx-2" name="bundles[${index}][quantity]" value="1" min="1" required style="width:100px;">
        <button type="button" class="btn btn-danger btn-sm remove-bundle-item">{{ __('panel/common.delete') }}</button>
      </div>
    `;
    $('.bundle-items-list').append(html);
    // 新增后初始化自动完成
    initSkuAutocomplete();
  });
  // 删除明细
  $(document).on('click', '.remove-bundle-item', function() {
    $(this).closest('.bundle-item').remove();
  });
});
</script>
@endpush 