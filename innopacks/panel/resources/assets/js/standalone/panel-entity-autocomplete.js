/**
 * Panel entity autocomplete — shared Layer B (URL + fetch + normalize + debounce + abort).
 * Included via standalone/index.js → panel-standalone.js; not part of app.js.
 * Used by PanelEntityPickerDialog, InnoLinkPicker, and plugin pages.
 * Requires: global `urls.panel_api` (panel layout), meta api-token.
 */
(function (global) {
  "use strict";

  const PATHS = {
    product: "/products/autocomplete",
    category: "/categories/autocomplete",
    brand: "/brands/autocomplete",
    page: "/pages/autocomplete",
    article: "/articles/autocomplete",
    catalog: "/catalogs/autocomplete",
  };

  function getPanelBaseUrl() {
    return typeof urls !== "undefined" && urls.panel_api ? urls.panel_api : "";
  }

  function getPanelApiHeaders() {
    const h = {
      Accept: "application/json",
      "X-Requested-With": "XMLHttpRequest",
    };
    const t = document.querySelector('meta[name="api-token"]')?.getAttribute("content");
    if (t) {
      h.Authorization = "Bearer " + t;
    }
    const locale = document.querySelector('meta[name="locale"]')?.getAttribute("content");
    if (locale) {
      h["Accept-Language"] = locale;
    }
    return h;
  }

  function buildAutocompleteUrl(entityType, keyword) {
    const path = PATHS[entityType];
    if (!path) {
      if (typeof console !== "undefined" && console.warn) {
        console.warn('[InnoPanel] Unknown entity type: "' + entityType + '". Register it in PATHS.');
      }
      return null;
    }
    const base = getPanelBaseUrl();
    if (!base) {
      return null;
    }
    return base + path + "?keyword=" + encodeURIComponent(keyword || "");
  }

  function parseAutocompleteBody(data) {
    if (data == null) {
      return [];
    }
    if (Array.isArray(data)) {
      return data;
    }
    if (Array.isArray(data.data)) {
      return data.data;
    }
    return [];
  }

  /**
   * Best-effort thumbnail for list row (entity APIs differ by resource).
   * @param {*} item
   * @returns {string|null}
   */
  function pickImage(item) {
    if (!item || typeof item !== "object") {
      return null;
    }
    const u =
      item.image_small ||
      item.image_big ||
      item.image ||
      item.logo_url ||
      item.logo_small ||
      item.logo_medium ||
      item.logo_large ||
      item.thumb ||
      null;
    return u || null;
  }

  /**
   * @param {*} item
   * @returns {string|null}
   */
  function pickPriceLabel(item) {
    if (!item || typeof item !== "object") {
      return null;
    }
    const p = item.price_format || item.origin_price_format || item.price || null;
    return p != null && String(p).trim() !== "" ? String(p) : null;
  }

  /**
   * @param {*} item
   * @returns {string}
   */
  function pickSubtitle(item) {
    if (!item || typeof item !== "object") {
      return "";
    }
    const s = item.summary || item.description || "";
    return typeof s === "string" ? s.trim() : "";
  }

  /**
   * Master SKU / product code for picker subtitle (e.g. ProductSimple.code).
   * @param {*} item
   * @returns {string}
   */
  function pickSkuCode(item) {
    if (!item || typeof item !== "object") {
      return "";
    }
    const c = item.code ?? item.sku_code ?? item.sku ?? "";
    if (c == null) {
      return "";
    }
    const s = typeof c === "string" ? c.trim() : String(c).trim();
    return s;
  }

  /**
   * @param {Array} rawList
   * @returns {Array<{ id: *, name: string, value: string, url: string|null, image: string|null, price_label: string|null, subtitle: string, sku_code: string, raw: * }>}
   */
  function normalizeList(rawList) {
    if (!Array.isArray(rawList)) {
      return [];
    }
    return rawList.map(function (item) {
      const name = item.name ?? item.title ?? item.label ?? String(item.id ?? "");
      const url = item.url ?? item.slug ?? null;
      const subtitle = pickSubtitle(item);
      const skuCode = pickSkuCode(item);
      return {
        id: item.id,
        name: name,
        value: name,
        url: url,
        image: pickImage(item),
        price_label: pickPriceLabel(item),
        subtitle: subtitle,
        sku_code: skuCode,
        raw: item,
      };
    });
  }

  const abortByType = {};

  function abortPrevious(entityType) {
    if (abortByType[entityType]) {
      abortByType[entityType].abort();
    }
    const ctrl = new AbortController();
    abortByType[entityType] = ctrl;
    return ctrl.signal;
  }

  /**
   * @param {string} entityType — key in PATHS (not "custom")
   * @param {string} keyword
   * @param {{ signal?: AbortSignal }} [options]
   * @returns {Promise<Array>}
   */
  async function fetchSuggestions(entityType, keyword, options) {
    const url = buildAutocompleteUrl(entityType, keyword);
    if (!url) {
      return [];
    }
    const signal = options && options.signal ? options.signal : abortPrevious(entityType);
    const res = await fetch(url, {
      credentials: "same-origin",
      headers: getPanelApiHeaders(),
      signal: signal,
    });
    const data = await res.json();
    const list = parseAutocompleteBody(data);
    return normalizeList(list);
  }

  const debounceTimers = {};

  /**
   * Adapter for el-autocomplete fetch-suggestions(queryString, cb).
   */
  async function querySearchAsync(entityType, queryString, cb) {
    clearTimeout(debounceTimers[entityType]);
    debounceTimers[entityType] = setTimeout(async function () {
      try {
        const list = await fetchSuggestions(entityType, queryString);
        cb(list);
      } catch (e) {
        if (e && e.name === "AbortError") {
          return;
        }
        cb([]);
      }
    }, 300);
  }

  const API = {
    PATHS: PATHS,
    getPanelBaseUrl: getPanelBaseUrl,
    getPanelApiHeaders: getPanelApiHeaders,
    buildAutocompleteUrl: buildAutocompleteUrl,
    parseAutocompleteBody: parseAutocompleteBody,
    normalizeList: normalizeList,
    fetchSuggestions: fetchSuggestions,
    querySearchAsync: querySearchAsync,
    pickImage: pickImage,
    pickPriceLabel: pickPriceLabel,
    pickSkuCode: pickSkuCode,
  };

  global.InnoPanelEntityAutocomplete = API;
  global.InnoPanel = global.InnoPanel || {};
  global.InnoPanel.entityAutocomplete = API;
})(typeof window !== "undefined" ? window : globalThis);
