export default {
  // 通过正则表达式匹配url中的参数，如果匹配到了，就替换掉原来的参数，如果没有匹配到，就添加参数
  updateQueryStringParameter(uri, key, value) {
    var re = new RegExp("([?&])" + key + "=.*?(&|$)", "i");
    var separator = uri.indexOf('?') !== -1 ? "&" : "?";
    if (uri.match(re)) {
      return uri.replace(re, '$1' + key + "=" + value + '$2');
    } else {
      return uri + separator + key + "=" + value;
    }
  },

  // 通过正则表达式匹配url中的参数，如果匹配到了，就删除掉原来的参数
  removeURLParameters(url, ...parameters) {
    const parsed = new URL(url);
    parameters.forEach(e => parsed.searchParams.delete(e))
    return parsed.toString()
  },
}
