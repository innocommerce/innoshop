<div class="tiered-price-display">
    <div class="tiered-price-table">
        <div class="tiered-price-row header">
            <!-- 动态生成表头 -->
        </div>
        <div class="tiered-price-row values">
            <!-- 动态生成价格值 -->
        </div>
    </div>
</div>
<div class="product-price">
    <span class="price2" id="price_format">0</span>
    <span class="old-price2 ms-2" id="origin_price_format">0</span>
    @hookinsert('front.product.detail.origin_price.after')
</div>
<style>
    .tiered-price-display {
        margin: 10px 0;
        font-size: 14px;
    }

    .tiered-price-table {
        border: 1px solid #e8e8e8;
        border-radius: 4px;
        overflow: hidden;
    }

    .tiered-price-row {
        display: flex;
        border-bottom: 1px solid #e8e8e8;
    }

    .tiered-price-row:last-child {
        border-bottom: none;
    }

    .tiered-price-cell {
        flex: 1;
        padding: 10px 15px;
        text-align: center;
        border-right: 1px solid #e8e8e8;
    }

    .tiered-price-cell:last-child {
        border-right: none;
    }

    .header .tiered-price-cell {
        background-color: #f8f9fa;
        font-weight: normal;
        color: #666;
    }

    .values .tiered-price-cell {
        color: #ff4400;
        font-weight: bold;
    }

    .price2 {
        font-size: 26px;
        color: #e53e3e;
        font-weight: 700;
    }

    .old-price2 {
        font-size: 16px;
        color: #777;
        text-decoration: line-through
    }


</style>

@push('footer')
    <script>
        let tieredsSku = null;
        let masterSkuTieredPrice = @json($sku);
        let init_min_quantity = @json($init_min_quantity);
        $(function () {
            initData(masterSkuTieredPrice.variants[0], masterSkuTieredPrice.variants[1]);
        })

        $('.product-variant-box .variant-value-name').click(function () {
            const variant = $(this).data('variant');
            const value = $(this).data('value');

            console.log(variant,value)
            initData(variant, value);
        });
        $('.product-variant-box .variant-value-select').change(function () {
            const variant = $(this).data('variant');
            const value = $(this).val();
            console.log(variant,value)
            initData(variant, value);
        });
        console.log("product_variable")

        $('.product-quantity').on('input', function () {
            setTimeout(function () {
                updatePrice();
            })
        });

        $('.quantity-wrap .plus, .quantity-wrap .minus').on('click', function () {

            setTimeout(function () {
                updatePrice();
            })
        });

        function initData(variant, value) {
            let variants = masterSkuTieredPrice.variants.slice(0);
            variants[variant] = value;
            masterSkuTieredPrice = skus.find(sku => sku.variants.toString() === variants.toString());

            console.log("============");
            if (masterSkuTieredPrice) {
                tiereds = masterSkuTieredPrice.tiereds;
            } else {
                tiereds = [];
            }
            console.log(tiereds);

            const $header = $('.tiered-price-row.header');
            const $values = $('.tiered-price-row.values');

            // 清空现有内容
            $header.empty();
            $values.empty();

            if (tiereds.length == 0) {
                $(".tiered-price-display").hide();
            } else {
                $(".tiered-price-display").show();
            }
            // 生成表头和价格
            tiereds.forEach(function (tier) {
                // 添加表头
                $header.append(
                    $('<div>', {
                        class: 'tiered-price-cell',
                        text: '>= ' + tier.num + ' Qty'
                    })
                );
                // 添加价格
                $values.append(
                    $('<div>', {
                        class: 'tiered-price-cell',
                        text: '$' + parseFloat(tier.price).toFixed(2)
                    })
                );
            });
            updatePrice();
        }

        function updatePrice() {
            if (tiereds.length == 0) {
                console.log("default=====")
                $("#price_format").text(masterSkuTieredPrice.price_format)
                if (masterSkuTieredPrice.price_format != masterSkuTieredPrice.origin_price_format) {
                    $("#origin_price_format").show();
                    $("#origin_price_format").text(masterSkuTieredPrice.origin_price_format)
                } else {
                    $("#origin_price_format").hide();
                }
            } else {
                const quantity = $('.product-quantity').val();

                let minQuantityTiereds = null;
                if (init_min_quantity == 1) {//有起拍量要求
                    minQuantityTiereds = tiereds[0];
                    if (quantity < minQuantityTiereds.num) {
                        $('.product-quantity').val(minQuantityTiereds.num);
                        setTimeout(function () {
                            updatePrice();
                        })
                        return;
                    }
                }


                for (let i = 0; i < tiereds.length; i++) {
                    if (minQuantityTiereds == null && quantity >= tiereds[i].num) {
                        minQuantityTiereds = tiereds[i];
                    } else if (minQuantityTiereds != null && minQuantityTiereds.num < tiereds[i].num && quantity >= tiereds[i].num) {
                        minQuantityTiereds = tiereds[i];
                    }
                }

                if (minQuantityTiereds != null) {
                    $("#price_format").text(minQuantityTiereds.price_format)
                    if (masterSkuTieredPrice.price_format != minQuantityTiereds.price_format) {
                        $("#origin_price_format").show();
                        $("#origin_price_format").text(masterSkuTieredPrice.price_format)
                    } else {
                        $("#origin_price_format").hide();
                    }
                } else {
                    $("#price_format").text(masterSkuTieredPrice.price_format)
                    if (masterSkuTieredPrice.price_format != masterSkuTieredPrice.origin_price_format) {
                        $("#origin_price_format").show();
                        $("#origin_price_format").text(masterSkuTieredPrice.origin_price_format)
                    } else {
                        $("#origin_price_format").hide();
                    }
                }
            }
        }
    </script>
@endpush
