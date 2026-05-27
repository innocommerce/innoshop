/**
 * InnoPanelImageField — Vue 3 + Element Plus (global build).
 * Included via standalone/index.js → panel-standalone.js; not part of app.js.
 * Depends on: Vue, Element Plus, inno (app.js), layer.
 */
(function (global) {
  "use strict";

  if (typeof Vue === "undefined") {
    return;
  }

  const { defineComponent, computed, toRefs } = Vue;

  function assetBaseUrl() {
    const meta = document.querySelector('meta[name="asset"]');
    return meta && meta.content ? meta.content.replace(/\/$/, "") : "";
  }

  function storageBaseUrl() {
    const meta = document.querySelector('meta[name="storage-base-url"]');
    return meta && meta.content ? meta.content.replace(/\/$/, "") : "";
  }

  /**
   * The storage prefix added by StorageService (matches PHP STORAGE_PREFIX).
   * Stored paths from file manager look like "static/media/catalog/img.png".
   */
  var _storagePrefix = "static/media/";

  function previewUrlFromStoredPath(path) {
    if (!path || typeof path !== "string") {
      return "";
    }
    const p = path.trim();
    if (!p) {
      return "";
    }
    if (p.startsWith("http://") || p.startsWith("https://")) {
      return p;
    }
    var normalized = p.replace(/^\/+/, "");

    // Paths that already carry the storage prefix (e.g. "static/media/catalog/img.png")
    // should be resolved via the asset base URL, NOT via storageBaseUrl which would
    // duplicate the prefix.
    if (normalized.startsWith(_storagePrefix)) {
      var assetBase = assetBaseUrl();
      return assetBase ? assetBase + "/" + normalized : "/" + normalized;
    }

    // Bare keys (e.g. "catalog/img.png") — prepend storage base URL
    var sBase = storageBaseUrl();
    if (sBase) {
      return sBase + "/" + normalized;
    }
    return "/" + normalized;
  }

  function storedPathFromFileManager(file) {
    if (!file || typeof file !== "object") {
      return "";
    }
    if (typeof file.path === "string" && file.path.trim() !== "") {
      return file.path.trim().replace(/^\/+/, "");
    }
    const raw = file.url || file.origin_url;
    if (typeof raw === "string" && raw.trim() !== "") {
      if (raw.startsWith("http://") || raw.startsWith("https://")) {
        try {
          const u = new URL(raw);
          return u.pathname.replace(/^\/+/, "");
        } catch (e) {
          return "";
        }
      }
      return raw.replace(/^\/+/, "");
    }
    return "";
  }

  const InnoPanelImageField = defineComponent({
    name: "InnoPanelImageField",
    props: {
      modelValue: { type: String, default: "" },
      browseLabel: { type: String, default: "File library" },
      clearLabel: { type: String, default: "Clear" },
      pathPlaceholder: { type: String, default: "" },
      /** Show manual path input */
      showManualPath: { type: Boolean, default: true },
      /** Smaller preview + tighter layout for dense grids */
      compact: { type: Boolean, default: false },
    },
    emits: ["update:modelValue", "change"],
    setup(props, { emit }) {
      const propRefs = toRefs(props);

      const previewSrc = computed(function () {
        return previewUrlFromStoredPath(props.modelValue || "");
      });

      function emitPath(next) {
        const v = next == null ? "" : String(next);
        emit("update:modelValue", v);
        emit("change", v);
      }

      function openFileManager() {
        if (typeof global.inno === "undefined" || typeof global.inno.fileManagerIframe !== "function") {
          if (typeof layer !== "undefined") {
            layer.msg("File manager is not available on this page.");
          }
          return;
        }
        global.inno.fileManagerIframe(function (file) {
          if (!file) {
            return;
          }
          const path = storedPathFromFileManager(file);
          if (path) {
            emitPath(path);
          }
        }, { type: "image", multiple: false });
      }

      function clearImage() {
        emitPath("");
      }

      function onManualInput(v) {
        emitPath(v);
      }

      return Object.assign({}, propRefs, {
        previewSrc,
        openFileManager,
        clearImage,
        onManualInput,
      });
    },
    template:
      '<div class="inno-panel-image-field" :class="{ \'inno-panel-image-field--compact\': compact }">' +
      '<div class="d-flex flex-wrap align-items-start gap-2">' +
      '<div class="flex-shrink-0">' +
      '<div class="inno-panel-image-field__thumb position-relative rounded border bg-light overflow-hidden d-flex align-items-center justify-content-center cursor-pointer" :class="compact ? \'inno-panel-image-field__thumb--sm\' : \'wh-80\'" :style="compact ? \'min-width:56px;min-height:56px;width:56px;height:56px\' : \'min-width:80px;min-height:80px\'" @click="openFileManager" role="button" tabindex="0" @keydown.enter.prevent="openFileManager">' +
      '<template v-if="previewSrc">' +
      '<img :src="previewSrc" class="img-fluid w-100 h-100" alt="" style="object-fit:cover" />' +
      "</template>" +
      "<template v-else>" +
      '<span class="text-secondary opacity-75"><i class="bi" :class="compact ? \'bi-image fs-4\' : \'bi-image fs-2\'"></i></span>' +
      "</template>" +
      "</div>" +
      "</div>" +
      '<div class="d-flex flex-column align-items-stretch gap-1 flex-grow-1 min-w-0">' +
      '<div class="d-flex flex-wrap gap-1">' +
      '<el-button size="small" type="primary" plain @click="openFileManager">{{ browseLabel }}</el-button>' +
      '<el-button v-if="modelValue" size="small" type="danger" link @click="clearImage">{{ clearLabel }}</el-button>' +
      "</div>" +
      '<el-input v-if="showManualPath" class="mt-1" size="small" :model-value="modelValue" :placeholder="pathPlaceholder" @update:model-value="onManualInput" clearable />' +
      "</div>" +
      "</div>" +
      "</div>",
  });

  global.InnoPanelImageField = InnoPanelImageField;
})(typeof window !== "undefined" ? window : globalThis);
