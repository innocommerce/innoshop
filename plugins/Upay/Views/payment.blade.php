<script src="{{ asset('vendor/qrcode/qrcode.min.js') }}"></script>

<div class="container">
  <div class="card-body">
    <div class="fs-5 mb-3">请转账或扫描支付</div>
    <div class="row">
      <p>{{ plugin_setting('upay.comment') }}</p>
      <div class="col-4">
        <p id="erc-20"></p>
        <p>ERC20: {{ plugin_setting('upay.address_erc20') }}</p>
      </div>

      <div class="col-4">
        <p id="trc-20"></p>
        <p>TRC20: {{ plugin_setting('upay.address_trc20') }}</p>
      </div>

      <div class="col-4">
        <p id="bep-20"></p>
        <p>BEP20: {{ plugin_setting('upay.address_bep20') }}</p>
      </div>
    </div>
    <button type="button" class="btn btn-primary btn-bank-transfer">{{ __('front/common.confirm') }}</button>
  </div>
</div>

<script type="text/javascript">
  new QRCode(document.getElementById("erc-20"), "{{ plugin_setting('upay.address_erc20') }}");
  new QRCode(document.getElementById("trc-20"), "{{ plugin_setting('upay.address_trc20') }}");
  new QRCode(document.getElementById("bep-20"), "{{ plugin_setting('upay.address_bep20') }}");
</script>