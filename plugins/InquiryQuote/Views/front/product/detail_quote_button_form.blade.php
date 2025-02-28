@if (!current_customer())
  <a class="btn btn-primary" style="height: 50px; line-height: 50px;" href="javascript:inno.openLogin()">
    {{ __('InquiryQuote::product.inquiry') }}
  </a>
@else
  <button class="btn btn-primary ms-2" data-bs-toggle="modal"
          data-bs-target="#myModal">{{ __('InquiryQuote::product.inquiry') }}</button>
@endif

<div class="modal fade" id="myModal" tabindex="-1" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="myModalLabel">{{ __('InquiryQuote::product.quotation_details') }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="inquiryForm">
          <div class="mb-3">
            <label for="quantity" class="form-label">{{ __('InquiryQuote::product.amount') }}</label>
            <input type="number" class="form-control" id="quantity"
                   placeholder="{{ __('InquiryQuote::product.number_inputs') }}" required>
            <div id="quantityError" class="text-danger d-none">
              {{ __('InquiryQuote::product.positive_integer') }}</div>
          </div>

          <div class="mb-3">
            <label for="inquiry_price" class="form-label">{{ __('InquiryQuote::product.price') }}</label>
            <input type="number" class="form-control" id="inquiry_price"
                   placeholder="{{ __('InquiryQuote::product.price_inputs') }}" required>
            <div id="inquiry_priceError" class="text-danger d-none">
              {{ __('InquiryQuote::product.nonnegative') }}</div>
          </div>

          <div class="mb-3">
            <label for="description" class="form-label">{{ __('InquiryQuote::product.describe') }}</label>
            <textarea class="form-control" id="description" rows="3"
                      placeholder="{{ __('InquiryQuote::product.describe_inputs') }}"></textarea>
          </div>
        </form>
      </div>
      <div class="modal-footer d-flex justify-content-center">
        <button type="button" class="btn btn-primary border-2 rounded" style="font-weight: normal;" id="submitInquiry">
          提交
        </button>
      </div>
    </div>
  </div>
</div>

@push('footer')
  <script>
    const modalElement = $('#myModal');
    const modal = new bootstrap.Modal(modalElement[0]);
    $('#submitInquiry').click(function () {
      const quantity = $('#quantity').val();
      const inquiry_price = $('#inquiry_price').val();
      const description = $('#description').val();
      let isValid = true;
      var sku_code = $('li.sku span.value').html();
      const quantityRegex = /^\d+$/;
      if (!quantityRegex.test(quantity)) {
        isValid = false;
        $('#quantityError').show();
      } else {
        $('#quantityError').hide();
      }
      const priceRegex = /^\d+(\.\d+)?$/;
      if (!priceRegex.test(inquiry_price)) {
        isValid = false;
        $('#inquiry_priceError').show();
      } else {
        $('#inquiry_priceError').hide();
      }
      if (isValid) {
        const inquiryData = {
          quantity: quantity,
          inquiry_price: inquiry_price,
          sku_code: sku_code,
          description: description
        };

        axios.post(`${urls.api_base}/inquiries`, inquiryData)
          .then(function (response) {
            inno.msg(response.message)
            modal.hide();
          })
          .catch(function (error) {
            inno.msg(error)
          });
      }
    });
  </script>
@endpush
