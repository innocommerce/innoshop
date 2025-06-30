@if(isset($options) && count($options))
<div class="sorter-component d-flex align-items-center gap-2">
  <div class="text-muted sorter-label">{{ __('panel/common.sort') }}:</div>
  <select class="form-select form-select-sm sorter-field" id="sort-field" name="sort">
    <option value="">{{ __('panel/common.none') }}</option>
    @foreach($options as $value => $label)
      <option value="{{ $value }}" {{ request('sort') == $value ? 'selected' : '' }}>{{ $label }}</option>
    @endforeach
  </select>
  <select class="form-select form-select-sm sorter-direction" id="sort-direction" name="order">
    <option value="" class="none-option" {{ !request('sort') ? '' : 'style=display:none' }}>{{ __('panel/common.none') }}</option>
    <option value="desc" {{ request('order', 'desc') == 'desc' ? 'selected' : '' }}>{{ __('panel/common.desc') }} ↓</option>
    <option value="asc" {{ request('order') == 'asc' ? 'selected' : '' }}>{{ __('panel/common.asc') }} ↑</option>
  </select>
</div>

@push('footer')
<script>
$(document).ready(function() {
  const $sortField = $('#sort-field');
  const $sortDirection = $('#sort-direction');
  const $noneOption = $sortDirection.find('.none-option');
  
  $sortField.on('change', function() {
    const fieldValue = $(this).val();
    
    if (fieldValue === '') {
      $noneOption.show();
      $sortDirection.val('');
    } else {
      $noneOption.hide();
      if ($sortDirection.val() === '' || $sortDirection.val() === null) {
        $sortDirection.val('desc');
      }
    }
    $sortDirection.trigger('change.select2');
  });
  
  if ($sortField.val() !== '') {
    $noneOption.hide();
    if ($sortDirection.val() === '') {
      $sortDirection.val('desc');
    }
  }
  
  // Auto-submit on dropdown change
  $sortField.add($sortDirection).on('change', function() {
    const currentUrl = new URL(window.location.href);
    const sortField = $sortField.val();
    const sortOrder = $sortDirection.val();
    
    // Handle "none" option
    if (sortField) {
      currentUrl.searchParams.set('sort', sortField);
    } else {
      currentUrl.searchParams.delete('sort');
    }
    
    if (sortOrder) {
      currentUrl.searchParams.set('order', sortOrder);
    } else {
      currentUrl.searchParams.delete('order');
    }
    
    window.location.href = currentUrl.toString();
  });
  
  // Initialize selection state
  const urlParams = new URLSearchParams(window.location.search);
  if (!urlParams.has('sort')) {
    $sortField.val('');
  }
  if (!urlParams.has('order')) {
    $sortDirection.val('');
  }
});
</script>
@endpush
@endif