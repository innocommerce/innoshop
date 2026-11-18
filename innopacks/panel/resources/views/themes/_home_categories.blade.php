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
    var hcPlaceholderImg = @json(asset("images/placeholder.png"));

    // A category cover may be a video (mp4/webm/ogg) instead of an image — render
    // an icon for those so the list never shows a broken <img>.
    function isVideoPath(url) {
      return /\.(mp4|webm|ogg)(\?|$)/i.test(url || '');
    }

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
                image_origin: cat.image_origin || '',
                is_video: !!cat.is_video,
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
        var isVideo = !!info.is_video || isVideoPath(image);
        var videoUrl = info.image_origin || image || '';

        $list.append(
          '<li class="list-group-item d-flex justify-content-between align-items-center home-category-list-item" data-category-id="' + catId + '">' +
            '<div class="d-flex align-items-center flex-grow-1 home-category-list-item__row">' +
              '<i class="bi bi-grip-vertical drag-handle text-muted me-2 flex-shrink-0"></i>' +
              '<div class="me-2 home-category-thumb">' +
                (isVideo
                  ? '<video src="' + videoUrl + '#t=0.5" preload="metadata" muted playsinline title="' + name + '"></video>' +
                    '<span class="home-category-thumb__play"><i class="bi bi-play-circle-fill"></i></span>'
                  : '<img src="' + (image || hcPlaceholderImg) + '" alt="' + name + '">') +
              '</div>' +
              '<div class="flex-grow-1 home-category-list-item__text">' +
                '<div class="fw-bold text-truncate home-category-list-item__name" title="' + name + '">' + name + '</div>' +
              '</div>' +
            '</div>' +
            '<button type="button" class="btn btn-sm btn-outline-danger remove-category-btn home-category-list-item__remove" data-category-id="' + catId + '">' +
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
        var itemImage = item.image || '';
        var itemOrigin = item.image_origin || itemImage;
        categoriesData[id] = {
          id: id,
          name: item.name || '',
          image: itemImage,
          image_origin: itemOrigin,
          is_video: item.is_video != null ? !!item.is_video : isVideoPath(itemOrigin),
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
