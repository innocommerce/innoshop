<script>
$(document).ready(function() {
    @if($enableDateRange)
    // Initialize date picker
    function initLaydate() {
        if (typeof laydate === 'undefined') {
            setTimeout(initLaydate, 100);
            return;
        }
        laydate.render({
            elem: '#start-date',
            type: 'date',
            format: 'yyyy-MM-dd',
            done: function(value) {
                $('#start-date').trigger('change');
            }
        });
        laydate.render({
            elem: '#end-date',
            type: 'date',
            format: 'yyyy-MM-dd',
            done: function(value) {
                $('#end-date').trigger('change');
            }
        });
    }
    initLaydate();
    @endif

    // Button filter click handler
    $(document).on('click', '.btn-filter', function() {
        var $btn = $(this);
        var filterName = $btn.data('filter-name');
        var value = $btn.data('value');

        $('.btn-filter[data-filter-name="' + filterName + '"]').removeClass('btn-primary').addClass('btn-outline-secondary');
        $btn.removeClass('btn-outline-secondary').addClass('btn-primary');

        @if($enableDateRange)
        if (filterName === 'date_filter' && value === 'custom') {
            $('.custom-date-inputs').removeClass('d-none');
            updateFilterAndSubmit(filterName, value, false);
            return;
        } else if (filterName === 'date_filter') {
            $('.custom-date-inputs').addClass('d-none');
            $('input[name="start_date"]').val('');
            $('input[name="end_date"]').val('');
        }
        @endif

        updateFilterAndSubmit(filterName, value);
    });

    // Show more button handler
    $(document).on('click', '.show-more-btn', function() {
        var $group = $(this).closest('.filter-group');
        var $moreOptions = $group.find('.filter-more-options');
        var $icon = $(this).find('i');

        if ($moreOptions.hasClass('d-none')) {
            $moreOptions.removeClass('d-none');
            $icon.removeClass('bi-plus').addClass('bi-dash');
            $(this).contents().last().replaceWith('{{ __('panel/common.collapse') }}');
        } else {
            $moreOptions.addClass('d-none');
            $icon.removeClass('bi-dash').addClass('bi-plus');
            $(this).contents().last().replaceWith('{{ __('panel/common.more') }}');
        }
    });

    // Remove filter handler
    $(document).on('click', '.remove-filter', function() {
        var filterType = $(this).data('filter-type');
        var field = $(this).data('field');

        if (filterType === 'search') {
            $('input[name="keyword"]').val('');
            updateFilterAndSubmit('keyword', '');
        } else if (filterType === 'date') {
            $('.btn-filter[data-filter-name="date_filter"]').removeClass('btn-primary').addClass('btn-outline-secondary');
            $('.btn-filter[data-filter-name="date_filter"][data-value="all"]').removeClass('btn-outline-secondary').addClass('btn-primary');
            $('.custom-date-inputs').addClass('d-none');
            $('input[name="start_date"]').val('');
            $('input[name="end_date"]').val('');
            updateFilterAndSubmit('date_filter', 'all');
        } else if (filterType === 'range') {
            $('input[name="' + field + '_start"]').val('');
            $('input[name="' + field + '_end"]').val('');
            $('#search-form').submit();
        } else {
            $('.btn-filter[data-filter-name="' + field + '"]').removeClass('btn-primary').addClass('btn-outline-secondary');
            $('.btn-filter[data-filter-name="' + field + '"][data-value=""]').removeClass('btn-outline-secondary').addClass('btn-primary');
            updateFilterAndSubmit(field, '');
        }
    });

    // Clear all filters handler
    $(document).on('click', '.clear-all-btn', function() {
        var $form = $('#search-form');
        $('input[name="keyword"]').val('');

        @if($enableDateRange)
        $('.btn-filter[data-filter-name="date_filter"]').removeClass('btn-primary').addClass('btn-outline-secondary');
        $('.btn-filter[data-filter-name="date_filter"][data-value="all"]').removeClass('btn-outline-secondary').addClass('btn-primary');
        $('.custom-date-inputs').addClass('d-none');
        $('input[name="start_date"]').val('');
        $('input[name="end_date"]').val('');
        @endif

        // Clear range filters
        $('.filter-group[data-filter-type="range"]').each(function() {
            var filterName = $(this).data('filter-name');
            $('input[name="' + filterName + '_start"]').val('');
            $('input[name="' + filterName + '_end"]').val('');
        });

        // Reset button filters
        $('.btn-filter').each(function() {
            var $btn = $(this);
            if ($btn.data('is-all') == '1') {
                $btn.removeClass('btn-outline-secondary').addClass('btn-primary');
            } else {
                $btn.removeClass('btn-primary').addClass('btn-outline-secondary');
            }
        });

        $form.submit();
    });

    // Helper function to update filter and submit form
    function updateFilterAndSubmit(name, value, shouldSubmit) {
        shouldSubmit = typeof shouldSubmit !== 'undefined' ? shouldSubmit : true;
        var $form = $('#search-form');
        var $hidden = $form.find('input[name="' + name + '"]');
        if ($hidden.length === 0) {
            $hidden = $('<input type="hidden">').attr('name', name);
            $form.append($hidden);
        }
        $hidden.val(value);
        if (shouldSubmit) {
            $form.submit();
        }
    }

    // Initialize hidden inputs for current button filter values
    @foreach($filters as $filterGroup)
        @php
            $filterType = $filterGroup['type'] ?? 'button';
            $filterName = $filterGroup['name'];
        @endphp
        @if($filterType !== 'range')
            @php $currentValue = request($filterName, ''); @endphp
            @if($currentValue !== '')
                (function() {
                    var $form = $('#search-form');
                    var $hidden = $form.find('input[name="{{ $filterName }}"]');
                    if ($hidden.length === 0) {
                        $hidden = $('<input type="hidden">').attr('name', '{{ $filterName }}');
                        $form.append($hidden);
                    }
                    $hidden.val('{{ $currentValue }}');
                })();
            @endif
        @endif
    @endforeach
});
</script>
