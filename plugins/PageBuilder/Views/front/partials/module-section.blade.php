{{-- 模块section模板 - 用于AJAX预览更新 --}}
<section id="module-{{ $module_id }}" class="module-item {{ request()->get('design') ? 'module-item-design' : '' }}" data-module-id="{{ $module_id }}">
  
  @if (request()->get('design'))
    @include('PageBuilder::front.partials.module-edit-buttons', ['module' => $module])
  @endif
  
  <div class="module-content">
    @include('PageBuilder::front.modules.' . $code, [
      'module' => $module,
      'content' => $content,
      'module_id' => $module_id,
    ])
  </div>
</section> 