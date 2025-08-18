{{-- 分类详细描述区域 --}}
@if($category->fallbackName('content'))
<div class="mt-5">
  <div class="card border-0 shadow-sm">
    <div class="card-header bg-primary text-white py-3">
      <h5 class="card-title mb-0 fw-bold">
        <i class="bi bi-file-text me-2"></i>
        {{ __('front/category.content') }}
      </h5>
    </div>
    <div class="card-body p-4">
      <div class="text-muted lh-lg">
        {!! $category->fallbackName('content') !!}
      </div>
    </div>
  </div>
</div>
@endif