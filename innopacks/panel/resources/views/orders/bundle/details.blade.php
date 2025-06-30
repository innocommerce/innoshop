@if ($item->item_type === 'bundle' && !empty($item->reference))
  <div class="mt-1">
    <a href="javascript:void(0)"
       class="text-primary text-decoration-none small"
       onclick="showBundleDetails({{ $item->id }}, {{ json_encode($item->reference) }})"
       title="点击查看组合商品详情">
      <i class="bi bi-box-seam me-1"></i>包含 {{ count($item->reference['bundles'] ?? []) }} 件商品
    </a>
  </div>
@endif
