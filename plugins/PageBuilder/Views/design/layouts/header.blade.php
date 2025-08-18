<header class="navbar navbar-expand-lg navbar-light bg-white border-bottom shadow-sm">
  <div class="container-fluid">
    {{-- 左侧返回按钮和品牌 --}}
    <div class="d-flex align-items-center">
      {{-- 返回按钮 - 多个选项供选择 --}}
      <a href="{{ panel_route('dashboard.index') }}" class="btn btn-link text-muted p-0 me-4" :title="lang.back_to_admin">
        <i class="bi bi-arrow-left-circle" style="font-size: 18px;"></i>
      </a>
      
      {{-- Logo 和插件名称 --}}
      <a href="{{ panel_route('pbuilder.index') }}" class="btn btn-link text-muted p-0 me-4 d-flex align-items-center text-decoration-none">
        <img src="{{ image_origin(system_setting('logo', 'images/logo.png')) }}" alt="InnoShop" height="28" class="me-2">
        <div class="d-flex flex-column">
          <span class="fw-bold text-primary" style="font-size: 14px;">{{ $plugin->getLocaleName() }}</span>
        </div>
      </a>
    </div>
    
    {{-- 中央设备切换按钮 --}}
    <div class="device-switch mx-auto">
      <div class="device-wrap">
        <div :class="{ active: design.type === 'pc' }" @click="switchDevice('pc')" :title="lang.pc_preview">
          <i class="el-icon-monitor"></i>
          <span>@{{ lang.pc }}</span>
        </div>
        <div :class="{ active: design.type === 'mobile' }" @click="switchDevice('mobile')" :title="lang.mobile_preview">
          <i class="el-icon-mobile-phone"></i>
          <span>@{{ lang.mobile }}</span>
        </div>
      </div>
    </div>
    
    {{-- 右侧操作按钮 --}}
    <div class="navbar-nav d-flex align-items-center">
      {{-- 保存状态指示器 --}}
      <div class="nav-item me-3">
        <div class="d-flex align-items-center">
          <div class="save-status me-2 d-flex align-items-center">
            <div class="status-dot" :class="{ 'saved': saveStatus === 'saved', 'unsaved': saveStatus === 'unsaved' }"></div>
            <small class="text-muted save-status-text" v-text="saveStatusText"></small>
          </div>
        </div>
      </div>
      
      {{-- 操作按钮组 --}}
      <div class="nav-item me-2">
        <button class="btn btn-outline-secondary action-btn" @click="importDemoData" :title="lang.import_demo + lang.data">
          <i class="el-icon-download"></i>
          <span class="d-none d-md-inline">@{{ lang.import_demo }}</span>
        </button>
      </div>
      
      <div class="nav-item me-2">
        <a class="btn btn-outline-info action-btn" href="{{ front_route('home.index') }}" target="_blank" :title="lang.view_website">
          <i class="el-icon-view"></i>
          <span class="d-none d-md-inline">@{{ lang.preview }}</span>
        </a>
      </div>
      
      <div class="nav-item">
        <button class="btn btn-primary action-btn" @click="saveButtonClicked" :disabled="saveStatus === 'saving'">
          <i class="el-icon-check" v-if="saveStatus !== 'saving'"></i>
          <i class="el-icon-loading" v-else></i>
          <span class="d-none d-md-inline">@{{ lang.save }}</span>
        </button>
      </div>
    </div>
  </div>
</header>