<div class="tab-pane fade" id="tab-setting-hot-products">
  <div class="mb-4">
    <h5 class="mb-3">{{ __('panel/setting.hot_products') }}</h5>
    <p class="text-muted small mb-3">{{ __('panel/setting.hot_products_desc') }}</p>

    {{-- Display settings --}}
    <div class="card mb-3">
      <div class="card-body">
        <div class="row">
          <div class="col-md-6">
            <label class="form-label fw-medium">{{ __('panel/setting.hot_products_display_mode') }}</label>
            <select class="form-select" name="hp_display_mode">
              <option value="tab" {{ ($hp_display_mode ?? 'flat') === 'tab' ? 'selected' : '' }}>{{ __('panel/setting.hot_products_display_tab') }}</option>
              <option value="flat" {{ ($hp_display_mode ?? 'flat') === 'flat' ? 'selected' : '' }}>{{ __('panel/setting.hot_products_display_flat') }}</option>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label fw-medium">{{ __('panel/setting.hot_products_title_align') }}</label>
            <select class="form-select" name="hp_title_align">
              <option value="left" {{ ($hp_title_align ?? 'left') === 'left' ? 'selected' : '' }}>{{ __('panel/setting.hot_products_align_left') }}</option>
              <option value="center" {{ ($hp_title_align ?? 'left') === 'center' ? 'selected' : '' }}>{{ __('panel/setting.hot_products_align_center') }}</option>
            </select>
          </div>
        </div>
      </div>
    </div>

    <div class="mb-3">
      <button type="button" class="btn btn-success" id="addFloorBtn">
        <i class="bi bi-plus-circle"></i> {{ __('panel/setting.hot_products_add_floor') }}
      </button>
    </div>

    <div id="floorList">
      @foreach ($product_floors ?? [] as $floorIndex => $floor)
        <div class="card mb-3 floor-card" data-floor-index="{{ $floorIndex }}">
          <div class="card-header d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center flex-grow-1">
              <i class="bi bi-grip-vertical text-muted me-2" style="cursor:move;flex-shrink:0;"></i>
              <strong>{{ __('panel/setting.hot_products') }} #{{ $floorIndex + 1 }}</strong>
            </div>
            <button type="button" class="btn btn-sm btn-danger remove-floor-btn">
              <i class="bi bi-trash"></i> {{ __('panel/setting.hot_products_remove_floor') }}
            </button>
          </div>
          <div class="card-body">
            <div class="mb-3">
              <x-common-form-locale-input
                name="floors[{{ $floorIndex }}][name]"
                nameFormat="flat"
                type="input"
                :translations="$floor['name_translations']"
                :label="__('panel/setting.hot_products_floor_name')"
                :placeholder="__('panel/setting.hot_products_floor_name_placeholder')"
              />
            </div>
            <div class="mb-3">
              <x-common-form-locale-input
                name="floors[{{ $floorIndex }}][subtitle]"
                nameFormat="flat"
                type="input"
                :translations="$floor['subtitle_translations']"
                :label="__('panel/setting.hot_products_subtitle')"
                :placeholder="__('panel/setting.hot_products_subtitle_placeholder')"
              />
            </div>
            <div class="mb-2">
              <button type="button" class="btn btn-sm btn-primary add-product-btn" data-floor-index="{{ $floorIndex }}">
                <i class="bi bi-plus-circle"></i> {{ __('panel/setting.hot_products_add_product') }}
              </button>
            </div>
            <input type="hidden" name="floors_products[{{ $floorIndex }}]"
                   id="floors-products-{{ $floorIndex }}"
                   value="{{ json_encode($floor['products']) }}">
            <ul class="list-group sortable-products sortable-products-{{ $floorIndex }}"></ul>
          </div>
        </div>
      @endforeach
    </div>

    {{-- Vue mount point for product picker dialog --}}
    <div id="product-picker-mount"></div>
  </div>
</div>

