<section class="container-fluid bg-primary text-white py-5" style="margin-bottom: -50px">
  <div class="container py-2">
    <div class="row align-items-center">
      <div class="col-md-6 mb-3 mb-md-0">
        <h2>{{ __('SubNews::common.subscribe_newsletter') }}</h2>
        <p class="mb-0">{{ __('SubNews::common.email_subscribe_description') }}</p>
      </div>
      <div class="col-md-6">
        <form method="post" action="{{ front_route('sub_news.subscribe') }}" class="d-flex flex-column flex-md-row" id="sub-news">
          <input type="email" name="email" class="form-control mb-2 mb-md-0 me-md-2" placeholder="{{ __('SubNews::common.enter_your_email') }}" aria-label="Email" required>
          <button type="submit" class="btn btn-dark text-white" aria-label="Subscribe to newsletter">{{ __('SubNews::common.subscribe_now') }}</button>
        </form>
      </div>
    </div>
  </div>
</section>

<script>
  $(document).ready(function () {
    $("#sub-news").submit(function (event) {
      event.preventDefault();
      let email = $("#sub-news input[name=email]").val();
      if (email === "") {
        inno.msg("{{ __('SubNews::common.enter_your_email') }}");
        return false;
      }
      $.ajax({
        type: "POST",
        url: "{{ front_route('sub_news.subscribe') }}",
        dataType: 'json',
        data: {
          email: email,
          _token: "{{ csrf_token() }}"
        },
        success: function (response) {
          console.log("Success: ", response);
          inno.msg("{{ __('SubNews::common.submitted_successfully') }}");
        },
        error: function (res, status, error) {
          console.log(res, status, error);
          inno.msg(res.responseJSON.message);
        },
      });
    });
  });
</script>