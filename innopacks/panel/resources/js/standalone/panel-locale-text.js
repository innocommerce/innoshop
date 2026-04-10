/**
 * InnoPanelLocaleText — reusable multi-locale string map editor (panel).
 * Included via standalone/index.js → panel-standalone.js; not part of app.js.
 * Load before panel-inno-panel.js; registered via InnoPanel.installVue().
 */
(function (global) {
  "use strict";

  if (typeof Vue === "undefined") {
    return;
  }

  const { defineComponent, reactive, ref, computed, toRefs } = Vue;

  function mergeLocaleMap(src, localeList) {
    const out = {};
    if (src && typeof src === "object") {
      Object.keys(src).forEach(function (k) {
        const v = src[k];
        out[k] = v == null ? "" : String(v);
      });
    }
    (localeList || []).forEach(function (loc) {
      const c = loc.code;
      if (out[c] === undefined) {
        out[c] = "";
      }
    });
    return out;
  }

  function previewText(map, panelLocale, fallbackLocale, localeList) {
    const m = map || {};
    const order = [panelLocale, fallbackLocale];
    for (let i = 0; i < order.length; i++) {
      const c = order[i];
      if (c && m[c] !== undefined && String(m[c]).trim() !== "") {
        return String(m[c]).trim();
      }
    }
    for (let j = 0; j < (localeList || []).length; j++) {
      const c = localeList[j].code;
      if (m[c] !== undefined && String(m[c]).trim() !== "") {
        return String(m[c]).trim();
      }
    }
    return "";
  }

  const InnoPanelLocaleText = defineComponent({
    name: "InnoPanelLocaleText",
    props: {
      modelValue: { type: Object, default: () => ({}) },
      localeList: { type: Array, default: () => [] },
      panelLocale: { type: String, default: "" },
      fallbackLocale: { type: String, default: "" },
      placeholder: { type: String, default: "" },
      editLabel: { type: String, default: "Edit all languages" },
      dialogTitle: { type: String, default: "Translations" },
      dialogHint: { type: String, default: "" },
      /** Optional line below the readonly preview (e.g. field help); empty = hidden */
      previewHint: { type: String, default: "" },
      /**
       * When the resolved preview from modelValue is empty, show this in the readonly field
       * (e.g. entity title above the editor). Not written to modelValue.
       */
      emptyPreviewFallback: { type: String, default: "" },
      confirmLabel: { type: String, default: "OK" },
      cancelLabel: { type: String, default: "Cancel" },
      disabled: { type: Boolean, default: false },
    },
    emits: ["update:modelValue", "change"],
    setup(props, { emit }) {
      const visible = ref(false);
      const draft = reactive({});

      const preview = computed(function () {
        return previewText(
          props.modelValue,
          props.panelLocale,
          props.fallbackLocale,
          props.localeList,
        );
      });

      const displayPreview = computed(function () {
        const p = preview.value;
        if (p !== "") {
          return p;
        }
        const fb =
          props.emptyPreviewFallback != null ? String(props.emptyPreviewFallback).trim() : "";
        return fb;
      });

      const isPreviewFromFallback = computed(function () {
        return (
          preview.value === "" &&
          props.emptyPreviewFallback != null &&
          String(props.emptyPreviewFallback).trim() !== ""
        );
      });

      function clearDraft() {
        Object.keys(draft).forEach(function (k) {
          delete draft[k];
        });
      }

      function openDialog() {
        if (props.disabled) {
          return;
        }
        clearDraft();
        Object.assign(draft, mergeLocaleMap(props.modelValue, props.localeList));
        visible.value = true;
      }

      function onConfirm() {
        const out = mergeLocaleMap(draft, props.localeList);
        emit("update:modelValue", out);
        emit("change", out);
        visible.value = false;
      }

      function onCancel() {
        visible.value = false;
      }

      function onFlagError(e) {
        if (e && e.target) {
          e.target.style.display = "none";
        }
      }

      return Object.assign({}, toRefs(props), {
        visible: visible,
        draft: draft,
        preview: preview,
        displayPreview: displayPreview,
        isPreviewFromFallback: isPreviewFromFallback,
        openDialog: openDialog,
        onConfirm: onConfirm,
        onCancel: onCancel,
        onFlagError: onFlagError,
      });
    },
    template:
      '<div class="inno-panel-locale-text">' +
      '<div class="inno-panel-locale-text__preview-row d-flex align-items-stretch min-w-0 w-100">' +
      '<el-tooltip placement="top" :content="displayPreview" :disabled="!displayPreview" :show-after="200" effect="dark">' +
      '<div class="inno-panel-locale-text__preview-tooltip flex-grow-1 min-w-0">' +
      '<el-input class="inno-panel-locale-text__preview" :class="{ \'inno-panel-locale-text__preview--fallback\': isPreviewFromFallback }" :model-value="displayPreview" readonly :placeholder="placeholder" :disabled="disabled">' +
      '<template v-slot:append>' +
      '<el-button class="inno-panel-locale-text__edit-btn" @click="openDialog" :disabled="disabled">{{ editLabel }}</el-button>' +
      "</template>" +
      "</el-input>" +
      "</div>" +
      "</el-tooltip>" +
      "</div>" +
      '<p v-if="previewHint" class="inno-panel-locale-text__preview-hint small text-muted mt-2 mb-0 lh-base">{{ previewHint }}</p>' +
      '<el-dialog v-model="visible" :title="dialogTitle" width="640px" append-to-body destroy-on-close align-center class="inno-panel-locale-text__dialog">' +
      '<p v-if="dialogHint" class="inno-panel-locale-text__hint small mb-3">{{ dialogHint }}</p>' +
      '<div v-for="loc in localeList" :key="loc.code" class="input-group mb-2 inno-panel-locale-text__dialog-row align-items-stretch" :title="loc.name + \' (\' + loc.code + \')\'">' +
      '<span class="input-group-text inno-panel-locale-text__lang-prefix">' +
      '<span class="d-flex align-items-center gap-2 min-w-0 inno-panel-locale-text__lang-label">' +
      '<span class="d-flex align-items-center justify-content-center flex-shrink-0 inno-panel-locale-text__lang-flag">' +
      '<img v-if="loc.image" :src="loc.image" class="img-fluid rounded border" width="22" height="16" :alt="loc.name" loading="lazy" @error="onFlagError" />' +
      "</span>" +
      '<span class="inno-panel-locale-text__lang-name text-truncate">{{ loc.name }}</span>' +
      "</span>" +
      "</span>" +
      '<div class="flex-grow-1 min-w-0 inno-panel-locale-text__dialog-field-wrap">' +
      '<el-input v-model="draft[loc.code]" :placeholder="placeholder" class="inno-panel-locale-text__dialog-field" />' +
      "</div>" +
      "</div>" +
      '<template #footer>' +
      '<span class="dialog-footer inno-panel-locale-text__footer">' +
      '<el-button @click="onCancel">{{ cancelLabel }}</el-button>' +
      '<el-button class="inno-panel-locale-text__confirm-btn" @click="onConfirm">{{ confirmLabel }}</el-button>' +
      "</span>" +
      "</template>" +
      "</el-dialog>" +
      "</div>",
  });

  global.InnoPanelLocaleText = InnoPanelLocaleText;
})(typeof window !== "undefined" ? window : globalThis);
