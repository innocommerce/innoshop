<div class="form-check form-switch d-flex align-items-center">
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
      $('input[name$="_enabled"]').on('change', function () {
        // 可以在这里添加额外的逻辑，比如显示/隐藏相关配置项
        const isChecked = $(this).prop('checked');
        const cardBody = $(this).closest('.card').find('.card-body');
        
        if (isChecked) {
          cardBody.removeClass('opacity-50');
        } else {
          cardBody.addClass('opacity-50');
        }
      });
      
      // 初始化状态
      $('input[name$="_enabled"]').each(function() {
        const isChecked = $(this).prop('checked');
        const cardBody = $(this).closest('.card').find('.card-body');
        
        if (!isChecked) {
          cardBody.addClass('opacity-50');
        }
      });
    });
  </script>
@endPushOnce