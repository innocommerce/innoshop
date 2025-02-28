<!-- 阶梯价格设置弹窗内容 -->
<div id="tieredPriceDialog" style="display: none; padding: 20px;">
    <div class="tiered-price-content">
        <div class="price-header">
            <div class="col-label">数量</div>
            <div class="col-label">金额</div>
            <div class="col-action"> <button type="button" class="btn btn-outline-secondary btn-sm" onclick="addTierPrice()">新增</button></div>
        </div>
        <div id="priceRows">
            <!-- 价格行将通过 JavaScript 动态添加 -->
        </div>
    </div>
</div>

<input type="hidden" name="allTieredPricesData" id="allTieredPricesData" value="{{ json_encode(old('allTieredPricesData', $allTieredPricesData ?? [])) }}">

@push('footer')
    <script>
        // 当前编辑的 SKU 索引
        let currentSkuIndex = 0;
        let allTieredPricesData = JSON.parse(document.getElementById('allTieredPricesData').value || '[]');

        // 打开弹窗
        function openTieredPriceDialog(obj) {
            let skuIndex = obj.getAttribute('data-sku_index');
            //let skuIndex = $(obj).attr('data-sku_index');
            currentSkuIndex = skuIndex;
            console.log("弹出阶梯价格设置"+skuIndex);
            // 确保数据存在
            if (!allTieredPricesData[skuIndex]) {
                allTieredPricesData[skuIndex] = [];
            }
            console.log(allTieredPricesData[skuIndex]);
            // 渲染价格行
            renderPriceRows();

            // 如果没有数据，添加一行
            //if (allTieredPricesData[skuIndex].length === 0) {
                //addTierPrice();
           // }

            // 打开 layer 弹窗
            layer.open({
                type: 1,
                title: '设置阶梯计价',
                area: ['500px', '80%'],
                content: $('#tieredPriceDialog'),
                btn: ['确定', '取消'],
                yes: function(index) {
                    saveTieredPrice(index);
                }
            });
        }

        // 渲染价格行
        function renderPriceRows() {
            const container = document.getElementById('priceRows');
            container.innerHTML = '';

            allTieredPricesData[currentSkuIndex].forEach((item, index) => {
                container.innerHTML += createPriceRowHtml(index, item);
            });
        }

        // 创建价格行 HTML
        function createPriceRowHtml(index, data = { num: 1, price: 0 }) {
            return `
                <div class="price-row" id="price-row-${index}">
                    <div class="col-num">
                        <input type="number" class="form-control form-control-sm" value="${data.num}"
                            min="1" onchange="updatePrice(${index}, 'num', this.value)">
                    </div>
                    <div class="col-price">
                        <input type="number" class="form-control form-control-sm" value="${data.price}"
                            min="0" step="0.01" onchange="updatePrice(${index}, 'price', this.value)">
                    </div>
                    <div class="col-action">
                        <button type="button" class="btn btn-outline-danger btn-sm"
                            onclick="removeTierPrice(${index})">删除</button>
                    </div>
                </div>
            `;
        }

        // 添加新的价格行
        function addTierPrice() {
            const index = allTieredPricesData[currentSkuIndex].length;
            allTieredPricesData[currentSkuIndex].push({'sku_index':currentSkuIndex,num: 1, price: 0 });

            const container = document.getElementById('priceRows');
            container.insertAdjacentHTML('beforeend', createPriceRowHtml(index));
        }

        // 删除价格行
        function removeTierPrice(index) {
            allTieredPricesData[currentSkuIndex].splice(index, 1);
            renderPriceRows();
        }

        // 更新价格数据
        function updatePrice(index, field, value) {
            allTieredPricesData[currentSkuIndex][index][field] = parseFloat(value);
        }

        // 保存阶梯价格
        function saveTieredPrice(layerIndex) {
            //if (allTieredPricesData[currentSkuIndex].length === 0) {
                //layer.msg('请至少添加一个阶梯价格', {icon: 2});
                //return;
            //}

            // 更新隐藏输入框的值
            document.getElementById('allTieredPricesData').value = JSON.stringify(allTieredPricesData);

            // 关闭弹窗
            layer.close(layerIndex);
            layer.msg('保存成功', {icon: 1});

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
