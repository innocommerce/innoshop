<script>
  ttq.track('InitiateCheckout', {
    content_type: 'product',
    content_ids: @json($content_ids),
    currency: '{{ $currency }}',
    quantity: {{ $num_items }},
    value: {{ $value }}
  });
</script>