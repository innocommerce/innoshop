@if($categories['data'] ?? [])
  <div class="col-1 col-md-3 col-lg-3 col-xl-2 d-none d-md-block">
    <div class="category-wrap">
      <nav class="nav plugin-market-nav flex-column mt-3">
        <a class="nav-link plugin-market-nav-item text-start my-1 {{ empty(request()->category) ? 'panel-item-active' : 'text-dark' }} rounded-3"
           aria-current="page" href="{{ panel_route('theme_market.index') }}"><h6 class="align-middle mb-0"><i
                class="bi {{ request()->category == 'all' || request()->category == '' ? 'bi-record-circle' : 'bi-record' }} mx-2"></i>
            All</h6></a>
        @foreach($categories['data'] as $category)
          <a class="nav-link plugin-market-nav-item text-start my-1 {{request()->category == $category['slug'] ? 'panel-item-active' : 'text-dark' }} rounded-3"
             aria-current="page" href="{{ panel_route('theme_market.index').'?category=' . $category['slug']}}">
            <h6 class="align-middle mb-0"><i
                  class="bi {{ request()->category == $category['slug'] ? 'bi-record-circle' : 'bi-record' }} mx-2"></i> {{$category['name']}}
            </h6></a>
        @endforeach
      </nav>
    </div>
  </div>
@endif
