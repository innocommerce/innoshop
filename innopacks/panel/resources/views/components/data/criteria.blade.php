@if($criteria)
<div class="bg-light rounded border mb-3" id="criteria-container">
  <form action="{{ $action }}" method="GET" id="filter-form">
    <!-- Hidden fields to preserve sorting parameters -->
    <input type="hidden" name="sort" id="hidden-sort" value="{{ request('sort') }}">
    <input type="hidden" name="order" id="hidden-order" value="{{ request('order') }}">
    
    <!-- Filter Header -->
    <div class="d-flex justify-content-between align-items-center px-3 py-2 filter-header">
      <div class="d-flex align-items-center">
        <i class="bi bi-funnel me-1 text-secondary"></i> <span class="fw-medium">{{ __('panel/common.filter') }}</span>
        <a href="#" class="text-decoration-none small text-primary ms-3 toggle-filters">
          <i class="bi bi-chevron-{{ $hasFilters ? 'up' : 'down' }}"></i>
          <span>{{ $hasFilters ? __('panel/common.collapse') : __('panel/common.expand') }}</span>
        </a>
      </div>
      <div class="d-flex align-items-center">
        <button type="submit" class="btn btn-sm btn-primary me-2">
          <i class="bi bi-search"></i> {{ __('panel/common.search') }}
        </button>
        <a href="{{ $action }}" class="btn btn-sm btn-outline-secondary @if($export) me-2 @endif">
          <i class="bi bi-arrow-repeat"></i> {{ __('panel/common.reset') }}
        </a>
        @if($export)
          <a href="{{ $getExportUrl() }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-file-earmark-excel"></i> {{ __('panel/common.export') }}
          </a>
        @endif
      </div>
    </div>

    <!-- Filter Form -->
    <div id="filter-container" class="px-3 py-2 border-top" style="display: {{ $hasFilters ? 'block' : 'none' }};">
      <!-- Filter Criteria -->
      <div class="row g-2 mb-0">
        @foreach($criteria as $item)
          @include('panel::components.data.criteria.' . $item['type'], ['item' => $item])
        @endforeach
      </div>
    </div>
  </form>
</div>

@push('footer')
<script>
$(document).ready(function() {
  // Check if filter conditions exist
  var hasFilters = {{ $hasFilters ? 'true' : 'false' }};
  
  // Toggle filter area visibility
  $('.toggle-filters').on('click', function(e) {
    e.preventDefault();
    var $container = $('#filter-container');
    var $icon = $(this).find('i');
    var $text = $(this).find('span');
    
    if ($container.is(':visible')) {
      $container.slideUp(200, function() {
        $container.removeClass('border-top');
        $icon.removeClass('bi-chevron-up').addClass('bi-chevron-down');
        $text.text('{{ __('panel/common.expand') }}');
      });
    } else {
      $container.addClass('border-top').slideDown(200, function() {
        $icon.removeClass('bi-chevron-down').addClass('bi-chevron-up');
        $text.text('{{ __('panel/common.collapse') }}');
      });
    }
  });
  
  // Initialize border state
  if (!hasFilters) {
    $('#filter-container').removeClass('border-top');
  }

  // Listen for form submission to ensure we get the latest sorting parameters
  $('#filter-form').on('submit', function() {
    // Get sorting values from the page if they exist
    var sortField = $('#sort-field').val();
    var sortDirection = $('#sort-direction').val();
    
    // If sort controls exist, use their values
    if (sortField !== undefined) {
      $('#hidden-sort').val(sortField);
    }
    
    if (sortDirection !== undefined) {
      $('#hidden-order').val(sortDirection);
    }
    
    // If hidden fields are empty, get sort parameters from URL
    if ($('#hidden-sort').val() === '') {
      const urlParams = new URLSearchParams(window.location.search);
      if (urlParams.has('sort')) {
        $('#hidden-sort').val(urlParams.get('sort'));
      }
    }
    
    if ($('#hidden-order').val() === '') {
      const urlParams = new URLSearchParams(window.location.search);
      if (urlParams.has('order')) {
        $('#hidden-order').val(urlParams.get('order'));
      }
    }
    
    // Set submit button to loading state
    $('button[type="submit"]').prop('disabled', true).html('<i class="bi bi-hourglass-split"></i> {{ __('panel/common.loading') }}');
  });
});
</script>
@endpush
@endif