@push('footer')
  <script>
    // ========== Hot Products (floors with i18n) ==========
    const langProductsEmpty = @json(__('panel/setting.hot_products_empty'));
    const langFloorDefault = @json(__('panel/setting.hot_products_floor_default'));
    const langFloorNamePlaceholder = @json(__('panel/setting.hot_products_floor_name_placeholder'));
    const langFloorNameLabel = @json(__('panel/setting.hot_products_floor_name'));
    const langRemoveFloor = @json(__('panel/setting.hot_products_remove_floor'));
    const langAddProduct = @json(__('panel/setting.hot_products_add_product'));
    const langSubtitleLabel = @json(__('panel/setting.hot_products_subtitle'));
    const langSubtitlePlaceholder = @json(__('panel/setting.hot_products_subtitle_placeholder'));
    const langPickerTitle = @json(__('panel/setting.hot_products_picker_title'));
    const langPickerHint = @json(__('panel/setting.hot_products_picker_hint'));
    const langPickerPlaceholder = @json(__('panel/setting.hot_products_picker_placeholder'));
    const langPickerEmpty = @json(__('panel/setting.hot_products_picker_empty'));
    const placeholderImage = @json(asset("vendor/innoshop/images/placeholder.png"));

    const productsData = {};
    const panelLocale = @json(panel_locale_code());
    let activeFloorIndex = null;
    let floorCounter = {{ count($product_floors ?? []) }};

    // Collect all product IDs from blade
    const floorProductIds = [];
    @foreach ($product_floors ?? [] as $fi => $floor)
      floorProductIds[{{ $fi }}] = @json($floor['products']);
    @endforeach

    // ========== Product info loading ==========
    function loadProductsInfo() {
      const allIds = [];
      floorProductIds.forEach(function(ids) {
        ids.forEach(function(pid) {
          if (!allIds.includes(pid)) allIds.push(pid);
        });
      });

      if (allIds.length === 0) {
        initSortables();
        mountProductPicker();
        return;
      }

      $.ajax({
        url: urls.panel_api + '/products/names',
        type: 'GET',
        data: { ids: allIds.join(',') },
        success: function(res) {
          if (res.data && Array.isArray(res.data)) {
            res.data.forEach(function(product) {
              productsData[product.id] = {
                id: product.id,
                name: product.name,
                image: product.image_small || product.image_big || '',
                code: product.code || '',
              };
            });
          }
        },
        complete: function() {
          renderAllProductLists();
          initSortables();
          mountProductPicker();
        }
      });
    }

    function renderAllProductLists() {
      for (const fi in floorProductIds) {
        renderProductsInFloor(parseInt(fi));
      }
    }

    function renderProductsInFloor(floorIndex) {
      const $input = $('#floors-products-' + floorIndex);
      if (!$input.length) return;
      let productIds = [];
      try { productIds = JSON.parse($input.val() || '[]'); } catch(e) { productIds = []; }

      const $list = $('.sortable-products-' + floorIndex);
      $list.empty();

      if (productIds.length === 0) {
        $list.html('<li class="list-group-item text-muted small">' + langProductsEmpty + '</li>');
        return;
      }

      productIds.forEach(function(productId) {
        const info = productsData[productId] || {};
        const name = info.name || ('Product ID: ' + productId);
        const image = info.image || '';
        const imageUrl = image || placeholderImage;
        const code = info.code || '';

        $list.append(
          '<li class="list-group-item d-flex justify-content-between align-items-center" data-product-id="' + productId + '">' +
            '<div class="d-flex align-items-center flex-grow-1" style="min-width:0;">' +
              '<i class="bi bi-grip-vertical text-muted me-2" style="cursor:move;flex-shrink:0;"></i>' +
              '<div class="me-2" style="flex-shrink:0;">' +
                '<img src="' + imageUrl + '" alt="' + name + '" class="rounded" style="width:40px;height:40px;object-fit:cover;border:1px solid #dee2e6;">' +
              '</div>' +
              '<div class="flex-grow-1" style="min-width:0;">' +
                '<div class="fw-bold text-truncate" style="max-width:100%;" title="' + name + '">' + name + '</div>' +
                (code ? '<div class="text-muted small text-truncate" style="max-width:100%;">SKU: ' + code + '</div>' : '') +
              '</div>' +
            '</div>' +
            '<button type="button" class="btn btn-sm btn-outline-danger remove-product-btn" data-floor-index="' + floorIndex + '" data-product-id="' + productId + '" style="flex-shrink:0;">' +
              '<i class="bi bi-x"></i>' +
            '</button>' +
          '</li>'
        );
      });
    }

    function initSortables() {
      if (typeof Sortable === 'undefined') return;

      // Floor drag-to-reorder
      const $container = $('#floorList');
      if ($container[0] && $container.children('.floor-card').length > 1) {
        new Sortable($container[0], {
          handle: '.bi-grip-vertical',
          animation: 150,
          onEnd: function() {
            reindexFloors();
          }
        });
      }

      // Product drag-to-reorder within floors
      $container.find('.sortable-products').each(function() {
        if (this.children.length > 1) {
          var cls = this.className || '';
          var match = cls.match(/sortable-products-(\d+)/);
          if (!match) return;
          var floorIndex = parseInt(match[1]);
          new Sortable(this, {
            handle: '.bi-grip-vertical',
            animation: 150,
            onEnd: function(evt) {
              var $input = $('#floors-products-' + floorIndex);
              var ids = [];
              try { ids = JSON.parse($input.val() || '[]'); } catch(e) { ids = []; }
              var item = ids.splice(evt.oldIndex, 1)[0];
              ids.splice(evt.newIndex, 0, item);
              $input.val(JSON.stringify(ids));
            }
          });
        }
      });
    }

    function reindexFloors() {
      $('#floorList .floor-card').each(function(newIndex) {
        $(this).attr('data-floor-index', newIndex);
        var $hidden = $(this).find('input[name^="floors_products"]');
        $hidden.attr('name', 'floors_products[' + newIndex + ']');
        $hidden.attr('id', 'floors-products-' + newIndex);
        $(this).find('.add-product-btn').data('floor-index', newIndex);
        var $list = $(this).find('.sortable-products');
        $list.removeClass(function(i, cls) { return (cls.match(/sortable-products-\d+/g) || []).join(' '); });
        $list.addClass('sortable-products-' + newIndex);
        $(this).find('.remove-product-btn').data('floor-index', newIndex);
      });
    }

    // ========== Add Floor ==========
    $('#addFloorBtn').on('click', function() {
      layer.prompt({
        formType: 0,
        title: langFloorNameLabel,
        value: '',
        placeholder: langFloorNamePlaceholder,
      }, function(value, index) {
        var name = (value || '').trim();
        if (!name) return;

        var newIndex = floorCounter++;
        var html =
          '<div class="card mb-3 floor-card" data-floor-index="' + newIndex + '">' +
            '<div class="card-header d-flex justify-content-between align-items-center">' +
              '<div class="d-flex align-items-center flex-grow-1">' +
                '<i class="bi bi-grip-vertical text-muted me-2" style="cursor:move;flex-shrink:0;"></i>' +
                '<strong>' + name + '</strong>' +
              '</div>' +
              '<button type="button" class="btn btn-sm btn-danger remove-floor-btn">' +
                '<i class="bi bi-trash"></i> ' + langRemoveFloor +
              '</button>' +
            '</div>' +
            '<div class="card-body">' +
              '<div class="mb-3">' +
                '<label class="form-label">' + langFloorNameLabel + '</label>' +
                '<input type="text" class="form-control" name="floors[' + newIndex + '][name][' + panelLocale + ']" value="' + name + '">' +
                '<p class="text-muted small mt-1">' + langFloorNamePlaceholder + '</p>' +
              '</div>' +
              '<div class="mb-3">' +
                '<label class="form-label">' + langSubtitleLabel + '</label>' +
                '<input type="text" class="form-control" name="floors[' + newIndex + '][subtitle][' + panelLocale + ']" value="" placeholder="' + langSubtitlePlaceholder + '">' +
              '</div>' +
              '<div class="mb-2">' +
                '<button type="button" class="btn btn-sm btn-primary add-product-btn" data-floor-index="' + newIndex + '">' +
                  '<i class="bi bi-plus-circle"></i> ' + langAddProduct +
                '</button>' +
              '</div>' +
              '<input type="hidden" name="floors_products[' + newIndex + ']" id="floors-products-' + newIndex + '" value="[]">' +
              '<ul class="list-group sortable-products sortable-products-' + newIndex + '">' +
                '<li class="list-group-item text-muted small">' + langProductsEmpty + '</li>' +
              '</ul>' +
            '</div>' +
          '</div>';
        $('#floorList').append(html);
        initSortables();
        layer.close(index);
      });
    });

    // ========== Remove Floor ==========
    $(document).on('click', '.remove-floor-btn', function() {
      $(this).closest('.floor-card').remove();
    });

    // ========== Add Product (Vue PanelEntityPickerDialog — multiple) ==========
    var productPickerVisible = Vue.ref(false);

    function addProductsToFloor(items) {
      var $input = $('#floors-products-' + activeFloorIndex);
      if (!$input.length) return;
      var ids = [];
      try { ids = JSON.parse($input.val() || '[]'); } catch(e) { ids = []; }

      var changed = false;
      items.forEach(function(item) {
        var id = parseInt(item.id);
        if (!id || ids.includes(id)) return;
        ids.push(id);
        productsData[id] = {
          id: id,
          name: item.name || '',
          image: item.image || '',
          code: item.sku_code || '',
        };
        changed = true;
      });

      if (changed) {
        $input.val(JSON.stringify(ids));
        renderProductsInFloor(activeFloorIndex);
        var $list = $('.sortable-products-' + activeFloorIndex);
        if (typeof Sortable !== 'undefined' && $list[0]) {
          new Sortable($list[0], {
            handle: '.bi-grip-vertical',
            animation: 150,
            onEnd: function(evt) {
              var pids = [];
              try { pids = JSON.parse($input.val() || '[]'); } catch(e) { pids = []; }
              var moved = pids.splice(evt.oldIndex, 1)[0];
              pids.splice(evt.newIndex, 0, moved);
              $input.val(JSON.stringify(pids));
            }
          });
        }
      }
    }

    function mountProductPicker() {
      var mountEl = document.getElementById('product-picker-mount');
      if (!mountEl || mountEl.dataset.vueMounted) return;
      mountEl.dataset.vueMounted = '1';

      var app = Vue.createApp({
        setup: function() {
          function onProductSelect(result) {
            // Multiple mode: result is an array; single mode: result is one object
            var items = Array.isArray(result) ? result : [result];
            addProductsToFloor(items);
            productPickerVisible.value = false;
          }

          return {
            pickerVisible: productPickerVisible,
            onProductSelect: onProductSelect,
            langPickerTitle: langPickerTitle,
            langPickerHint: langPickerHint,
            langPickerPlaceholder: langPickerPlaceholder,
            langPickerEmpty: langPickerEmpty,
          };
        },
        template:
          '<panel-entity-picker-dialog v-model="pickerVisible" entity-type="product" :multiple="true" :title="langPickerTitle" :hint="langPickerHint" :placeholder="langPickerPlaceholder" :empty-text="langPickerEmpty" @select="onProductSelect" />',
      });
      app.use(ElementPlus);
      if (window.InnoPanel && typeof window.InnoPanel.installVue === 'function') {
        window.InnoPanel.installVue(app);
      }
      app.mount(mountEl);
    }

    $(document).on('click', '.add-product-btn', function() {
      activeFloorIndex = parseInt($(this).data('floor-index'));
      productPickerVisible.value = true;
    });

    // ========== Remove Product ==========
    $(document).on('click', '.remove-product-btn', function() {
      var floorIndex = parseInt($(this).data('floor-index'));
      var productId = parseInt($(this).data('product-id'));
      var $input = $('#floors-products-' + floorIndex);
      var ids = [];
      try { ids = JSON.parse($input.val() || '[]'); } catch(e) { ids = []; }
      var idx = ids.indexOf(productId);
      if (idx > -1) {
        ids.splice(idx, 1);
        $input.val(JSON.stringify(ids));
      }
      delete productsData[productId];
      renderProductsInFloor(floorIndex);
    });

    // ========== Init ==========
    loadProductsInfo();
  </script>
@endpush
