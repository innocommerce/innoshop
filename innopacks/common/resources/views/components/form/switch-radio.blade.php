<x-panel::form.row :title="$title">
  <div class="form-check form-switch">
    <input class="form-check-input" type="checkbox" role="switch" name="{{ $name }}" @if($value) checked @endif >
    <input type="hidden" name="{{ $name }}" value="{{ $value }}">
  </div>
  @if ($description ?? '')
  <div class="mt-2 text-muted small">
    <i class="bi bi-info-circle me-1"></i>{!! $description !!}
  </div>
  @endif
</x-panel::form.row>

@pushOnce('footer')
  <script>
    $(function () {
      $('input[role="switch"]').on('change', function () {
        $(this).next().val($(this).prop('checked') ? 1 : 0);
      });
    });
  </script>
@endPushOnce
