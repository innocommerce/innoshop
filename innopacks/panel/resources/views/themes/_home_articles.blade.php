<div class="tab-pane fade" id="tab-setting-home-articles">
  <div class="mb-4">
    <h5 class="mb-3">{{ __('panel/setting.home_articles') }}</h5>
    <p class="text-muted small mb-3">{{ __('panel/setting.home_articles_desc') }}</p>

    <div class="mb-3">
      <button type="button" class="btn btn-primary" id="addArticleBtn">
        <i class="bi bi-plus-circle"></i> {{ __('panel/setting.home_articles_add') }}
      </button>
    </div>

    <div class="card">
      <div class="card-header">{{ __('panel/setting.home_articles_selected') }}</div>
      <div class="card-body">
        <ul class="list-group" id="selectedHomeArticles"></ul>
      </div>
    </div>

    @php
      $homeArticlesValue = old('home_articles');
      if (empty($homeArticlesValue)) {
        $settingValue = system_setting('home_articles', []);
        $homeArticlesValue = is_array($settingValue) ? json_encode($settingValue) : ($settingValue ?: '[]');
      }
    @endphp
    <input type="hidden" name="home_articles" id="home_articles" value="{{ $homeArticlesValue }}">

    {{-- Vue mount point for article picker dialog --}}
    <div id="article-picker-mount"></div>
  </div>
</div>

@push('footer')
  <script>
    // ========== Home Articles (Vue PanelEntityPickerDialog) ==========
    const langArticlesEmpty = @json(__('panel/setting.home_articles_empty'));
    const langArticleIdLabel = @json(__('panel/setting.home_articles'));
    const langArticlesPickerTitle = @json(__('panel/setting.home_articles_picker_title'));
    const langArticlesPickerHint = @json(__('panel/setting.home_articles_picker_hint'));
    const langArticlesPickerPlaceholder = @json(__('panel/setting.home_articles_picker_placeholder'));
    const langArticlesPickerEmpty = @json(__('panel/setting.home_articles_picker_empty'));

    const articlesData = {};
    let selectedArticleIds = JSON.parse($('#home_articles').val() || '[]');

    function renderSelectedArticles() {
      const $container = $('#selectedHomeArticles');
      $container.empty();

      if (selectedArticleIds.length === 0) {
        $container.html('<li class="list-group-item text-muted small">' + langArticlesEmpty + '</li>');
        updateHomeArticlesInput();
        return;
      }

      selectedArticleIds.forEach(function(articleId) {
        const info = articlesData[articleId] || {};
        const title = info.name || (langArticleIdLabel + ' ID: ' + articleId);
        const image = info.image || '';

        const $item = $(`
          <li class="list-group-item d-flex align-items-center" data-article-id="${articleId}">
            <i class="bi bi-grip-vertical drag-handle text-muted me-2 flex-shrink-0"></i>
            <div class="me-2" style="flex-shrink:0;">
              ${image ? '<img src="' + image + '" class="rounded" style="width:36px;height:36px;object-fit:cover;border:1px solid #dee2e6;">' : '<div class="bg-light rounded d-flex align-items-center justify-content-center" style="width:36px;height:36px;"><i class="bi bi-file-earmark-text text-muted"></i></div>'}
            </div>
            <div class="flex-grow-1" style="min-width:0;">
              <div class="fw-bold text-truncate" style="max-width:100%;" title="${title}">${title}</div>
            </div>
            <button type="button" class="btn btn-sm btn-outline-danger remove-article" data-article-id="${articleId}" style="flex-shrink:0;">
              <i class="bi bi-x"></i>
            </button>
          </li>
        `);
        $container.append($item);
      });

      updateHomeArticlesInput();
    }

    function updateHomeArticlesInput() {
      $('#home_articles').val(JSON.stringify(selectedArticleIds));
    }

    $(document).on('click', '.remove-article', function() {
      const id = parseInt($(this).data('article-id'));
      selectedArticleIds = selectedArticleIds.filter(aid => aid !== id);
      delete articlesData[id];
      renderSelectedArticles();
    });

    const articlePickerVisible = Vue.ref(false);

    function mountArticlePicker() {
      const mountEl = document.getElementById('article-picker-mount');
      if (!mountEl || mountEl.dataset.vueMounted) return;
      mountEl.dataset.vueMounted = '1';

      const app = Vue.createApp({
        setup() {
          function onArticleSelect(item) {
            const id = parseInt(item.id);
            if (!id || selectedArticleIds.includes(id)) return;

            selectedArticleIds.push(id);
            articlesData[id] = {
              id: id,
              name: item.name || item.title || '',
              image: item.image || '',
            };
            renderSelectedArticles();
            articlePickerVisible.value = false;
          }

          return { pickerVisible: articlePickerVisible, onArticleSelect,
            langArticlesPickerTitle, langArticlesPickerHint,
            langArticlesPickerPlaceholder, langArticlesPickerEmpty };
        },
        template: `
          <panel-entity-picker-dialog
            v-model="pickerVisible"
            entity-type="article"
            :title="langArticlesPickerTitle"
            :hint="langArticlesPickerHint"
            :placeholder="langArticlesPickerPlaceholder"
            :empty-text="langArticlesPickerEmpty"
            @select="onArticleSelect"
          />`,
      });
      app.use(ElementPlus);
      if (window.InnoPanel && typeof window.InnoPanel.installVue === 'function') {
        window.InnoPanel.installVue(app);
      }
      app.mount(mountEl);

      document.getElementById('addArticleBtn').addEventListener('click', function() {
        articlePickerVisible.value = true;
      });
    }

    function loadHomeArticlesInfo() {
      if (selectedArticleIds.length === 0) {
        renderSelectedArticles();
        mountArticlePicker();
        return;
      }

      $.ajax({
        url: urls.panel_api + '/articles/names',
        type: 'GET',
        data: { ids: selectedArticleIds.join(',') },
        success: function(res) {
          if (res.data && Array.isArray(res.data)) {
            res.data.forEach(function(article) {
              articlesData[article.id] = {
                id: article.id,
                name: article.name,
                image: article.image_small || article.image_big || '',
              };
            });
          }
          renderSelectedArticles();
        },
        error: function() {
          renderSelectedArticles();
        },
        complete: function() {
          mountArticlePicker();
          if (typeof Sortable !== 'undefined') {
            new Sortable(document.getElementById('selectedHomeArticles'), {
              handle: '.bi-grip-vertical',
              animation: 150,
              onEnd: function(evt) {
                const item = selectedArticleIds.splice(evt.oldIndex, 1)[0];
                selectedArticleIds.splice(evt.newIndex, 0, item);
                updateHomeArticlesInput();
              }
            });
          }
        }
      });
    }

    loadHomeArticlesInfo();
  </script>
@endpush
