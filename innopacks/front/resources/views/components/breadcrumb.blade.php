<div class="breadcrumb-wrap">
  <div class="container">
    <ul class="breadcrumb">
      @foreach ($breadcrumbs as $index=>$breadcrumb)
        @if (isset($breadcrumb['url']) && $breadcrumb['url'])
          <li>
            @if($index == 0)
            <i class="bi bi-house-door-fill home-icon"></i>
            @endif
            <a href="{{ $breadcrumb['url'] }}">{{ $breadcrumb['title'] }}</a>
        @else
          <li class="breadcrumb-item active" aria-current="page">{{ $breadcrumb['title'] }}</li>
        @endif
      @endforeach
    </ul>
  </div>
</div>