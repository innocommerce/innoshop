/**
 * InnoPanel — Layer C: InnoLinkPicker + installVue + LINK_TYPE_OPTIONS.
 * Included via standalone/index.js → panel-standalone.js; not part of app.js.
 * Depends on: panel-entity-autocomplete.js, panel-entity-picker.js (before this file).
 * Optional: panel-locale-text.js (InnoPanelLocaleText) before this file.
 */
(function (global) {
  "use strict";

  if (typeof Vue === "undefined") {
    return;
  }

  const { defineComponent, ref, computed, watch } = Vue;

  const DEFAULT_LINK_TYPE_OPTIONS = [
    { value: "custom", label: "Custom URL" },
    { value: "product", label: "Product" },
    { value: "category", label: "Category" },
    { value: "brand", label: "Brand" },
    { value: "page", label: "Page" },
    { value: "article", label: "Article" },
    { value: "catalog", label: "Catalog" },
  ];

  const InnoLinkPicker = defineComponent({
    name: "InnoLinkPicker",
    props: {
      modelValue: { type: Object, default: null },
      allowedTypes: { type: Array, default: () => [] },
      /** Full list of { value, label }; when empty, DEFAULT_LINK_TYPE_OPTIONS is used */
      linkTypeOptions: { type: Array, default: () => [] },
      placeholderType: { type: String, default: "" },
      placeholderCustomUrl: { type: String, default: "" },
      pickerHint: { type: String, default: "" },
      pickerPlaceholder: { type: String, default: "" },
      pickerSearchButtonLabel: { type: String, default: undefined },
      pickerConfirmButtonLabel: { type: String, default: undefined },
      pickerEmptyText: { type: String, default: undefined },
      /** Use :type as placeholder for the current entity type label, e.g. "Select :type" */
      pickerTitleTemplate: { type: String, default: "Select :type" },
      chooseEntityLabel: { type: String, default: "Choose" },
      changeEntityLabel: { type: String, default: "Change" },
      clearEntityLabel: { type: String, default: "Clear" },
    },
    emits: ["update:modelValue", "change"],
    setup(props, { emit }) {
      const baseOptions = computed(function () {
        return props.linkTypeOptions && props.linkTypeOptions.length
          ? props.linkTypeOptions
          : DEFAULT_LINK_TYPE_OPTIONS;
      });

      const typeOptions = computed(function () {
        const allowed = props.allowedTypes;
        if (!allowed || !allowed.length) {
          return baseOptions.value;
        }
        return baseOptions.value.filter(function (o) {
          return allowed.indexOf(o.value) !== -1;
        });
      });

      const selectedType = ref("custom");
      const customUrl = ref("");
      const selectedEntity = ref(null);
      const pickerVisible = ref(false);

      function syncFromModel(val) {
        if (!val) {
          selectedType.value = "custom";
          customUrl.value = "";
          selectedEntity.value = null;
          return;
        }
        const t = val.type || "custom";
        selectedType.value = t;
        if (t === "custom") {
          customUrl.value = val.url || "";
          selectedEntity.value = null;
          return;
        }
        customUrl.value = "";
        const id = val.id;
        const hasId = id !== undefined && id !== null && id !== "";
        selectedEntity.value = hasId
          ? {
              id: id,
              name: val.name || "",
              url: val.url || "",
              image: val.image || val.entity_image || "",
              price_label: val.price_label || val.entity_price || "",
              sku_code: val.sku_code || val.entity_sku || "",
            }
          : null;
      }

      watch(
        function () {
          return props.modelValue;
        },
        syncFromModel,
        { deep: true, immediate: true },
      );

      const isCustom = computed(function () {
        return selectedType.value === "custom";
      });

      const currentTypeLabel = computed(function () {
        const opt = typeOptions.value.find(function (o) {
          return o.value === selectedType.value;
        });
        return opt ? opt.label : "";
      });

      const pickerDialogTitle = computed(function () {
        return props.pickerTitleTemplate.replace(":type", currentTypeLabel.value || "");
      });

      function emitValue() {
        let val = null;
        if (isCustom.value) {
          val = { type: "custom", id: null, name: null, url: (customUrl.value || "").trim() };
        } else {
          if (selectedEntity.value) {
            val = {
              type: selectedType.value,
              id: selectedEntity.value.id,
              name: selectedEntity.value.name,
              url: selectedEntity.value.url || "",
              image: selectedEntity.value.image || "",
              price_label: selectedEntity.value.price_label || "",
              sku_code: selectedEntity.value.sku_code || "",
            };
          } else {
            val = null;
          }
        }
        emit("update:modelValue", val);
        emit("change", val);
      }

      function onTypeChange(val) {
        selectedType.value = val;
        selectedEntity.value = null;
        customUrl.value = "";
        if (val !== "custom") {
          pickerVisible.value = true;
          emit("update:modelValue", { type: val, id: null, name: null, url: "", image: "", price_label: "", sku_code: "" });
          emit("change", { type: val, id: null, name: null, url: "", image: "", price_label: "", sku_code: "" });
        } else {
          emitValue();
        }
      }

      function onEntitySelect(item) {
        if (!item) {
          return;
        }
        selectedEntity.value = {
          id: item.id,
          name: item.name,
          url: item.url != null ? item.url : "",
          image: item.image != null ? item.image : "",
          price_label: item.price_label != null ? item.price_label : "",
          sku_code: item.sku_code != null ? item.sku_code : "",
        };
        pickerVisible.value = false;
        emitValue();
      }

      function onCustomUrlInput(v) {
        customUrl.value = v;
        emitValue();
      }

      function reselect() {
        pickerVisible.value = true;
      }

      function clear() {
        selectedEntity.value = null;
        emitValue();
      }

      return {
        selectedType: selectedType,
        customUrl: customUrl,
        selectedEntity: selectedEntity,
        pickerVisible: pickerVisible,
        typeOptions: typeOptions,
        isCustom: isCustom,
        pickerDialogTitle: pickerDialogTitle,
        onTypeChange: onTypeChange,
        onEntitySelect: onEntitySelect,
        onCustomUrlInput: onCustomUrlInput,
        reselect: reselect,
        clear: clear,
        placeholderType: computed(function () {
          return props.placeholderType;
        }),
        placeholderCustomUrl: computed(function () {
          return props.placeholderCustomUrl;
        }),
        pickerHint: computed(function () {
          return props.pickerHint;
        }),
        pickerPlaceholder: computed(function () {
          return props.pickerPlaceholder;
        }),
        pickerSearchButtonLabel: computed(function () {
          return props.pickerSearchButtonLabel;
        }),
        pickerConfirmButtonLabel: computed(function () {
          return props.pickerConfirmButtonLabel;
        }),
        pickerEmptyText: computed(function () {
          return props.pickerEmptyText;
        }),
        chooseEntityLabel: computed(function () {
          return props.chooseEntityLabel;
        }),
        changeEntityLabel: computed(function () {
          return props.changeEntityLabel;
        }),
        clearEntityLabel: computed(function () {
          return props.clearEntityLabel;
        }),
        emitValue: emitValue,
      };
    },
    template:
      '<div class="inno-link-picker">' +
      '<el-select :model-value="selectedType" class="w-100 mb-2" :placeholder="placeholderType" @change="onTypeChange">' +
      '<el-option v-for="opt in typeOptions" :key="opt.value" :label="opt.label" :value="opt.value" />' +
      "</el-select>" +
      '<el-input v-if="isCustom" :model-value="customUrl" :placeholder="placeholderCustomUrl" @input="onCustomUrlInput" />' +
      "<template v-else>" +
      '<div v-if="selectedEntity" class="inno-link-picker__selected d-flex align-items-start gap-2 w-100">' +
      '<img v-if="selectedEntity.image" :src="selectedEntity.image" alt="" class="rounded border flex-shrink-0 align-self-start" width="44" height="44" style="object-fit:cover" />' +
      '<div class="inno-link-picker__entity-meta flex-grow-1 min-w-0">' +
      '<el-tooltip placement="top" :content="selectedEntity.name" :disabled="!selectedEntity.name" :show-after="200" effect="dark">' +
      '<div class="fw-medium text-truncate">{{ selectedEntity.name }}</div>' +
      "</el-tooltip>" +
      '<el-tooltip v-if="selectedEntity.sku_code" placement="top" :content="\'SKU: \' + selectedEntity.sku_code" :show-after="200" effect="dark">' +
      '<div class="text-muted small text-truncate">SKU: {{ selectedEntity.sku_code }}</div>' +
      "</el-tooltip>" +
      '<el-tooltip v-if="selectedEntity.price_label" placement="top" :content="selectedEntity.price_label" :show-after="200" effect="dark">' +
      '<div class="text-primary small text-truncate">{{ selectedEntity.price_label }}</div>' +
      "</el-tooltip>" +
      '<el-tooltip v-if="selectedEntity.url" placement="top" :content="selectedEntity.url" :show-after="200" effect="dark">' +
      '<span class="text-muted small text-truncate d-block">{{ selectedEntity.url }}</span>' +
      "</el-tooltip>" +
      "</div>" +
      '<div class="inno-link-picker__actions d-flex flex-shrink-0 gap-1 align-items-start align-self-start">' +
      '<el-button type="primary" link size="small" @click="reselect">{{ changeEntityLabel }}</el-button>' +
      '<el-button type="danger" link size="small" @click="clear">{{ clearEntityLabel }}</el-button>' +
      "</div>" +
      "</div>" +
      '<el-button v-else size="small" type="primary" plain @click="reselect">{{ chooseEntityLabel }}</el-button>' +
      "</template>" +
      '<panel-entity-picker-dialog v-model="pickerVisible" :entity-type="selectedType" :title="pickerDialogTitle" :hint="pickerHint" :placeholder="pickerPlaceholder" :search-button-text="pickerSearchButtonLabel" :confirm-button-text="pickerConfirmButtonLabel" :empty-text="pickerEmptyText" @select="onEntitySelect" />' +
      "</div>",
  });

  function installVue(app) {
    if (global.PanelEntityPickerDialog) {
      app.component("PanelEntityPickerDialog", global.PanelEntityPickerDialog);
    }
    app.component("InnoLinkPicker", InnoLinkPicker);
    if (global.InnoPanelLocaleText) {
      app.component("InnoPanelLocaleText", global.InnoPanelLocaleText);
    }
    if (global.InnoPanelImageField) {
      app.component("InnoPanelImageField", global.InnoPanelImageField);
    }
  }

  global.InnoPanel = global.InnoPanel || {};
  Object.assign(global.InnoPanel, {
    entityAutocomplete: global.InnoPanelEntityAutocomplete,
    installVue: installVue,
    components: {
      PanelEntityPickerDialog: global.PanelEntityPickerDialog,
      InnoLinkPicker: InnoLinkPicker,
      InnoPanelLocaleText: global.InnoPanelLocaleText,
      InnoPanelImageField: global.InnoPanelImageField,
    },
    LINK_TYPE_OPTIONS: DEFAULT_LINK_TYPE_OPTIONS,
  });
})(typeof window !== "undefined" ? window : globalThis);
