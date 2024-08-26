<div class="account-sidebar">
  <div class="account-user">
    <div class="profile"><img src="{{ image_resize($customer->avatar) }}" class="img-fluid"></div>
    <div class="account-name">
      <div class="fw-bold name">{{ __('front/account.hello') }}, {{ $customer->name }}</div>
      <div class="text-secondary email">{{ $customer->email }}</div>
    </div>
  </div>
  <ul class="account-links">
    <li class="{{ equal_route_name('front.account.index') ? 'active' : '' }}">
      <a href="{{ account_route('index') }}"><i class="bi bi-person"></i>{{ front_trans('account.account') }}</a>
    </li>
    <li class="{{ equal_route_name('front.account.orders.index') ? 'active' : '' }}">
      <a href="{{ account_route('orders.index') }}"><i class="bi bi-clipboard2-check"></i>{{ front_trans('account.orders') }}</a>
    </li>
    <li class="{{ equal_route_name('front.account.favorites.index') ? 'active' : '' }}">
      <a href="{{ account_route('favorites.index') }}"><i class="bi bi-star"></i>{{ front_trans('account.favorites') }}</a>
    </li>
    <li class="{{ equal_route_name('front.account.addresses.index') ? 'active' : '' }}">
      <a href="{{ account_route('addresses.index') }}"><i class="bi bi-geo-alt"></i>{{ front_trans('account.addresses') }}</a>
    </li>
    <li class="{{ equal_route_name('front.account.order_returns.index') ? 'active' : '' }}">
      <a href="{{ account_route('order_returns.index') }}"><i class="bi bi-backpack"></i>{{ front_trans('account.order_returns') }}</a>
    </li>
    <li class="{{ equal_route_name('front.account.edit.index') ? 'active' : '' }}">
      <a href="{{ account_route('edit.index') }}"><i class="bi bi-pen"></i>{{ front_trans('account.edit') }}</a>
    </li>
    <li class="{{ equal_route_name('front.account.password.index') ? 'active' : '' }}">
      <a href="{{ account_route('password.index') }}"><i class="bi bi-shield-lock"></i>{{ front_trans('account.password') }}</a>
    </li>
    <li><a href="{{ account_route('logout') }}"><i class="bi bi-box-arrow-left"></i>{{ front_trans('account.logout') }}</a></li>
  </ul>
</div>