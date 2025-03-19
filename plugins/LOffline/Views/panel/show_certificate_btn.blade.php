<a class="btn btn-primary ms-2 pay-certificate-trigger">
  {{__('LOffline::common.pay_img')}}
</a>
@pushonce('footer')
<script>
  $(function () {

  $('.pay-certificate-trigger').click(function () {
    let url = "{{panel_route('l_offline.pay_certificate')}}?order_id={{$order_id}}";
    layer.open({
      type: 2,
      title: "{{__('LOffline::common.pay_img')}}",
      shadeClose: true,
      shade: 0.3,
      area: ['60%', '80%'],
      content: url
    });
  });
  });
</script>
@endpushonce
