@foreach($variants as $key => $variant)
    <div class="product-variant">
        <div class="variant-title">{{ $variant['name'][front_locale_code()] ?? '-' }}</div>
        <div class="variant-values">
            @if(!isset($variant['show_type']) || $variant['show_type'] == 1)
                @foreach($variant['values'] as $vk => $value)
                    <div class="variant-value-name" data-variant="{{ $key }}"
                         data-value="{{ $vk }}">{{ $value['name'][front_locale_code()] ?? '-' }}</div>
                @endforeach
            @else
                <select class="form-select variant-value-select" data-variant="{{ $key }}">
                    @foreach($variant['values'] as $vk => $value)
                        <option value="{{ $vk }}" data-variant="{{ $key }}" data-value="{{ $vk }}">
                            {{ $value['name'][front_locale_code()] ?? '-' }}
                        </option>
                    @endforeach
                </select>
            @endif
        </div>
    </div>
@endforeach

<!-- 自定义sku -->
@if(isset($custom_skus) && count($custom_skus))
    @foreach($custom_skus as $custom_sku)
        <div class="product-variant">
            <div class="variant-title">{{ $custom_sku->translation->name }}</div>
            <div class="variant-values">
                <input type="text"
                       class="form-control custom-params"
                       data-key="{{$custom_sku->translation->name}}"
                       name="custom_sku[{{ $custom_sku->id }}]"
                       data-custom-sku-id="{{ $custom_sku->id }}"/>
            </div>
        </div>
    @endforeach
@endif
@push('footer')
    <script>

        if ($('.product-variant-box').length) {
            let masterSku = @json($sku);
            $('.product-variant-box .variant-value-select').change(function () {
                console.log("product_variable")
                const variant = $(this).data('variant');
                const value = $(this).val();
                let variants = masterSku.variants.slice(0);
                variants[variant] = value;
                masterSku = skus.find(sku => sku.variants.toString() === variants.toString());

                $('.product-param .sku .value').text(masterSku.code);
                $('.product-param .model .value').text(masterSku.model);
                $('.product-price .price').text(masterSku.price_format);
                $('.product-price .old-price').text(masterSku.origin_price_format);
                $('.product-quantity').data('sku-id', masterSku.id)
                $('.main-product-img img').attr('src', masterSku.origin_image_url);
                history.pushState({}, '', inno.updateQueryStringParameter(window.location.href, 'sku_id', masterSku.id));

                if (masterSku.quantity * 1 === 0) {
                    $('.product-info-bottom button, .product-info-bottom .quantity-wrap').addClass('disabled');
                    $('.stock-wrap .in-stock').addClass('d-none').siblings('.out-stock').removeClass('d-none');
                } else {
                    $('.product-info-bottom button, .product-info-bottom .quantity-wrap').removeClass('disabled');
                    $('.stock-wrap .in-stock').removeClass('d-none').siblings('.out-stock').addClass('d-none');
                }

                $(this).addClass('active').siblings().removeClass('active');
                updateVariantStatus()
            });
        }
        $('.add-cart, .buy-now').on('click', function () {
            const skuId = $('.product-quantity').data('sku-id');
            const customParams = $('.custom-params');
            const customData = {};
            for (let i = 0; i < customParams.length; i++) {
                if (customParams[i].value !== '') {
                    customData[customParams[i].dataset.key] = customParams[i].value;
                }
            }
            let productId = "{{$product->id}}";
            axios.post("{{front_route('other_sku.store')}}", {'product_id': productId,"sku_id":skuId,"custom_sku":customData}).then((res) => {

            })
            //TODO 异步更新到临时表
        });


    </script>
@endpush
