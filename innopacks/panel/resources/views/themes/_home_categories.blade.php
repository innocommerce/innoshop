<div class="tab-pane fade" id="tab-setting-home-categories">
  <div class="mb-4">
    <h5 class="mb-3">{{ __('panel/setting.home_categories') }}</h5>
    <p class="text-muted small mb-3">{{ __('panel/setting.home_categories_desc') }}</p>

    <div class="mb-3">
      <button type="button" class="btn btn-success" id="addHomeCategoryBtn">
        <i class="bi bi-plus-circle"></i> {{ __('panel/setting.home_categories_add') }}
      </button>
    </div>

    <input type="hidden" name="home_categories_ids" id="homeCategoriesInput"
           value="{{ json_encode($home_category_ids ?? []) }}">
    <ul class="list-group" id="homeCategoryList"></ul>
  </div>

  {{-- Vue mount point for category picker dialog --}}
  <div id="category-picker-mount"></div>
</div>

@push('footer')
  <script>
    // ========== Home Categories (picker + sortable) ==========
    var hcLangEmpty = @json(__('panel/setting.home_categories_empty'));
    var hcPickerTitle = @json(__('panel/setting.home_categories_picker_title'));
    var hcPickerHint = @json(__('panel/setting.home_categories_picker_hint'));
    var hcPickerPlaceholder = @json(__('panel/setting.home_categories_picker_placeholder'));
    var hcPickerEmpty = @json(__('panel/setting.home_categories_picker_empty'));
    var hcPlaceholderImg = @json(asset("vendor/innoshop/images/placeholder.png"));

    var categoriesData = {};
    var categoryIds = @json($home_category_ids ?? []);

    // ========== Load category info ==========
    function loadCategoriesInfo() {
      if (categoryIds.length === 0) {
        renderCategoryList();
        mountCategoryPicker();
        return;
      }

      $.ajax({
        url: urls.panel_api + '/categories/names',
        type: 'GET',
        data: { ids: categoryIds.join(',') },
        success: function(res) {
          if (res.data && Array.isArray(res.data)) {
            res.data.forEach(function(cat) {
              categoriesData[cat.id] = {
                id: cat.id,
                name: cat.name,
                image: cat.image_small || cat.image || '',
              };
            });
          }
        },
        complete: function() {
          renderCategoryList();
          initCategorySortable();
          mountCategoryPicker();
        }
      });
    }

    function renderCategoryList() {
      var $input = $('#homeCategoriesInput');
      var ids = [];
      try { ids = JSON.parse($input.val() || '[]'); } catch(e) { ids = []; }
      var $list = $('#homeCategoryList');
      $list.empty();

      if (ids.length === 0) {
        $list.html('<li class="list-group-item text-muted small">' + hcLangEmpty + '</li>');
        return;
      }

      ids.forEach(function(catId) {
        var info = categoriesData[catId] || {};
        var name = info.name || ('Category ID: ' + catId);
        var image = info.image || '';
        var imageUrl = image || hcPlaceholderImg;

        $list.append(
          '<li class="list-group-item d-flex justify-content-between align-items-center" data-category-id="' + catId + '">' +
            '<div class="d-flex align-items-center flex-grow-1" style="min-width:0;">' +
              '<i class="bi bi-grip-vertical text-muted me-2" style="cursor:move;flex-shrink:0;"></i>' +
              '<div class="me-2" style="flex-shrink:0;">' +
                '<img src="' + imageUrl + '" alt="' + name + '" class="rounded" style="width:40px;height:40px;object-fit:cover;border:1px solid #dee2e6;">' +
              '</div>' +
              '<div class="flex-grow-1" style="min-width:0;">' +
                '<div class="fw-bold text-truncate" style="max-width:100%;" title="' + name + '">' + name + '</div>' +
              '</div>' +
            '</div>' +
            '<button type="button" class="btn btn-sm btn-outline-danger remove-category-btn" data-category-id="' + catId + '" style="flex-shrink:0;">' +
              '<i class="bi bi-x"></i>' +
            '</button>' +
          '</li>'
        );
      });
    }

    function initCategorySortable() {
      if (typeof Sortable === 'undefined') return;
      var $list = $('#homeCategoryList');
      if ($list[0] && $list.children().length > 1) {
        new Sortable($list[0], {
          handle: '.bi-grip-vertical',
          animation: 150,
          onEnd: function() {
            var newOrder = [];
            $list.find('li[data-category-id]').each(function() {
              newOrder.push(parseInt($(this).data('category-id')));
            });
            $('#homeCategoriesInput').val(JSON.stringify(newOrder));
          }
        });
      }
    }

    // ========== Add Category (Vue PanelEntityPickerDialog — multiple) ==========
    var categoryPickerVisible = Vue.ref(false);

    function addCategoriesToHome(items) {
      var $input = $('#homeCategoriesInput');
      var ids = [];
      try { ids = JSON.parse($input.val() || '[]'); } catch(e) { ids = []; }

      var changed = false;
      items.forEach(function(item) {
        var id = parseInt(item.id);
        if (!id || ids.includes(id)) return;
        ids.push(id);
        categoriesData[id] = {
          id: id,
          name: item.name || '',
          image: item.image || '',
        };
        changed = true;
      });

      if (changed) {
        $input.val(JSON.stringify(ids));
        renderCategoryList();
        initCategorySortable();
      }
    }

    function mountCategoryPicker() {
      var mountEl = document.getElementById('category-picker-mount');
      if (!mountEl || mountEl.dataset.vueMounted) return;
      mountEl.dataset.vueMounted = '1';

      var app = Vue.createApp({
        setup: function() {
          function onCategorySelect(result) {
            var items = Array.isArray(result) ? result : [result];
            addCategoriesToHome(items);
            categoryPickerVisible.value = false;
          }

          return {
            pickerVisible: categoryPickerVisible,
            onCategorySelect: onCategorySelect,
            hcPickerTitle: hcPickerTitle,
            hcPickerHint: hcPickerHint,
            hcPickerPlaceholder: hcPickerPlaceholder,
            hcPickerEmpty: hcPickerEmpty,
          };
        },
        template:
          '<panel-entity-picker-dialog v-model="pickerVisible" entity-type="category" :multiple="true" :title="hcPickerTitle" :hint="hcPickerHint" :placeholder="hcPickerPlaceholder" :empty-text="hcPickerEmpty" @select="onCategorySelect" />',
      });
      app.use(ElementPlus);
      if (window.InnoPanel && typeof window.InnoPanel.installVue === 'function') {
        window.InnoPanel.installVue(app);
      }
      app.mount(mountEl);
    }

    $('#addHomeCategoryBtn').on('click', function() {
      categoryPickerVisible.value = true;
    });

    // ========== Remove Category ==========
    $(document).on('click', '.remove-category-btn', function() {
      var catId = parseInt($(this).data('category-id'));
      var $input = $('#homeCategoriesInput');
      var ids = [];
      try { ids = JSON.parse($input.val() || '[]'); } catch(e) { ids = []; }
      var idx = ids.indexOf(catId);
      if (idx > -1) {
        ids.splice(idx, 1);
        $input.val(JSON.stringify(ids));
      }
      delete categoriesData[catId];
      renderCategoryList();
    });

    // ========== Init ==========
    loadCategoriesInfo();
  </script>
@endpush
