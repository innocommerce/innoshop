@if($categories['data'] ?? [])
  <div class="row d-block d-md-none">
    <div class="category-wrap">
      <nav class="nav plugin-market-nav overflow-x-scroll">
        <a class="nav-link plugin-market-nav-item text-start my-2 {{ empty(request()->category) ? 'panel-item-active-border' : 'text-dark' }}"
           aria-current="page" href="{{ panel_route('plugin_market.index') }}">All</a>
        @foreach($categories['data'] ?? [] as $category)
          <a class="nav-link plugin-market-nav-item text-start my-2 {{request()->category == $category['slug'] ? 'panel-item-active-border' : 'text-dark' }}"
             aria-current="page"
             href="{{ panel_route('plugin_market.index').'?category=' . $category['slug'] }}"> {{$category['name']}}</a>
        @endforeach
      </nav>
    </div>
    <hr>
  </div>
@endif