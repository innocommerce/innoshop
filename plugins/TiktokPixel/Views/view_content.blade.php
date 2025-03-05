<script>
  ttq.track('ViewContent', {
    content_type: 'product',
    content_ids: @json($content_ids),
    value: {{ $value }},
    currency: '{{ $currency }}'
  });
</script>
