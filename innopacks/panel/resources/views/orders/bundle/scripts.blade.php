<script>
function showBundleDetails(itemId, reference) {
  if (!reference || !reference.bundles) {
    inno.msg('{{ __('panel/order.no_bundle_info') }}');
    return;
  }

  const tbody = document.getElementById('bundleItemsTable');
  tbody.innerHTML = '';

  reference.bundles.forEach(function(bundleItem, index) {
    const row = `
      <tr class="border-bottom">
        <td class="py-3">
          <div class="d-flex align-items-center">
            <div class="product-image me-3" style="width: 50px; height: 50px;">
              <img src="${bundleItem.image || '/images/placeholder.png'}" 
                   class="img-fluid rounded border" 
                   style="width: 50px; height: 50px; object-fit: cover;"
                   alt="${bundleItem.product_name}">
            </div>
            <div>
              <div class="fw-semibold text-dark">${bundleItem.product_name}</div>
            </div>
          </div>
        </td>
        <td class="py-3">
          <code class="bg-light px-2 py-1 rounded">${bundleItem.sku_code}</code>
        </td>
        <td class="py-3 text-center">
          <span class="badge bg-primary">${bundleItem.quantity}</span>
        </td>
      </tr>
    `;
    tbody.innerHTML += row;
  });

  // 显示模态框
  const modal = new bootstrap.Modal(document.getElementById('bundleDetailsModal'));
  modal.show();
}
</script> 