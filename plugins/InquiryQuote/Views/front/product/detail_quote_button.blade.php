@if (!current_customer())
  <button class="btn btn-primary" onclick="inno.openLogin()">
    {{ __('InquiryQuote::product.inquiry') }}
  </button>
@else
  <button id="add-inquiry" class="btn btn-primary ms-2" data-product-id="{{ $product->id }}">{{ __('InquiryQuote::product.inquiry') }}</button>
@endif

@push('footer')
  <script>
    $('#add-inquiry').click(function () {
      let sku_code = $('li.sku span.value').html();
      let quantity = $('input.product-quantity').val();

      const inquiryData = {
        quantity: quantity,
        sku_code: sku_code,
      };

      axios.post(`${urls.api_base}/inquiries`, inquiryData)
        .then(function (response) {
          inno.msg(response.message)
          window.location.reload();
        })
        .catch(function (error) {
          inno.msg(error)
        });
    });
  </script>
@endpush
