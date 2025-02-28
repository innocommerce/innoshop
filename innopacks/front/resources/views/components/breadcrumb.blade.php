<div class="breadcrumb-wrap">
  <div class="container {{ count($breadcrumbs) > 0 ? 'd-flex justify-content-between' : 'justify-content-center' }}">
    <ul class="breadcrumb">
      @foreach ($breadcrumbs as $index=>$breadcrumb)
        @if (isset($breadcrumb['url']) && $breadcrumb['url'])
          <li>
            @if($index == 0)
              <i class="bi bi-house-door-fill home-icon"></i>
            @endif
            <a href="{{ $breadcrumb['url'] }}">{{ $breadcrumb['title'] }}</a>
          </li>
        @else
          <li class="breadcrumb-item active" aria-current="page">{{ $breadcrumb['title'] }}</li>
        @endif
      @endforeach
    </ul>

    @if (count($breadcrumbs) > 0 && ($showFilter))
      <li class="d-block d-md-none" id="toggleFilterSidebar"><i class="fs-4 bi bi-funnel"></i></li>
    @endif
  </div>
</div>
