<script>
  $(document).ready(function () {
    $(".add-cart, .btn-add-cart, .buy-now").click(function () {
      let id = $(this).data('id');
      let price = $(this).data('price');

      ttq.track('AddToCart', {
        content_type: 'product',
        content_ids: [id],
        value: price,
        currency: '{{ $currency }}',
        quantity: quantity
      });
    });
  });
</script>
