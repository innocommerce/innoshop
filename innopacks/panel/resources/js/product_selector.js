
// ProductSelector.js
const ProductSelector = {
  init: function(callback, options = {}) {
    const defaultOptions = {
      keyword: '',
    };

    const finalOptions = { ...defaultOptions, ...options };

    // 直接注册全局回调
    window.productSelectorCallback = function(product) {
      if (typeof callback === "function") {
        callback(product);
      }
    };

   
    layer.open({
      type: 2,
      title: "产品选择器", 
      shadeClose: false,
      shade: 0.8,
      area: ["800px", "800px"],
      content: urls.base_url + '/product-selector',
    });
  }
};

export default ProductSelector; 