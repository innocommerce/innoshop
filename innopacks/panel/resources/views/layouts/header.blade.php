<div class="header-box d-flex justify-content-between align-items-center px-3">
  <div></div>
  <div class="d-flex">
    <div class="header-item dropdown d-flex align-items-center">
      <div class="wh-40 rounded-circle overflow-hidden">
        <img src="{{ image_resize() }}" class="img-fluid">
      </div>
      <span class="ms-2">{{ current_admin()->name }} <i class="bi bi-chevron-down"></i></span>

      <ul class="dropdown-menu">
        <li><a class="dropdown-item" href="{{ route('home.index') }}" target="_blank">前台首页</a></li>
        <li><a class="dropdown-item" href="{{ panel_route('account.index') }}">个人中心</a></li>
        <li><a class="dropdown-item" href="{{ panel_route('logout.index') }}">退出登录</a></li>
      </ul>
    </div>
  </div>
</div>