
/**
 * 产品选择器模块
 * 提供产品选择功能，不自动弹窗
 */
export default {
  /**
   * 初始化产品选择器
   * @param {Function} callback - 选择产品后的回调函数
   * @param {Object} options - 配置选项
   */
  init: function(callback, options = {}) {
    const defaultOptions = {
      keyword: '',
    };

    const finalOptions = { ...defaultOptions, ...options };

    // 注册全局回调函数
    window.productSelectorCallback = function(product) {
      if (typeof callback === "function") {
        callback(product);
      }
    };

    // 不自动弹窗，只注册回调
    console.log('产品选择器已初始化，回调已注册');
  },

  /**
   * 手动打开产品选择器弹窗
   * @param {Object} options - 弹窗配置选项
   */
  open: function(options = {}) {
    const defaultOptions = {
      type: 2,
      title: "产品选择器",
      shadeClose: false,
      shade: 0.8,
      area: ["800px", "800px"],
      content: urls.base_url + '/products/selector',
    };

    const finalOptions = { ...defaultOptions, ...options };

    layer.open(finalOptions);
  }
};