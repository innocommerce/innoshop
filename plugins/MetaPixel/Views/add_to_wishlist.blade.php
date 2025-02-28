<script>
  $(document).ready(function () {
    $(".add-wishlist").click(function () {
      let id = $(this).data('id');
      let price = $(this).data('price');
      let isWishlist = $(this).attr('data-in-wishlist') * 1;
      if (!isWishlist) {
        fbq('track', 'AddToWishlist', {
          content_ids: [id],
          value: price,
          currency: '{{ $currency }}'
        });
      }
    });
  });
</script>
