<script>
  let urls = {
    panel_base_url: '{{ panel_route('home.index') }}',
    panel_api_base_url: '{{ route('api.panel.base.index') }}',
    file_manager: '{{ route('panel.file_manager.iframe') }}',
    ai_generate: '{{ panel_route('content_ai.generate') }}',
    preview_url: '{{ front_route('home.index') }}?design=1',
  }

  const lang = {
    hint: '提示',
    delete_confirm: '确认删除？',
    confirm: '确认',
    cancel: '取消',
    module_edit: '{{ __('PageBuilder::common.module_edit') }}',
    back: '{{ __('PageBuilder::common.back') }}',
    module_library: '{{ __('PageBuilder::common.module_library') }}',
    search_modules: '{{ __('PageBuilder::common.search_modules') }}',
    no_matching_modules: '{{ __('PageBuilder::common.no_matching_modules') }}',
    back_to_admin: '{{ __('PageBuilder::common.back_to_admin') }}',
    pc_preview: '{{ __('PageBuilder::common.pc_preview') }}',
    pc: '{{ __('PageBuilder::common.pc') }}',
    mobile_preview: '{{ __('PageBuilder::common.mobile_preview') }}',
    mobile: '{{ __('PageBuilder::common.mobile') }}',
    import_demo: '{{ __('PageBuilder::common.import_demo') }}',
    data: '{{ __('PageBuilder::common.data') }}',
    view_website: '{{ __('PageBuilder::common.view_website') }}',
    preview: '{{ __('PageBuilder::common.preview') }}',
    save: '{{ __('PageBuilder::common.save') }}',
    product_module: '{{ __('PageBuilder::common.product_module') }}',
    media_module: '{{ __('PageBuilder::common.media_module') }}',
    content_module: '{{ __('PageBuilder::common.content_module') }}',
    layout_module: '{{ __('PageBuilder::common.layout_module') }}',
  }

  // 获取语言信息
  const $languages = @json(locales());
  // 获取当前语言
  const $locale = '{{ locale_code() }}';

  // 防抖函数 - 保持 this 上下文
  function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
      const context = this; // 保存 this 上下文
      
      const later = () => {
        clearTimeout(timeout);
        func.apply(context, args); // 使用 apply 保持 this 上下文
      };
      clearTimeout(timeout);
      timeout = setTimeout(later, wait);
    };
  }

  // 随机字符串生成
  function randomString(length) {
    const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    let result = '';
    for (let i = 0; i < length; i++) {
      result += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    return result;
  }

  // 全局inno对象
  window.inno = window.inno || {};
  window.inno.randomString = randomString;
  window.inno.debounce = debounce; // 添加 debounce 到 inno 对象

  // 多语言填充函数
  function languagesFill(text) {
    const obj = {};
    $languages.forEach(e => {
      obj[e.code] = text;
    });
    return obj;
  }

  // 默认占位图片
  const PLACEHOLDER_IMAGE_PATH = 'images/placeholder.png';
  const PLACEHOLDER_IMAGE_URL = "{{ asset('images/placeholder.png') }}";

  const asset = document.querySelector('meta[name="asset"]').content;
  if (typeof Vue !== 'undefined') {
    // 定义默认缩略图
    Vue.prototype.thumbnail = function thumbnail(image) {
      if (!image) {
        return PLACEHOLDER_IMAGE_URL;
      }

      if (typeof image === 'string' && image.indexOf('http') === 0) {
        return image;
      }
      if (typeof image === 'object') {
        const locale = $locale || 'zh_cn';
        return image[locale] || (Object.values(image)[0] ||
          PLACEHOLDER_IMAGE_URL);
      }
      return asset + image;
    };

    // 全局thumbnail函数
    window.thumbnail = function(image) {
      if (!image) {
        return PLACEHOLDER_IMAGE_URL;
      }

      if (typeof image === 'string' && image.indexOf('http') === 0) {
        return image;
      }
      if (typeof image === 'object') {
        const locale = $locale || 'zh_cn';
        return image[locale] || (Object.values(image)[0] ||
          PLACEHOLDER_IMAGE_URL);
      }
      return asset + image;
    };

    // 挂载工具函数
    Vue.prototype.stringLengthInte = function stringLengthInte(text, length) {
      return text.length > length ? text.substring(0, length) + '...' : text;
    };
  }
</script>