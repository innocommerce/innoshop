// Update URL parameter using regex, replace if exists, add if not
export function updateQueryStringParameter(uri, key, value) {
  var re = new RegExp("([?&])" + key + "=.*?(&|$)", "i");
  var separator = uri.indexOf('?') !== -1 ? "&" : "?";
  if (uri.match(re)) {
    return uri.replace(re, '$1' + key + "=" + value + '$2');
  } else {
    return uri + separator + key + "=" + value;
  }
}

// Remove URL parameters using regex
export function removeURLParameters(url, ...parameters) {
  const parsed = new URL(url);
  parameters.forEach(e => parsed.searchParams.delete(e))
  return parsed.toString()
}

// Get URL parameter by name
export function getQueryString(name, url = window.location.href) {
  var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)", "i");
  var r = url.split('?')[1] ? url.split('?')[1].match(reg) : null;
  if (r != null) return unescape(r[2]);
  return null;
}
