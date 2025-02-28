<div
    style="margin-left: 5px"
    class="img-upload-item wh-80 img-link-trigger d-flex overflow-hidden justify-content-center rounded align-items-center border border-1 mb-1">
    <div class="img-info d-flex justify-content-center align-items-center wh-80 bg-white cursor-pointer">
        <i class="bi bi-link-45deg fs-3 text-secondary"></i>
    </div>
</div>
<script>
    $('.img-link-trigger').click(function () {
        layer.open({
            type: 2,
            title: "{{__('ProductLinkImg::common.iframe_title')}}",
            shadeClose: true,
            shade: 0.3,
            area: ['60%', '80%'],
            content: "{{panel_route('product_link_img.index')}}"
        });
    });
</script>
