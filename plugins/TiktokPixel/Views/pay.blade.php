<script>
  ttq.track('PlaceAnOrder', {
    content_ids:  @json($content_ids),
    content_type: 'product',
    currency: '{{ $currency }}',
    num_items: {{ $num_items }},
    value: {{ $value }}
  });
</script>
