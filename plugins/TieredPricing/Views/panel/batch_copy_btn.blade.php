<th>
    <div class="batch-input-item mb-2">
        <div class="input-group input-group-sm">
            <button class="btn btn-outline-primary" type="button" onclick="openBatchTieredPriceSetting()">批量设置批发</button>
        </div>
    </div>
    {{ __('panel/product.quantity') }}
</th>
<!-- 阶梯价格设置弹窗内容 -->
<div id="batchTieredPriceDialog" style="display: none; padding: 20px;">
    <div class="tiered-price-content">
        <div class="price-header">
            <div class="col-label">数量</div>
            <div class="col-label">金额</div>
            <div class="col-action"> <button type="button" class="btn btn-outline-secondary btn-sm" onclick="batchAddTierPrice()">新增</button></div>
        </div>
        <div id="batchPriceRows">
            <!-- 价格行将通过 JavaScript 动态添加 -->
        </div>
    </div>
</div>

@push('footer')
    <script>
        // 当前编辑的 SKU 索引
        let batchAllTieredPricesData = [];

        // 打开弹窗
        function openBatchTieredPriceSetting(obj) {

            // 如果没有数据，添加一行
           // if (batchAllTieredPricesData.length === 0) {
                //batchAddTierPrice();
            //}

            // 打开 layer 弹窗
            layer.open({
                type: 1,
                title: '批量设置阶梯计价',
                area: ['500px', '80%'],
                content: $('#batchTieredPriceDialog'),
                btn: ['确定', '取消'],
                yes: function(index) {
                    batchSaveTieredPrice(index);
                }
            });
        }
        // 渲染价格行
        function renderBatchPriceRows() {
            const container = document.getElementById('batchPriceRows');
            container.innerHTML = '';

            batchAllTieredPricesData.forEach((item, index) => {
                container.innerHTML += createPriceRowHtml(index, item);
            });
        }

        // 创建价格行 HTML
        function batchCreatePriceRowHtml(index, data = { num: 1, price: 0 }) {
            return `
                <div class="price-row" id="price-row-${index}">
                    <div class="col-num">
                        <input type="number" class="form-control form-control-sm" value="${data.num}"
                            min="1" onchange="batchUpdatePrice(${index}, 'num', this.value)">
                    </div>
                    <div class="col-price">
                        <input type="number" class="form-control form-control-sm" value="${data.price}"
                            min="0" step="0.01" onchange="batchUpdatePrice(${index}, 'price', this.value)">
                    </div>
                    <div class="col-action">
                        <button type="button" class="btn btn-outline-danger btn-sm"
                            onclick="batchRemoveTierPrice(${index})">删除</button>
                    </div>
                </div>
            `;
        }

        // 添加新的价格行
        function batchAddTierPrice() {
            const index = batchAllTieredPricesData.length;
            batchAllTieredPricesData.push({num: 1, price: 0 });

            const container = document.getElementById('batchPriceRows');
            container.insertAdjacentHTML('beforeend', batchCreatePriceRowHtml(index));
        }

        // 删除价格行
        function batchRemoveTierPrice(index) {
            batchAllTieredPricesData.splice(index, 1);
            renderBatchPriceRows();
        }

        // 更新价格数据
        function batchUpdatePrice(index, field, value) {
            batchAllTieredPricesData[index][field] = parseFloat(value);
        }

        // 保存阶梯价格
        function batchSaveTieredPrice(layerIndex) {
            if (batchAllTieredPricesData.length === 0) {
                layer.msg('请至少添加一个阶梯价格', {icon: 2});
                return;
            }

            allTieredPricesData = JSON.parse(document.getElementById('allTieredPricesData').value || '[]');
            // 更新到每个sku数据中
            variantsBoxApp.skus.forEach(function (item, index) {
                allTieredPricesData[index] = [];
                batchAllTieredPricesData.forEach(function (item2, index2) {
                    console.log(index)
                    allTieredPricesData[index].push({'sku_index':index,num: item2.num, price: item2.price });
                });
            });

            console.log(allTieredPricesData);
            document.getElementById('allTieredPricesData').value = JSON.stringify(allTieredPricesData);
            // 关闭弹窗
            layer.close(layerIndex);
            layer.msg('已成功复制并覆盖到当前商品的其他sku数据', {icon: 1});

        }
    </script>

    <style>
        .tiered-price-content {
            padding: 20px 0;
        }
        .price-header, .price-row {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
            padding: 0 20px;
        }
        .col-label {
            flex: 1;
            text-align: center;
            font-weight: bold;
        }
        .col-num, .col-price {
            flex: 1;
            padding: 0 5px;
        }
        .col-num input, .col-price input {
            width: 120px;
            margin: 0 auto;
            text-align: center;
        }
        .col-action {
            width: 80px;
            text-align: center;
        }
        .add-row {
            margin-top: 20px;
            text-align: center;
        }
    </style>
@endpush
