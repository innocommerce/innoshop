@if($productOptions && $productOptions->count() > 0)
  <div class="product-options">
    <h6 class="options-title">{{ __('front/product.custom_options') }}</h6>
    
    @foreach($productOptions as $productOption)
      @php
        $option = $productOption->option;
        $productOptionValues = $product->productOptionValues
          ->where('option_id', $option->id);
        $optionType = $option->type ?? 'select';
        $isRequired = $option->required ?? false;
      @endphp
      
      @if($option && $productOptionValues->count() > 0)
        <div class="option-group mb-3" data-option-id="{{ $option->id }}" data-option-type="{{ $optionType }}" data-required="{{ $isRequired ? 'true' : 'false' }}">
          <label class="option-label">
            {{ $option->currentName }}
            @if($isRequired)
              <span class="text-danger">*</span>
            @endif
          </label>
          
          @if($optionType === 'select')
            {{-- 下拉选择框 --}}
            <select class="form-select option-select mt-2" 
                    name="option_{{ $option->id }}" 
                    data-option-id="{{ $option->id }}">
              <option value="">{{ __('front/common.please_choose') }}</option>
              @foreach($productOptionValues as $productOptionValue)
                @php
                  $optionValue = $productOptionValue->optionValue;
                  $priceAdjustment = $productOptionValue->price_adjustment ?? 0;
                  $quantity = $productOptionValue->quantity ?? 0;
                  $isOutOfStock = $quantity <= 0;
                @endphp
                <option value="{{ $optionValue->id }}" 
                        data-price-adjustment="{{ $priceAdjustment }}"
                        data-quantity="{{ $quantity }}"
                        {{ $isOutOfStock ? 'disabled' : '' }}>
                  {{ $optionValue->currentName }}
                  @if($priceAdjustment != 0)
                    ({{ $priceAdjustment > 0 ? '+' : '' }}{{ currency_format($priceAdjustment) }})
                  @endif
                  @if($isOutOfStock)
                    - {{ __('front/product.out_stock') }}
                  @endif
                </option>
              @endforeach
            </select>
            
          @elseif($optionType === 'radio')
            {{-- 单选按钮 --}}
            <div class="option-values radio-group mt-2 d-flex flex-wrap gap-2">
              @foreach($productOptionValues as $productOptionValue)
                @php
                  $optionValue = $productOptionValue->optionValue;
                  $priceAdjustment = $productOptionValue->price_adjustment ?? 0;
                  $quantity = $productOptionValue->quantity ?? 0;
                  $isOutOfStock = $quantity <= 0;
                @endphp
                <div class="form-check option-radio-item mobile-option-item {{ $isOutOfStock ? 'out-of-stock' : '' }}">
                  @if($optionValue->image)
                    <div class="option-image mb-1">
                      <img src="{{ image_resize($optionValue->image) }}" alt="{{ $optionValue->currentName }}" class="img-thumbnail">
                    </div>
                  @endif
                  
                  <input class="form-check-input" 
                         type="radio" 
                         name="option_{{ $option->id }}" 
                         id="option_{{ $option->id }}_{{ $optionValue->id }}"
                         value="{{ $optionValue->id }}"
                         data-option-id="{{ $option->id }}"
                         data-price-adjustment="{{ $priceAdjustment }}"
                         data-quantity="{{ $quantity }}"
                         {{ $isOutOfStock ? 'disabled' : '' }}>
                  <label class="form-check-label mobile-option-label" for="option_{{ $option->id }}_{{ $optionValue->id }}">
                    <span class="option-name">{{ $optionValue->currentName }}</span>
                    @if($priceAdjustment != 0)
                      <span class="price-adjustment d-block">
                        ({{ $priceAdjustment > 0 ? '+' : '' }}{{ currency_format($priceAdjustment) }})
                      </span>
                    @endif
                    @if($isOutOfStock)
                      <span class="out-of-stock-text d-block">{{ __('front/product.out_stock') }}</span>
                    @endif
                  </label>
                </div>
              @endforeach
            </div>
            
          @elseif($optionType === 'checkbox')
            {{-- 多选复选框 --}}
            <div class="option-values checkbox-group mt-2 d-flex flex-wrap gap-2">
              @foreach($productOptionValues as $productOptionValue)
                @php
                  $optionValue = $productOptionValue->optionValue;
                  $priceAdjustment = $productOptionValue->price_adjustment ?? 0;
                  $quantity = $productOptionValue->quantity ?? 0;
                  $isOutOfStock = $quantity <= 0;
                @endphp
                <div class="form-check option-checkbox-item mobile-option-item {{ $isOutOfStock ? 'out-of-stock' : '' }}">
                  @if($optionValue->image)
                    <div class="option-image mb-1">
                      <img src="{{ image_resize($optionValue->image) }}" alt="{{ $optionValue->currentName }}" class="img-thumbnail">
                    </div>
                  @endif
                  
                  <input class="form-check-input" 
                         type="checkbox" 
                         name="option_{{ $option->id }}[]" 
                         id="option_{{ $option->id }}_{{ $optionValue->id }}"
                         value="{{ $optionValue->id }}"
                         data-option-id="{{ $option->id }}"
                         data-price-adjustment="{{ $priceAdjustment }}"
                         data-quantity="{{ $quantity }}"
                         {{ $isOutOfStock ? 'disabled' : '' }}>
                  <label class="form-check-label mobile-option-label" for="option_{{ $option->id }}_{{ $optionValue->id }}">
                    <span class="option-name">{{ $optionValue->currentName }}</span>
                    @if($priceAdjustment != 0)
                      <span class="price-adjustment d-block">
                        ({{ $priceAdjustment > 0 ? '+' : '' }}{{ currency_format($priceAdjustment) }})
                      </span>
                    @endif
                    @if($isOutOfStock)
                      <span class="out-of-stock-text d-block">{{ __('front/product.out_stock') }}</span>
                    @endif
                  </label>
                </div>
              @endforeach
            </div>
          @endif
          
          {{-- 选项描述放在选项值列表下方 --}}
          @if($option->description && is_array($option->description))
            <div class="option-description mt-3">
              <small class="text-muted">
                {{ $option->description[app()->getLocale()] ?? $option->description['en'] ?? '' }}
              </small>
            </div>
          @endif
        </div>
      @endif
    @endforeach
    
    <!-- 当前选择和总价显示区域 -->
    <div class="current-selection-summary mb-4" style="display: none;">
      <div class="card">
        <div class="card-body p-3">
          <h6 class="card-title mb-2">
            <i class="fas fa-check-circle text-success me-2"></i>{{ __('front/product.current_selection') }}
          </h6>
          <div class="selected-options-list mb-3">
            <!-- 动态显示选中的选项 -->
          </div>
          <div class="total-price-display">
            <div class="row align-items-center">
              <div class="col">
                <strong class="text-primary">{{ __('front/product.total_price') }}：</strong>
              </div>
              <div class="col-auto">
                <span class="badge bg-primary fs-6 current-total-price">
                  {{ currency_format($product->price) }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>



  @push('footer')
  <script>
    $(document).ready(function() {
      // 获取基础价格 - 优先从当前SKU获取，否则使用产品默认价格
      let basePrice = {{ $sku['price'] ?? 0 }};
      
      // 全局函数，用于更新基础价格（规格切换时调用）
      window.updateBasePrice = function(newPrice) {
        basePrice = parseFloat(newPrice);
        updateProductPrice(); // 重新计算总价
      };
      
      // 存储选中的选项
      let selectedOptions = {};
      
      // 全局验证函数，供外部调用
      window.validateRequiredOptions = function() {
        return validateRequiredOptions();
      };
      
      // 下拉选择框事件
      $('.option-select').change(function() {
        const $this = $(this);
        const optionId = $this.data('option-id');
        const selectedValue = $this.val();
        const priceAdjustment = parseFloat($this.find('option:selected').data('price-adjustment')) || 0;
        
        if (selectedValue) {
          selectedOptions[optionId] = [selectedValue];
        } else {
          delete selectedOptions[optionId];
        }
        
        updateProductPrice();
        validateRequiredOptions();
      });
      
      // 单选按钮事件 - 支持点击整个选项区域和标签文字
      $('.option-radio-item, .option-radio-item label').on('click', function(e) {
        e.preventDefault(); // 阻止默认的label点击行为
        e.stopPropagation(); // 阻止事件冒泡
        
        const $item = $(this).hasClass('option-radio-item') ? $(this) : $(this).closest('.option-radio-item');
        const $input = $item.find('input[type="radio"]');
        
        // 检查是否禁用（缺货）
        if ($input.prop('disabled') || $item.hasClass('out-of-stock')) {
          return false; // 如果禁用则不执行任何操作
        }
        
        const optionId = $input.data('option-id');
        const optionValue = $input.val();
        const priceAdjustment = parseFloat($input.data('price-adjustment')) || 0;
        
        // 取消同组其他选项的选中状态
        $item.siblings('.option-radio-item').removeClass('selected');
        $item.addClass('selected');
        
        // 设置单选框选中
        $input.prop('checked', true);
        
        selectedOptions[optionId] = [optionValue];
        
        updateProductPrice();
        validateRequiredOptions();
      });
      
      // 多选复选框事件 - 支持点击整个选项区域和标签文字
      $('.option-checkbox-item, .option-checkbox-item label').on('click', function(e) {
        e.preventDefault(); // 阻止默认的label点击行为
        e.stopPropagation(); // 阻止事件冒泡
        
        const $item = $(this).hasClass('option-checkbox-item') ? $(this) : $(this).closest('.option-checkbox-item');
        const $input = $item.find('input[type="checkbox"]');
        
        // 检查是否禁用（缺货）
        if ($input.prop('disabled') || $item.hasClass('out-of-stock')) {
          return false; // 如果禁用则不执行任何操作
        }
        
        const optionId = $input.data('option-id');
        const optionValue = $input.val();
        const priceAdjustment = parseFloat($input.data('price-adjustment')) || 0;
        
        // 切换选中状态
        if ($item.hasClass('selected')) {
          $item.removeClass('selected');
          $input.prop('checked', false);
          
          // 从选中选项中移除
          if (selectedOptions[optionId]) {
            selectedOptions[optionId] = selectedOptions[optionId].filter(id => id !== optionValue);
            if (selectedOptions[optionId].length === 0) {
              delete selectedOptions[optionId];
            }
          }
        } else {
          $item.addClass('selected');
          $input.prop('checked', true);
          
          // 添加到选中选项
          if (!selectedOptions[optionId]) {
            selectedOptions[optionId] = [];
          }
          selectedOptions[optionId].push(optionValue);
        }
        
        updateProductPrice();
        validateRequiredOptions();
      });
      
      // 更新产品价格和选择显示
      function updateProductPrice() {
        let totalAdjustment = 0;
        
        // 计算下拉选择框的价格调整
        $('.option-select').each(function() {
          const selectedOption = $(this).find('option:selected');
          if (selectedOption.val()) {
            totalAdjustment += parseFloat(selectedOption.data('price-adjustment')) || 0;
          }
        });
        
        // 计算单选按钮的价格调整
        $('.option-radio-item input[type="radio"]:checked').each(function() {
          totalAdjustment += parseFloat($(this).data('price-adjustment')) || 0;
        });
        
        // 计算多选复选框的价格调整
        $('.option-checkbox-item input[type="checkbox"]:checked').each(function() {
          totalAdjustment += parseFloat($(this).data('price-adjustment')) || 0;
        });
        
        const finalPrice = basePrice + totalAdjustment;
        
        // 使用全局货币格式化函数
        const formattedPrice = window.inno.formatCurrency(finalPrice);
        $('.product-price .price').text(formattedPrice);
        $('.current-total-price').text(formattedPrice);
        
        // 更新当前选择显示
        updateCurrentSelectionDisplay();
      }
      
      // 更新当前选择显示
      function updateCurrentSelectionDisplay() {
        const $selectionList = $('.selected-options-list');
        const $summaryCard = $('.current-selection-summary');
        
        $selectionList.empty();
        let hasSelections = false;
        
        // 显示下拉选择框的选择
        $('.option-select').each(function() {
          const $select = $(this);
          const selectedOption = $select.find('option:selected');
          const optionName = $select.closest('.option-group').find('.option-label').text().trim().replace('*', '');
          
          if (selectedOption.val()) {
            hasSelections = true;
            const priceAdjustment = parseFloat(selectedOption.data('price-adjustment')) || 0;
            const priceText = priceAdjustment !== 0 ? 
              ` (${priceAdjustment > 0 ? '+' : ''}${window.inno.formatCurrency(priceAdjustment)})` : '';
            
            $selectionList.append(`
              <div class="selected-option-item mb-2">
                <span class="badge bg-light text-dark me-2">${optionName}</span>
                <span class="option-value">${selectedOption.text().split('(')[0].trim()}${priceText}</span>
              </div>
            `);
          }
        });
        
        // 显示单选按钮的选择
        $('.option-radio-item input[type="radio"]:checked').each(function() {
          const $input = $(this);
          const optionName = $input.closest('.option-group').find('.option-label').text().trim().replace('*', '');
          const optionValueName = $input.closest('.option-radio-item').find('.option-name').text() || 
                                  $input.closest('.option-radio-item').find('label').text();
          const priceAdjustment = parseFloat($input.data('price-adjustment')) || 0;
          const priceText = priceAdjustment !== 0 ? 
            ` (${priceAdjustment > 0 ? '+' : ''}${window.inno.formatCurrency(priceAdjustment)})` : '';

          hasSelections = true;
          $selectionList.append(`
            <div class="selected-option-item mb-2">
              <span class="badge bg-light text-dark me-2">${optionName}</span>
              <span class="option-value">${optionValueName}${priceText}</span>
            </div>
          `);
        });
        
        // 显示多选复选框的选择
        $('.option-checkbox-item input[type="checkbox"]:checked').each(function() {
          const $input = $(this);
          const optionName = $input.closest('.option-group').find('.option-label').text().trim().replace('*', '');
          const optionValueName = $input.closest('.option-checkbox-item').find('.option-name').text() || 
                                  $input.closest('.option-checkbox-item').find('label').text();
          const priceAdjustment = parseFloat($input.data('price-adjustment')) || 0;
          const priceText = priceAdjustment !== 0 ? 
            ` (${priceAdjustment > 0 ? '+' : ''}${window.inno.formatCurrency(priceAdjustment)})` : '';
          
          hasSelections = true;
          $selectionList.append(`
            <div class="selected-option-item mb-2">
              <span class="badge bg-light text-dark me-2">${optionName}</span>
              <span class="option-value">${optionValueName}${priceText}</span>
            </div>
          `);
        });
        
        // 显示或隐藏选择摘要卡片
        if (hasSelections) {
          $summaryCard.show();
        } else {
          $summaryCard.hide();
        }
      }
      
      // 验证必选项
      function validateRequiredOptions() {
        let allValid = true;
        let hasRequiredOptions = false;
        let missingOptions = [];
        
        $('.option-group').each(function() {
          const $group = $(this);
          const optionId = $group.data('option-id');
          const optionType = $group.data('option-type');
          const isRequired = $group.data('required');
          const optionName = $group.find('.option-label').text().trim().replace('*', '');
          
          // 移除之前的错误消息
          $group.find('.option-error-message').remove();
          
          if (isRequired) {
            hasRequiredOptions = true;
            let hasSelection = false;
            
            if (optionType === 'select') {
              const selectValue = $group.find('.option-select').val();
              hasSelection = selectValue !== '';
            } else if (optionType === 'radio') {
              const checkedRadios = $group.find('input[type="radio"]:checked');
              hasSelection = checkedRadios.length > 0;
            } else if (optionType === 'checkbox') {
              const checkedBoxes = $group.find('input[type="checkbox"]:checked');
              hasSelection = checkedBoxes.length > 0;
            }
            
            if (!hasSelection) {
              allValid = false;
              $group.addClass('has-error');
              missingOptions.push(optionName);
              
              // 添加错误提示消息
              const errorMessage = `<div class="option-error-message">
                <i class="bi bi-exclamation-circle"></i>
                请选择 ${optionName}
              </div>`;
              $group.append(errorMessage);
            } else {
              $group.removeClass('has-error');
            }
          } else {
            // 非必选项移除错误状态
            $group.removeClass('has-error');
          }
        });
        
        // 如果没有必选项，则总是返回true
        if (!hasRequiredOptions) {
          allValid = true;
        }
        
        // 更新购买按钮状态和提示
        if (allValid) {
          $('.add-cart, .buy-now').removeClass('disabled').attr('title', '');
        } else {
          $('.add-cart, .buy-now').addClass('disabled').attr('title', `请先选择：${missingOptions.join('、')}`);
        }
        
        return allValid;
      }
      
      // 注意：加入购物车的事件处理已在show.blade.php中定义，这里不再重复定义
    });
  </script>
  
  <style>
    /* 移动端选项优化样式 */
    .mobile-option-item {
      width: 120px;
      min-height: 60px;
      border: 1px solid #dee2e6;
      border-radius: 8px;
      padding: 8px;
      margin: 0;
      position: relative;
      cursor: pointer;
      transition: all 0.2s ease;
      background: #fff;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      text-align: center;
      flex: 0 0 auto; /* 防止flex项目收缩 */
    }
    
    .mobile-option-item:hover {
      border-color: #007bff;
      box-shadow: 0 2px 4px rgba(0,123,255,0.1);
    }
    
    .mobile-option-item.selected {
      border-color: #007bff;
      background-color: #f8f9ff;
      box-shadow: 0 2px 8px rgba(0,123,255,0.2);
    }
    
    .mobile-option-item.out-of-stock {
      opacity: 0.5;
      cursor: not-allowed;
      background-color: #f8f9fa;
    }
    
    .mobile-option-item .form-check-input {
      position: absolute;
      top: 6px;
      right: 6px;
      margin: 0;
    }
    
    .mobile-option-label {
      width: 100%;
      margin: 0;
      padding: 0;
      cursor: pointer;
      font-size: 12px;
      line-height: 1.2;
    }
    
    .mobile-option-label .option-name {
      font-weight: 500;
      color: #333;
      display: block;
      margin-bottom: 2px;
      word-break: break-word;
    }
    
    .mobile-option-label .price-adjustment {
      font-size: 10px;
      color: #007bff;
      font-weight: 600;
    }
    
    .mobile-option-label .out-of-stock-text {
      font-size: 10px;
      color: #dc3545;
      font-weight: 500;
    }
    
    .mobile-option-item .option-image {
      width: 100%;
      margin-bottom: 4px;
    }
    
    .mobile-option-item .option-image img {
      width: 100%;
      height: 40px;
      object-fit: cover;
      border-radius: 4px;
    }
    
    /* 响应式调整 */
     @media (max-width: 576px) {
       .radio-group, .checkbox-group {
         gap: 6px !important;
         display: flex !important;
         flex-wrap: wrap !important;
       }
       
       .mobile-option-item {
         width: calc(33.333% - 4px) !important;
         min-width: 100px !important;
         font-size: 11px;
         padding: 6px;
         min-height: 50px;
         flex: 0 0 auto !important;
       }
       
       .mobile-option-label {
         font-size: 11px;
       }
       
       .mobile-option-label .price-adjustment {
         font-size: 9px;
       }
       
       .mobile-option-label .out-of-stock-text {
         font-size: 9px;
       }
       
       .mobile-option-item .option-image img {
         height: 30px;
       }
     }
    
    @media (min-width: 577px) and (max-width: 768px) {
      .mobile-option-item {
        width: calc(33.333% - 6px);
        min-width: 120px;
      }
    }
    
    @media (min-width: 769px) {
      .mobile-option-item {
        width: auto;
        min-width: 120px;
        max-width: 140px;
      }
    }
    
    /* 错误状态样式 */
    .option-group.has-error .mobile-option-item {
      border-color: #dc3545;
    }
    
    .option-error-message {
      color: #dc3545;
      font-size: 12px;
      margin-top: 8px;
      padding: 4px 8px;
      background-color: #f8d7da;
      border: 1px solid #f5c6cb;
      border-radius: 4px;
    }
    
    .option-error-message i {
      margin-right: 4px;
    }
  </style>
  @endpush
@endif