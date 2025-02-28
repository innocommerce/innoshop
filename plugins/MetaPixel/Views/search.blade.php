<script>
  fbq(
    'track', 'Search', {
      search_string: '{{ $search_string }}',
      content_ids: @json($content_ids),
      value: {{ $vlue }},
      currency: '{{ $currency }}'
    }
  );
</script>