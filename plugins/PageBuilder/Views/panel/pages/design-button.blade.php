{{-- PageBuilder 设计按钮 --}}
  <a href="{{ panel_route('pbuilder.page.index', [$item->slug ?? $item->id]) }}" target="_blank">
    <el-button size="small" plain type="success">
      <i class="bi bi-palette"></i> {{ __('PageBuilder::common.design') ?? '设计' }}
    </el-button>
  </a>
