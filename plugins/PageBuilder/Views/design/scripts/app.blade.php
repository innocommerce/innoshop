<script>
  const urls = {
    panel_api: '{{ route('api.panel.base.index') }}',
    panel_base: '{{ panel_route('home.index') }}',
    panel_upload: '{{ panel_route('upload.images') }}',
    panel_ai: '{{ panel_route('content_ai.generate') }}',
  }

  const lang = {
    hint: '{{ __('PageBuilder::common.hint') }}',
    delete_confirm: '{{ __('PageBuilder::common.confirm_delete_module') }}',
    confirm: '{{ __('PageBuilder::common.confirm') }}',
    cancel: '{{ __('PageBuilder::common.cancel') }}',
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
    select_page: '{{ __('PageBuilder::common.select_page') }}',
    home_page: '{{ __('PageBuilder::common.home_page') }}',
    content_module: '{{ __('PageBuilder::common.content_module') }}',
    layout_module: '{{ __('PageBuilder::common.layout_module') }}',
    saved: '{{ __('PageBuilder::common.saved') }}',
    unsaved: '{{ __('PageBuilder::common.unsaved') }}',
    saving: '{{ __('PageBuilder::common.saving') }}',
    save_failed: '{{ __('PageBuilder::common.save_failed') }}',
    module_template_not_found: '{{ __('PageBuilder::common.module_template_not_found') }}',
    internal_server_error: '{{ __('PageBuilder::common.internal_server_error') }}',
    module_data_format_error: '{{ __('PageBuilder::common.module_data_format_error') }}',
    request_failed: '{{ __('PageBuilder::common.request_failed') }}',
    network_connection_failed: '{{ __('PageBuilder::common.network_connection_failed') }}',
    unknown_error: '{{ __('PageBuilder::common.unknown_error') }}',
    failed_to_update_module: '{{ __('PageBuilder::common.failed_to_update_module') }}',
    failed_to_add_module: '{{ __('PageBuilder::common.failed_to_add_module') }}',
    import_failed: '{{ __('PageBuilder::common.import_failed') }}',
    confirm_delete_module: '{{ __('PageBuilder::common.confirm_delete_module') }}',
    confirm_import_demo: '{{ __('PageBuilder::common.confirm_import_demo') }}',
    demo_data_home_only: '{{ __('PageBuilder::common.demo_data_home_only') }}',
    already_editing_module: '{{ __('PageBuilder::common.already_editing_module') }}',
    // Module names
    module_slideshow: '{{ __('PageBuilder::modules.slideshow') }}',
    module_custom_products: '{{ __('PageBuilder::modules.custom_products') }}',
    module_category_products: '{{ __('PageBuilder::modules.category_products') }}',
    module_latest_products: '{{ __('PageBuilder::modules.latest_products') }}',
    module_rich_text: '{{ __('PageBuilder::modules.rich_text') }}',
    module_single_image: '{{ __('PageBuilder::modules.single_image') }}',
    module_four_image: '{{ __('PageBuilder::modules.four_image') }}',
    module_left_image_right_text: '{{ __('PageBuilder::modules.left_image_right_text') }}',
    module_brands: '{{ __('PageBuilder::modules.brands') }}',
    module_brand_products: '{{ __('PageBuilder::modules.brand_products') }}',
    module_card_slider: '{{ __('PageBuilder::modules.card_slider') }}',
    module_multi_row_images: '{{ __('PageBuilder::modules.multi_row_images') }}',
    module_image_text_list: '{{ __('PageBuilder::modules.image_text_list') }}',
    module_four_image_plus: '{{ __('PageBuilder::modules.four_image_plus') }}',
    module_article: '{{ __('PageBuilder::modules.article') }}',
    module_video: '{{ __('PageBuilder::modules.video') }}',
    // Editor labels - Category and Brand
    category_settings: '{{ __('PageBuilder::editor.category_settings') }}',
    current_category: '{{ __('PageBuilder::editor.current_category') }}',
    search_category: '{{ __('PageBuilder::editor.search_category') }}',
    search_category_placeholder: '{{ __('PageBuilder::editor.search_category_placeholder') }}',
    brand_settings: '{{ __('PageBuilder::editor.brand_settings') }}',
    current_brand: '{{ __('PageBuilder::editor.current_brand') }}',
    search_brand: '{{ __('PageBuilder::editor.search_brand') }}',
    search_brand_placeholder: '{{ __('PageBuilder::editor.search_brand_placeholder') }}',
    product_quantity: '{{ __('PageBuilder::editor.product_quantity') }}',
    enter_product_quantity: '{{ __('PageBuilder::editor.enter_product_quantity') }}',
    show_latest_products_count: '{{ __('PageBuilder::editor.show_latest_products_count') }}',
    sort_method: '{{ __('PageBuilder::editor.sort_method') }}',
    sort_sales_desc: '{{ __('PageBuilder::editor.sort_sales_desc') }}',
    sort_price_desc: '{{ __('PageBuilder::editor.sort_price_desc') }}',
    sort_price_asc: '{{ __('PageBuilder::editor.sort_price_asc') }}',
    sort_created_desc: '{{ __('PageBuilder::editor.sort_created_desc') }}',
    sort_rating_desc: '{{ __('PageBuilder::editor.sort_rating_desc') }}',
    sort_viewed_desc: '{{ __('PageBuilder::editor.sort_viewed_desc') }}',
    sort_updated_desc: '{{ __('PageBuilder::editor.sort_updated_desc') }}',
    sort_position_asc: '{{ __('PageBuilder::editor.sort_position_asc') }}',
    items_2: '{{ __('PageBuilder::editor.items_2') }}',
    autoplay: '{{ __('PageBuilder::editor.autoplay') }}',
    enable: '{{ __('PageBuilder::editor.enable') }}',
    disable: '{{ __('PageBuilder::editor.disable') }}',
    delete_extra_screens_to_disable: '{{ __('PageBuilder::editor.delete_extra_screens_to_disable') }}',
    product_content: '{{ __('PageBuilder::editor.product_content') }}',
    add_screen: '{{ __('PageBuilder::editor.add_screen') }}',
    screen: '{{ __('PageBuilder::editor.screen') }}',
    screen_products: '{{ __('PageBuilder::editor.screen_products') }}',
    no_products_in_screen: '{{ __('PageBuilder::editor.no_products_in_screen') }}',
    items_count: '{{ __('PageBuilder::editor.items_count') }}',
    add_products: '{{ __('PageBuilder::editor.add_products') }}',
    delete_current_screen: '{{ __('PageBuilder::editor.delete_current_screen') }}',
    keep_at_least_one_product: '{{ __('PageBuilder::editor.keep_at_least_one_product') }}',
    keep_at_least_one_screen: '{{ __('PageBuilder::editor.keep_at_least_one_screen') }}',
    select_brands: '{{ __('PageBuilder::editor.select_brands') }}',
    selected_brands: '{{ __('PageBuilder::editor.selected_brands') }}',
    no_brands_search: '{{ __('PageBuilder::editor.no_brands_search') }}',
    article_management: '{{ __('PageBuilder::editor.article_management') }}',
    add_article: '{{ __('PageBuilder::editor.add_article') }}',
    selected_articles: '{{ __('PageBuilder::editor.selected_articles') }}',
    no_articles_search: '{{ __('PageBuilder::editor.no_articles_search') }}',
    video_type: '{{ __('PageBuilder::editor.video_type') }}',
    local_video: '{{ __('PageBuilder::editor.local_video') }}',
    youtube: '{{ __('PageBuilder::editor.youtube') }}',
    vimeo: '{{ __('PageBuilder::editor.vimeo') }}',
    video_file: '{{ __('PageBuilder::editor.video_file') }}',
    click_select_video: '{{ __('PageBuilder::editor.click_select_video') }}',
    video_formats_supported: '{{ __('PageBuilder::editor.video_formats_supported') }}',
    video_url: '{{ __('PageBuilder::editor.video_url') }}',
    enter_video_url: '{{ __('PageBuilder::editor.enter_video_url') }}',
    cover_image: '{{ __('PageBuilder::editor.cover_image') }}',
    video_title: '{{ __('PageBuilder::editor.video_title') }}',
    video_description: '{{ __('PageBuilder::editor.video_description') }}',
    video_controls: '{{ __('PageBuilder::editor.video_controls') }}',
    loop: '{{ __('PageBuilder::editor.loop') }}',
    muted: '{{ __('PageBuilder::editor.muted') }}',
    show_controls: '{{ __('PageBuilder::editor.show_controls') }}',
    image_position: '{{ __('PageBuilder::editor.image_position') }}',
    left_image_right_text: '{{ __('PageBuilder::editor.left_image_right_text') }}',
    right_image_left_text: '{{ __('PageBuilder::editor.right_image_left_text') }}',
    description_content: '{{ __('PageBuilder::editor.description_content') }}',
    enter_description: '{{ __('PageBuilder::editor.enter_description') }}',
    text_alignment: '{{ __('PageBuilder::editor.text_alignment') }}',
    button_text: '{{ __('PageBuilder::editor.button_text') }}',
    row: '{{ __('PageBuilder::editor.row') }}',
    items_per_row_select: '{{ __('PageBuilder::editor.items_per_row_select') }}',
    row_3_items: '{{ __('PageBuilder::editor.row_3_items') }}',
    row_4_items: '{{ __('PageBuilder::editor.row_4_items') }}',
    row_6_items: '{{ __('PageBuilder::editor.row_6_items') }}',
    expand: '{{ __('PageBuilder::editor.expand') }}',
    collapse: '{{ __('PageBuilder::editor.collapse') }}',
    add_row: '{{ __('PageBuilder::editor.add_row') }}',
    recommended_same_size_drag: '{{ __('PageBuilder::editor.recommended_same_size_drag') }}',
    items_5: '{{ __('PageBuilder::editor.items_5') }}',
    image_text_items: '{{ __('PageBuilder::editor.image_text_items') }}',
    add_image_text_item: '{{ __('PageBuilder::editor.add_image_text_item') }}',
    no_image_text_items_search: '{{ __('PageBuilder::editor.no_image_text_items_search') }}',
    image_number: '{{ __('PageBuilder::editor.image_number') }}',
    style_settings: '{{ __('PageBuilder::editor.style_settings') }}',
    display_columns: '{{ __('PageBuilder::editor.display_columns') }}',
    autoplay_interval: '{{ __('PageBuilder::editor.autoplay_interval') }}',
    autoplay_interval_tip: '{{ __('PageBuilder::editor.autoplay_interval_tip') }}',
    show_brand_names: '{{ __('PageBuilder::editor.show_brand_names') }}',
    show: '{{ __('PageBuilder::editor.show') }}',
    hide: '{{ __('PageBuilder::editor.hide') }}',
    image_height: '{{ __('PageBuilder::editor.image_height') }}',
    image_height_tip: '{{ __('PageBuilder::editor.image_height_tip') }}',
    padding: '{{ __('PageBuilder::editor.padding') }}',
    padding_tip: '{{ __('PageBuilder::editor.padding_tip') }}',
    border_radius: '{{ __('PageBuilder::editor.border_radius') }}',
    border_radius_tip: '{{ __('PageBuilder::editor.border_radius_tip') }}',
    border_width: '{{ __('PageBuilder::editor.border_width') }}',
    border_width_tip: '{{ __('PageBuilder::editor.border_width_tip') }}',
    border_color: '{{ __('PageBuilder::editor.border_color') }}',
    border_style: '{{ __('PageBuilder::editor.border_style') }}',
    solid: '{{ __('PageBuilder::editor.solid') }}',
    dashed: '{{ __('PageBuilder::editor.dashed') }}',
    dotted: '{{ __('PageBuilder::editor.dotted') }}',
    double: '{{ __('PageBuilder::editor.double') }}',
    drag_sort_add_multiple: '{{ __('PageBuilder::editor.drag_sort_add_multiple') }}',
    search_article_placeholder: '{{ __('PageBuilder::editor.search_article_placeholder') }}',
    no_articles_editor: '{{ __('PageBuilder::editor.no_articles_editor') }}',
    enter_video_title: '{{ __('PageBuilder::editor.enter_video_title') }}',
    enter_video_description: '{{ __('PageBuilder::editor.enter_video_description') }}',
    enter_button_text_placeholder: '{{ __('PageBuilder::editor.enter_button_text_placeholder') }}',
    row_number_display: '{{ __('PageBuilder::editor.row_number_display') }}',
    // Editor labels
    module_width: '{{ __('PageBuilder::editor.module_width') }}',
    narrow_screen: '{{ __('PageBuilder::editor.narrow_screen') }}',
    wide_screen: '{{ __('PageBuilder::editor.wide_screen') }}',
    full_screen: '{{ __('PageBuilder::editor.full_screen') }}',
    module_title: '{{ __('PageBuilder::editor.module_title') }}',
    subtitle: '{{ __('PageBuilder::editor.subtitle') }}',
    display_settings: '{{ __('PageBuilder::editor.display_settings') }}',
    items_per_row: '{{ __('PageBuilder::editor.items_per_row') }}',
    items_3: '{{ __('PageBuilder::editor.items_3') }}',
    items_4: '{{ __('PageBuilder::editor.items_4') }}',
    items_6: '{{ __('PageBuilder::editor.items_6') }}',
    product_settings: '{{ __('PageBuilder::editor.product_settings') }}',
    search_products: '{{ __('PageBuilder::editor.search_products') }}',
    search_products_placeholder: '{{ __('PageBuilder::editor.search_products_placeholder') }}',
    search_and_add_products: '{{ __('PageBuilder::editor.search_and_add_products') }}',
    selected_products: '{{ __('PageBuilder::editor.selected_products') }}',
    no_products_search: '{{ __('PageBuilder::editor.no_products_search') }}',
    image_settings: '{{ __('PageBuilder::editor.image_settings') }}',
    link_settings: '{{ __('PageBuilder::editor.link_settings') }}',
    title_settings: '{{ __('PageBuilder::editor.title_settings') }}',
    subtitle_settings: '{{ __('PageBuilder::editor.subtitle_settings') }}',
    button_settings: '{{ __('PageBuilder::editor.button_settings') }}',
    button_link: '{{ __('PageBuilder::editor.button_link') }}',
    position_settings: '{{ __('PageBuilder::editor.position_settings') }}',
    content_position: '{{ __('PageBuilder::editor.content_position') }}',
    left: '{{ __('PageBuilder::editor.left') }}',
    center: '{{ __('PageBuilder::editor.center') }}',
    right: '{{ __('PageBuilder::editor.right') }}',
    slideshow_management: '{{ __('PageBuilder::editor.slideshow_management') }}',
    title_not_set: '{{ __('PageBuilder::editor.title_not_set') }}',
    select_image: '{{ __('PageBuilder::editor.select_image') }}',
    clear: '{{ __('PageBuilder::editor.clear') }}',
    recommended_size: '{{ __('PageBuilder::editor.recommended_size') }}',
    enter_title: '{{ __('PageBuilder::editor.enter_title') }}',
    enter_subtitle: '{{ __('PageBuilder::editor.enter_subtitle') }}',
    enter_module_title: '{{ __('PageBuilder::editor.enter_module_title') }}',
    enter_button_text: '{{ __('PageBuilder::editor.enter_button_text') }}',
    title_color: '{{ __('PageBuilder::editor.title_color') }}',
    title_size: '{{ __('PageBuilder::editor.title_size') }}',
    subtitle_color: '{{ __('PageBuilder::editor.subtitle_color') }}',
    subtitle_size: '{{ __('PageBuilder::editor.subtitle_size') }}',
    button_bg_color: '{{ __('PageBuilder::editor.button_bg_color') }}',
    button_text_color: '{{ __('PageBuilder::editor.button_text_color') }}',
    no_slideshow_add: '{{ __('PageBuilder::editor.no_slideshow_add') }}',
    add_slideshow: '{{ __('PageBuilder::editor.add_slideshow') }}',
    confirm_delete_slideshow: '{{ __('PageBuilder::editor.confirm_delete_slideshow') }}',
    delete_success: '{{ __('PageBuilder::editor.delete_success') }}',
    add_slideshow_success: '{{ __('PageBuilder::editor.add_slideshow_success') }}',
    select_link_type: '{{ __('PageBuilder::editor.select_link_type') }}',
    enter_keyword_search: '{{ __('PageBuilder::editor.enter_keyword_search') }}',
    search: '{{ __('PageBuilder::editor.search') }}',
    new_window_open: '{{ __('PageBuilder::editor.new_window_open') }}',
    manage: '{{ __('PageBuilder::editor.manage') }}',
    custom_name: '{{ __('PageBuilder::editor.custom_name') }}',
    enter_link_address: '{{ __('PageBuilder::editor.enter_link_address') }}',
    content: '{{ __('PageBuilder::editor.content') }}',
    status: '{{ __('PageBuilder::editor.status') }}',
    enabled: '{{ __('PageBuilder::editor.enabled') }}',
    disabled: '{{ __('PageBuilder::editor.disabled') }}',
    data_not_exists: '{{ __('PageBuilder::editor.data_not_exists') }}',
    go_add: '{{ __('PageBuilder::editor.go_add') }}',
    confirm_button: '{{ __('PageBuilder::editor.confirm_button') }}',
    none: '{{ __('PageBuilder::editor.none') }}',
    product_link: '{{ __('PageBuilder::editor.product_link') }}',
    product_category: '{{ __('PageBuilder::editor.product_category') }}',
    specific_page: '{{ __('PageBuilder::editor.specific_page') }}',
    article_category: '{{ __('PageBuilder::editor.article_category') }}',
    product_brand: '{{ __('PageBuilder::editor.product_brand') }}',
    fixed_link: '{{ __('PageBuilder::editor.fixed_link') }}',
    custom: '{{ __('PageBuilder::editor.custom') }}',
    select_link: '{{ __('PageBuilder::editor.select_link') }}',
    basic_settings: '{{ __('PageBuilder::editor.basic_settings') }}',
    content_settings: '{{ __('PageBuilder::editor.content_settings') }}',
    rich_text_content: '{{ __('PageBuilder::editor.rich_text_content') }}',
    edit_content: '{{ __('PageBuilder::editor.edit_content') }}',
    no_content_click_edit: '{{ __('PageBuilder::editor.no_content_click_edit') }}',
    click_edit_start: '{{ __('PageBuilder::editor.click_edit_start') }}',
    content_set: '{{ __('PageBuilder::editor.content_set') }}',
    content_not_set: '{{ __('PageBuilder::editor.content_not_set') }}',
    rich_text_editor: '{{ __('PageBuilder::editor.rich_text_editor') }}',
    save_content: '{{ __('PageBuilder::editor.save_content') }}',
    content_saved: '{{ __('PageBuilder::editor.content_saved') }}',
    file_manager_not_loaded: '{{ __('PageBuilder::editor.file_manager_not_loaded') }}',
    editor_load_failed: '{{ __('PageBuilder::editor.editor_load_failed') }}',
    image_description: '{{ __('PageBuilder::editor.image_description') }}',
    enter_image_description: '{{ __('PageBuilder::editor.enter_image_description') }}',
    image_link: '{{ __('PageBuilder::editor.image_link') }}',
    add_image: '{{ __('PageBuilder::editor.add_image') }}',
    max_4_images: '{{ __('PageBuilder::editor.max_4_images') }}',
    recommended_same_size: '{{ __('PageBuilder::editor.recommended_same_size') }}',
    recommended_size_400: '{{ __('PageBuilder::editor.recommended_size_400') }}',
    drag_sort: '{{ __('PageBuilder::editor.drag_sort') }}',
    delete: '{{ __('PageBuilder::editor.delete') }}',
    image: '{{ __('PageBuilder::editor.image') }}',
    click_select_image: '{{ __('PageBuilder::editor.click_select_image') }}',
    preview_image: '{{ __('PageBuilder::editor.preview_image') }}',
    file_manager_unavailable: '{{ __('PageBuilder::editor.file_manager_unavailable') }}',
    account_center: '{{ __('PageBuilder::editor.account_center') }}',
    my_favorites: '{{ __('PageBuilder::editor.my_favorites') }}',
    my_orders: '{{ __('PageBuilder::editor.my_orders') }}',
    latest_products: '{{ __('PageBuilder::editor.latest_products') }}',
    brand_list: '{{ __('PageBuilder::editor.brand_list') }}',
    // Left image right text - additional
    margin_settings: '{{ __('PageBuilder::editor.margin_settings') }}',
    overall_margin: '{{ __('PageBuilder::editor.overall_margin') }}',
    left_margin: '{{ __('PageBuilder::editor.left_margin') }}',
    right_margin: '{{ __('PageBuilder::editor.right_margin') }}',
    top_margin: '{{ __('PageBuilder::editor.top_margin') }}',
    bottom_margin: '{{ __('PageBuilder::editor.bottom_margin') }}',
    content_spacing: '{{ __('PageBuilder::editor.content_spacing') }}',
    title_spacing: '{{ __('PageBuilder::editor.title_spacing') }}',
    subtitle_spacing: '{{ __('PageBuilder::editor.subtitle_spacing') }}',
    description_spacing: '{{ __('PageBuilder::editor.description_spacing') }}',
    image_selection: '{{ __('PageBuilder::editor.image_selection') }}',
    recommended_size_800_450: '{{ __('PageBuilder::editor.recommended_size_800_450') }}',
    image_padding: '{{ __('PageBuilder::editor.image_padding') }}',
    horizontal_padding: '{{ __('PageBuilder::editor.horizontal_padding') }}',
    vertical_padding: '{{ __('PageBuilder::editor.vertical_padding') }}',
    align_left: '{{ __('PageBuilder::editor.align_left') }}',
    align_center: '{{ __('PageBuilder::editor.align_center') }}',
    align_right: '{{ __('PageBuilder::editor.align_right') }}',
    // Multi row images - additional
    no_images_click_add: '{{ __('PageBuilder::editor.no_images_click_add') }}',
    add_image_with_count: '{{ __('PageBuilder::editor.add_image_with_count') }}',
    add_new_row: '{{ __('PageBuilder::editor.add_new_row') }}',
    desc_bg_color: '{{ __('PageBuilder::editor.desc_bg_color') }}',
    desc_text_color: '{{ __('PageBuilder::editor.desc_text_color') }}',
    desc_font_size: '{{ __('PageBuilder::editor.desc_font_size') }}',
    desc_font_size_tip: '{{ __('PageBuilder::editor.desc_font_size_tip') }}',
    // Image text list - additional
    autoplay_interval_time: '{{ __('PageBuilder::editor.autoplay_interval_time') }}',
    show_title: '{{ __('PageBuilder::editor.show_title') }}',
    image_height_tip: '{{ __('PageBuilder::editor.image_height_tip') }}',
    padding_tip_image_text: '{{ __('PageBuilder::editor.padding_tip_image_text') }}',
    image_text_item_management: '{{ __('PageBuilder::editor.image_text_item_management') }}',
    no_image_text_items: '{{ __('PageBuilder::editor.no_image_text_items') }}',
    click_add_image_text_item: '{{ __('PageBuilder::editor.click_add_image_text_item') }}',
    add_image_text_item_dialog: '{{ __('PageBuilder::editor.add_image_text_item_dialog') }}',
    edit_image_text_item_dialog: '{{ __('PageBuilder::editor.edit_image_text_item_dialog') }}',
    item_title: '{{ __('PageBuilder::editor.item_title') }}',
    enter_item_title: '{{ __('PageBuilder::editor.enter_item_title') }}',
    item_image: '{{ __('PageBuilder::editor.item_image') }}',
    recommended_size_200_100: '{{ __('PageBuilder::editor.recommended_size_200_100') }}',
    item_link_optional: '{{ __('PageBuilder::editor.item_link_optional') }}',
    // Four image plus - additional
    max_4_images_warning: '{{ __('PageBuilder::editor.max_4_images_warning') }}',
  }

  const $languages = @json(locales());
  const $locale = '{{ locale_code() }}';
  const $panelBaseUrl = '{{ panel_route('home.index') }}';

  function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
      const context = this;
      
      const later = () => {
        clearTimeout(timeout);
        func.apply(context, args);
      };
      clearTimeout(timeout);
      timeout = setTimeout(later, wait);
    };
  }

  function randomString(length) {
    const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    let result = '';
    for (let i = 0; i < length; i++) {
      result += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    return result;
  }

  window.inno = window.inno || {};
  window.inno.randomString = randomString;
  window.inno.debounce = debounce;

  function languagesFill(text) {
    const obj = {};
    $languages.forEach(e => {
      obj[e.code] = text;
    });
    return obj;
  }

  const PLACEHOLDER_IMAGE_PATH = 'images/placeholder.png';
  const PLACEHOLDER_IMAGE_URL = "{{ asset('images/placeholder.png') }}";

  const asset = document.querySelector('meta[name="asset"]').content;
  if (typeof Vue !== 'undefined') {
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

    Vue.prototype.stringLengthInte = function stringLengthInte(text, length) {
      return text.length > length ? text.substring(0, length) + '...' : text;
    };
  }
</script>