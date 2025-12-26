{{-- 模块空状态组件 --}}
@if (request('design'))
  <div class="module-empty-state">
    <div class="module-empty-content">
      <i class="bi {{ $icon ?? 'bi-inbox' }}"></i>
      <span>{{ $message ?? __('PageBuilder::common.no_content') }}</span>
    </div>
  </div>
@endif

<style>
.module-empty-state {
  text-align: center;
  padding: 60px 20px;
}

.module-empty-content {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 16px;
}

.module-empty-content i {
  font-size: 48px;
  color: #ccc;
  display: block;
}

.module-empty-content span {
  font-size: 16px;
  color: #999;
  display: block;
}
</style>

