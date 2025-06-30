<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ panel_locale_direction() }}">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="api-token" content="{{ session('panel_api_token') }}">

  <link rel="stylesheet" href="{{ mix('build/panel/css/bootstrap.css') }}">
  <link rel="stylesheet" href="{{ mix('build/panel/css/app.css') }}">
  <script src="{{ asset('vendor/jquery/jquery-3.7.1.min.js') }}"></script>
  <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
  <script src="{{ asset('vendor/layer/3.5.1/layer.js') }}"></script>
  <script src="{{ mix('build/panel/js/app.js') }}"></script>
  <script>
    let selectedProduct = null;
    function renderProducts(products) {
      const $tbody = $('#productTable tbody');
      $tbody.empty();
      products.forEach(product => {
        $tbody.append(`
          <tr class="product-row" 
              data-id="${product.id}"
              data-name="${product.product_name}"
              data-code="${product.code}"
              data-image="${product.image}"
              data-image_url="${product.image_url}"
              data-price="${product.price}"
              data-price_format="${product.price_format}"
              data-origin_price="${product.origin_price}"
              data-origin_price_format="${product.origin_price_format}"
              data-product_id="${product.product_id}"
              data-model="${product.model}"
              data-quantity="${product.quantity}">
            <td style="width:40px;">
              <input type="checkbox" name="product" class="form-check-input" value="${product.id}">
            </td>
            <td style="width:56px;">
              <img src="${product.image_url}" alt="${product.product_name}" class="rounded-3" style="width:48px;height:48px;object-fit:cover;">
            </td>
            <td>
              <div class="fw-bold text-truncate" style="max-width:320px;" title="${product.product_name}">${product.product_name}</div>
              <div class="text-muted text-truncate" style="max-width:320px;">SKU: ${product.code}</div>
            </td>
            <td class="text-end fw-bold" style="color:#e74c3c;min-width:90px;">${product.price_format}</td>
          </tr>
        `);
      });

      // 行点击选中/取消
      $tbody.find('tr').off('click').on('click', function(e) {
        if (!$(e.target).is('input[type=checkbox]')) {
          const $checkbox = $(this).find('input[type=checkbox]');
          $checkbox.prop('checked', !$checkbox.prop('checked')).trigger('change');
        }
      });

      // 选中高亮
      $tbody.find('input[type=checkbox]').on('change', function() {
        const $tr = $(this).closest('tr');
        if ($(this).prop('checked')) {
          $tr.addClass('table-active');
        } else {
          $tr.removeClass('table-active');
        }
      });
    }
    function searchProducts() {
      const keyword = $('#searchInput').val();
      $.get('/api/products/skus', { keyword }, function(res) {
        renderProducts(res.data || []);
      });
    }
    function syncFakeScrollbar() {
      // 设置假滚动条宽度与表格一致
      $('#fake-scrollbar-inner').width($('#productTable').outerWidth());

      // 同步滚动
      $('.table-fake-scrollbar').on('scroll', function() {
        $('.table-inner-scroll').scrollLeft($(this).scrollLeft());
      });
      $('.table-inner-scroll').on('scroll', function() {
        $('.table-fake-scrollbar').scrollLeft($(this).scrollLeft());
      });
    }
    $(function() {
      $('#searchBtn').on('click', searchProducts);
      
      $('#confirmBtn').on('click', function() {
        const selectedProducts = [];
        $('#productTable tbody input[name=product]:checked').each(function() {
          const $tr = $(this).closest('tr');
          selectedProducts.push({
            id: $tr.data('id'),
            name: $tr.data('name'),
            code: $tr.data('code'),
            image: $tr.data('image'),
            image_url: $tr.data('image_url'),
            price: $tr.data('price'),
            price_format: $tr.data('price_format'),
            origin_price: $tr.data('origin_price'),
            origin_price_format: $tr.data('origin_price_format'),
            product_id: $tr.data('product_id'),
            model: $tr.data('model'),
            quantity: $tr.data('quantity'),
          });
        });
        if (selectedProducts.length === 0) {
          layer.msg('{{ __('panel/product.select_product') }}', {icon: 2}); 
          return;
        }
        if (window.parent && window.parent.productSelectorCallback) {
          window.parent.productSelectorCallback(selectedProducts);
        }
        if (window.parent && window.parent.layer) {
          const index = window.parent.layer.getFrameIndex(window.name);
          window.parent.layer.close(index);
        }
      });
      searchProducts();
      syncFakeScrollbar();
    });
  </script>
  @stack('header')
  <style>
    /* 用Bootstrap类名替代大部分自定义样式，仅保留必要的微调 */
    .product-list-container {
      
      overflow-y: auto;
      margin-bottom: 0;
    }
    .product-list {
      list-style: none;
      padding: 0;
      margin: 0;
    }
    .product-list .product-item {
      cursor: pointer;
      padding: 0.5rem 0.5rem;
      border-bottom: 1px solid #dee2e6; /* Bootstrap table border color */
      display: flex;
      align-items: center;
      background: #fff;
      transition: background 0.2s;
    }
    .product-list .product-item.selected {
      background: #eaf4fb;
    }
    .product-list .product-img {
      width: 48px;
      height: 48px;
      object-fit: cover;
      border-radius: 0.5rem; /* Bootstrap rounded-3 */
      margin-right: 0.75rem; /* Bootstrap spacing */
      flex-shrink: 0;
    }
    .product-list .product-info {
      min-width: 0;
      flex: 1 1 0%;
      display: flex;
      flex-direction: column;
      justify-content: center;
    }

    .product-list .product-sku {
      font-size: 0.875rem;
      color: #6c757d; /* Bootstrap text-muted */
      margin-top: 0.25rem;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
      max-width: 220px;
    }
    .product-list .product-price {
      min-width: 80px;
      margin-left: 0.75rem;
      text-align: right;
      color: #e74c3c;
      font-size: 1rem;
      font-weight: bold;
    }
    .table-hover tbody tr:hover {
      background-color: #f5faff;
    }
  </style>
</head>


<body class="@yield('body-class')" >

  <div class="p-4" style="overflow-y: auto;">
    <div class="card mb-3">
  
    <div class="card-body">
      <div class="input-group mb-3">
        <input type="text" id="searchInput" class="form-control" placeholder="{{ __('panel/product.search_placeholder') }}">   
        <button class="btn btn-primary" id="searchBtn">{{ __('panel/common.search') }} </button> 
      </div>
      <div class="table-responsive product-list-container" style="max-height:500px;overflow-y:auto;">
        <table class="table table-hover align-middle mb-0" id="productTable">
          <tbody>
            <!-- JS 动态插入 -->
          </tbody>
        </table>
      </div>
    </div>
    </div>
    <button class="btn btn-success mt-4 w-100" id="confirmBtn" style="position:sticky;bottom:24px;z-index:20;">{{ __('InquiryQuote::quote.confirm') }}</button>
  </div>
  @stack('footer')
</body>


</html>
