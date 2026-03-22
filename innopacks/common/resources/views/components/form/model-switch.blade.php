<div class="form-check form-switch d-flex align-items-center js-model-switch">
  <input type="hidden" name="{{ $name }}" value="0">
  <input class="form-check-input" type="checkbox" name="{{ $name }}" value="1" 
         {{ $value ? 'checked' : '' }} 
         id="{{ $id }}" style="cursor: pointer;">
  <label class="form-check-label" for="{{ $id }}" style="cursor: pointer;">
    {{ $label }}
  </label>
</div>

@pushOnce('footer')
  <script>
    $(function () {
      // Only bind to real model-switch checkboxes. A broad `[name$="_enabled"]` also matches
      // unrelated fields such as `api_docs_enabled` and would dim the wrong `.card-body`.
      const $modelSwitchInputs = $('.js-model-switch input[type="checkbox"][name$="_enabled"]');

      $modelSwitchInputs.on('change', function () {
        const isChecked = $(this).prop('checked');
        const cardBody = $(this).closest('.card').find('.card-body');

        if (isChecked) {
          cardBody.removeClass('opacity-50');
        } else {
          cardBody.addClass('opacity-50');
        }
      });

      $modelSwitchInputs.each(function () {
        const isChecked = $(this).prop('checked');
        const cardBody = $(this).closest('.card').find('.card-body');

        if (!isChecked) {
          cardBody.addClass('opacity-50');
        }
      });
    });
  </script>
@endPushOnce