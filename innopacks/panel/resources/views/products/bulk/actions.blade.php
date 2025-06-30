<div class="d-flex align-items-center gap-2 flex-wrap">
  <div class="btn-group me-2">
    <button type="button" class="btn btn-sm btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false"
            :disabled="checkedIds.length === 0">
      <i class="bi bi-pencil-square"></i> 
      <span class="d-none d-sm-inline">{{ __('panel/product.bulk_settings') }}</span>
      <span class="d-sm-none">{{ __('panel/common.bulk') }}</span>
      <span v-if="checkedIds.length > 0" class="badge bg-primary text-white ms-1">@{{ checkedIds.length }}</span>
    </button>
    <ul class="dropdown-menu">
      <li><a class="dropdown-item" href="#" @click.prevent="bulkAction('price')">
        <i class="bi bi-tag me-2"></i>{{ __('panel/product.price') }}</a></li>
      <li><a class="dropdown-item" href="#" @click.prevent="bulkAction('categories')">
        <i class="bi bi-collection me-2"></i>{{ __('panel/product.categories') }}</a></li>
      <li><a class="dropdown-item" href="#" @click.prevent="bulkAction('quantity')">
        <i class="bi bi-box me-2"></i>{{ __('panel/product.quantity') }}</a></li>
      <li><hr class="dropdown-divider"></li>
      <li><a class="dropdown-item" href="#" @click.prevent="bulkAction('publish')">
        <i class="bi bi-eye me-2"></i>{{ __('panel/product.publish') }}</a></li>
      <li><a class="dropdown-item" href="#" @click.prevent="bulkAction('unpublish')">
        <i class="bi bi-eye-slash me-2"></i>{{ __('panel/product.unpublish') }}</a></li>
    </ul>
  </div>
  <button class="btn btn-sm btn-outline-danger" @click="deleteAll" :disabled="checkedIds.length === 0">
    <i class="bi bi-trash"></i> 
    <span class="d-sm-inline">{{ __('panel/common.delete') }}</span>
    <span v-if="checkedIds.length > 0" class="badge bg-danger text-white ms-1">@{{ checkedIds.length }}</span>
  </button>
</div> 