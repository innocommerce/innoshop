<div class="filter-sidebar">
  <div class="filter-sidebar-item">
    <div class="title">{{ __('front/category.category') }}</div>
    <div class="content">
      <div class="accordion" id="filter-category">
        @foreach ($categories as $key => $category)
        <div class="accordion-item">
          <div class="accordion-title">
            <a href="{{ $category['url'] }}" class="">{{ $category['name'] }}</a>
            @if ($category['children'])
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#filter-collapse-{{ $key }}"></button>
            @endif
          </div>
          @if ($category['children'])
          <div id="filter-collapse-{{ $key }}" class="accordion-collapse collapse" data-bs-parent="#filter-category">
            <div class="accordion-body">
              <div class="accordion" id="filter-category-{{ $key }}">
                @foreach ($category['children'] as $child)
                <div class="accordion-item">
                  <div class="accordion-title">
                    <a href="{{ $child['url'] }}" class="">{{ $child['name'] }}</a>
                    @if (isset($child['children']) && $child['children'])
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#filter-collapse-{{ $key }}-{{ $loop->index }}"></button>
                    @endif
                  </div>
                  @if (isset($child['children']) && $child['children'])
                  <div id="filter-collapse-{{ $key }}-{{ $loop->index }}" class="accordion-collapse collapse" data-bs-parent="#filter-category-{{ $key }}">
                    <div class="accordion-body">
                      <div class="accordion" id="filter-category-{{ $key }}-{{ $loop->index }}">
                        @foreach ($child['children'] as $subChild)
                        <div class="accordion-item">
                          <div class="accordion-title">
                            <a href="{{ $subChild['url'] }}" class="">{{ $subChild['name'] }}</a>
                          </div>
                        </div>
                        @endforeach
                      </div>
                    </div>
                  </div>
                  @endif
                </div>
                @endforeach
              </div>
            </div>
          </div>
          @endif
        </div>
        @endforeach
      </div>
    </div>
  </div>
</div>

@push('footer')
<script>
$('#filter-category a').each(function() {
  if ($(this).attr('href') === window.location.href) {
    $(this).addClass('text-primary');
    $(this).parents('.accordion-item').each(function() {
      $(this).find('.accordion-button').attr('aria-expanded', true).siblings('a').addClass('text-primary');
      $(this).find('.accordion-collapse').addClass('show');
    });
  }
});
</script>
@endpush