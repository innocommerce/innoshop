/**
 * DOM-derived panel runtime config (tokens, locale, editor language).
 */

export function getPanelConfig() {
  return {
    base: document.querySelector('base')?.href || window.location.origin,
    editorLanguage: document.querySelector('meta[name="editor_language"]')?.content || 'zh_cn',
    apiToken:
      typeof $ !== 'undefined'
        ? $('meta[name="api-token"]').attr('content') ||
          $(window.parent.document).find('meta[name="api-token"]').attr('content')
        : '',
    csrfToken: typeof $ !== 'undefined' ? $('meta[name="csrf-token"]').attr('content') : '',
    locale: typeof $ !== 'undefined' ? $('html').attr('lang') : 'en',
  };
}
