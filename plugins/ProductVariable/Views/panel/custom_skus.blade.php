@push('header')
    <style>
        .custom-sku-box .v-locales-input {
            margin-bottom: 10px;
        }

        .custom-sku-box .v-locales-input .input-group {
            margin-bottom: 8px;
        }

        .custom-sku-box .v-locales-input .input-group-text {
            width: 100px;
        }

        .custom-sku-box .v-locales-input .input-group-text img {
            width: 16px;
            margin-right: 5px;
        }

        .custom-sku-box .sku-item {
            position: relative;
            padding: 15px;
            padding-right: 50px;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            margin-bottom: 15px;
        }

        .custom-sku-box .sku-item .delete-btn {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #dc3545;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 4px;
            transition: all 0.2s;
            z-index: 100;
            background: #fff;
        }

        .custom-sku-box .sku-item .delete-btn:hover {
            background-color: #dc3545;
            color: #fff;
        }

        .custom-sku-box .sku-item .delete-btn i {
            font-size: 18px;
        }

        .custom-sku-box .add-sku-btn {
            color: #0d6efd;
            cursor: pointer;
        }

        .custom-sku-box .empty-text {
            color: #6c757d;
            text-align: center;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 4px;
            margin-bottom: 15px;
        }
    </style>
@endpush

<div class="card custom-sku-box mb-3">
    <div class="card-header">
        <h5 class="card-title mb-0">{{ __('ProductVariable::panel.custom_skus') }}</h5>
    </div>

    <div class="card-body">
        <div class="sku-form" id="custom-sku-app">
            <input type="hidden" name="custom_skus" :value="JSON.stringify(custom_skus)">
            <div v-if="!custom_skus.length" class="empty-text">
                {{ __('ProductVariable::panel.sku_name_help') }}
            </div>
            <div v-else class="sku-list">
                <div v-for="(sku, index) in custom_skus" :key="index" class="sku-item">
                    <div class="delete-btn" @click="removeSku(index)">
                        <i class="bi bi-x-lg"></i>
                    </div>
                    <div class="v-locales-input">
                        <div v-for="locale in locales" class="input-group" :key="locale.code">
                            <span class="input-group-text">
                                <img :src="'/images/flag/'+ locale.code +'.png'" class="img-fluid">
                                @{{ locale.name }}
                            </span>
                            <input type="text" 
                                   class="form-control" 
                                   v-model="sku.name[locale.code]" 
                                   :placeholder="'{{ __('ProductVariable::panel.sku_name_help') }}'">
                        </div>
                        <span class="text-12 text-danger" style="margin-left: 100px" v-if="sku.error">
                            <i class="bi bi-exclamation-circle"></i> {{ __('ProductVariable::panel.sku_name_required') }}
                        </span>
                    </div>
                </div>
            </div>
            <div class="add-sku-btn mt-3" @click="addSku">
                <i class="bi bi-plus-lg"></i> {{ __('ProductVariable::panel.add_sku') }}
            </div>
        </div>
    </div>
</div>

@push('footer')
    <script>
        // 等待主Vue应用创建完成后再创建我们的组件
        document.addEventListener('DOMContentLoaded', function() {
            const customSkuApp = {
                setup() {
                    const custom_skus = ref([]);
                    const locales = ref(@json(locales()));

                    // 创建一个新的空SKU对象
                    const createEmptySku = () => {
                        const sku = {
                            name: {},
                            error: false
                        };
                        locales.value.forEach(item => {
                            sku.name[item.code] = '';
                        });
                        return sku;
                    };

                    // 添加一个新的SKU
                    const addSku = () => {
                        custom_skus.value.push(createEmptySku());
                    };

                    // 删除一个SKU
                    const removeSku = (index) => {
                        custom_skus.value.splice(index, 1);
                    };

                    // 如果已有数据，加载现有数据
                    @if (isset($custom_skus))
                        custom_skus.value = @json($custom_skus);
                    @endif

                    // 验证所有SKU
                    const validateSkus = () => {
                        let valid = true;
                        custom_skus.value.forEach(sku => {
                            let skuValid = true;
                            locales.value.forEach(item => {
                                if (!sku.name[item.code]) {
                                    skuValid = false;
                                }
                            });
                            sku.error = !skuValid;
                            if (!skuValid) valid = false;
                        });
                        return valid;
                    };

                    onMounted(() => {
                        // 监听主表单的提交事件
                        $('#product-form').on('submit', function(e) {
                            if (!validateSkus()) {
                                e.preventDefault();
                                layer.msg('{{ __('ProductVariable::panel.sku_name_required') }}', {icon: 2});
                                return false;
                            }
                        });
                    });

                    return {
                        custom_skus,
                        locales,
                        addSku,
                        removeSku
                    };
                }
            };

            // 使用主应用的 createApp 来创建我们的组件
            const app = Vue.createApp(customSkuApp);
            app.mount('#custom-sku-app');
        });
    </script>
@endpush