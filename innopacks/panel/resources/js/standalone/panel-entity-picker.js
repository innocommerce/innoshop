/**
 * PanelEntityPickerDialog — Vue 3 + Element Plus (global builds).
 * Included via standalone/index.js → panel-standalone.js; not part of app.js.
 * Depends on: Vue, ElementPlus, InnoPanelEntityAutocomplete (load order in panel layout).
 */
(function () {
  "use strict";

  if (typeof Vue === "undefined") {
    return;
  }

  const { defineComponent, ref, watch, h, resolveComponent } = Vue;

  const SEARCH_DEBOUNCE_MS = 300;

  const PanelEntityPickerDialog = defineComponent({
    name: "PanelEntityPickerDialog",
    props: {
      modelValue: { type: Boolean, default: false },
      entityType: { type: String, required: true },
      title: { type: String, default: "" },
      hint: { type: String, default: "" },
      placeholder: { type: String, default: "" },
      searchButtonText: { type: String, default: "Search" },
      confirmButtonText: { type: String, default: "Confirm" },
      emptyText: { type: String, default: "" },
    },
    emits: ["update:modelValue", "select"],
    setup(props, { emit }) {
      const keyword = ref("");
      const list = ref([]);
      const loading = ref(false);
      const selected = ref(null);
      let debounceTimer = null;

      function clearDebounce() {
        if (debounceTimer !== null) {
          clearTimeout(debounceTimer);
          debounceTimer = null;
        }
      }

      function scheduleDebouncedSearch() {
        clearDebounce();
        debounceTimer = setTimeout(function () {
          debounceTimer = null;
          runSearch();
        }, SEARCH_DEBOUNCE_MS);
      }

      function runSearchImmediate() {
        clearDebounce();
        runSearch();
      }

      async function runSearch() {
        const api = window.InnoPanelEntityAutocomplete;
        if (!api || !props.entityType || props.entityType === "custom") {
          list.value = [];
          return;
        }
        loading.value = true;
        selected.value = null;
        try {
          list.value = await api.fetchSuggestions(props.entityType, keyword.value);
        } catch (e) {
          list.value = [];
        } finally {
          loading.value = false;
        }
      }

      function selectRow(item) {
        selected.value = item;
      }

      function onSelect(item) {
        if (!item) {
          return;
        }
        emit("select", {
          id: item.id,
          name: item.name,
          value: item.value,
          url: item.url != null ? item.url : null,
          image: item.image != null ? item.image : null,
          price_label: item.price_label != null ? item.price_label : null,
          subtitle: item.subtitle != null ? item.subtitle : null,
          sku_code: item.sku_code != null && item.sku_code !== "" ? item.sku_code : null,
        });
        emit("update:modelValue", false);
        keyword.value = "";
        list.value = [];
        selected.value = null;
      }

      function confirmSelection() {
        if (!selected.value) {
          return;
        }
        onSelect(selected.value);
      }

      function onSearchKeyup(e) {
        if (e && e.key === "Enter") {
          runSearchImmediate();
        }
      }

      watch(
        () => props.modelValue,
        function (v) {
          if (v) {
            clearDebounce();
            keyword.value = "";
            list.value = [];
            selected.value = null;
            runSearch();
          } else {
            clearDebounce();
          }
        },
      );

      return function renderPanelEntityPicker() {
        const ElDialog = resolveComponent("el-dialog");
        const ElInput = resolveComponent("el-input");
        const ElButton = resolveComponent("el-button");

        const emptyHint =
          props.emptyText ||
          "No matching items. Try another keyword.";

        function rowThumb(item) {
          return item.image
            ? h("img", {
                src: item.image,
                alt: "",
                class: "rounded border flex-shrink-0 panel-entity-picker-row__thumb",
              })
            : h(
                "div",
                {
                  class:
                    "rounded border bg-light flex-shrink-0 d-flex align-items-center justify-content-center text-secondary small user-select-none panel-entity-picker-row__thumb",
                },
                "—",
              );
        }

        function rowMetaLines(item) {
          const label = String(item.name || item.value || "");
          const lines = [h("div", { class: "text-truncate fw-medium" }, label)];
          const skuLine =
            item.sku_code && String(item.sku_code).trim() !== ""
              ? "SKU: " + String(item.sku_code)
              : item.subtitle && String(item.subtitle) !== label
                ? String(item.subtitle)
                : "";
          if (skuLine) {
            lines.push(h("div", { class: "text-muted small text-truncate" }, skuLine));
          }
          return lines;
        }

        function rowVNode(item) {
          const isSel = selected.value && selected.value.id === item.id;
          return h(
            "div",
            {
              key: item.id,
              class: [
                "panel-entity-picker-row d-flex align-items-center gap-2 py-2 px-2",
                { "panel-entity-picker-row--selected": isSel },
              ],
              onClick: function () {
                selectRow(item);
              },
            },
            [
              h("input", {
                type: "checkbox",
                class: "form-check-input flex-shrink-0 mt-0 panel-entity-picker-row__check",
                checked: isSel,
                onClick: function (e) {
                  e.stopPropagation();
                  selectRow(item);
                },
              }),
              rowThumb(item),
              h("div", { class: "flex-grow-1 min-w-0" }, rowMetaLines(item)),
              item.price_label
                ? h(
                    "div",
                    {
                      class: "flex-shrink-0 fw-semibold panel-entity-picker-row__price text-end",
                    },
                    String(item.price_label),
                  )
                : h("div", { class: "flex-shrink-0" }, ""),
            ],
          );
        }

        let scrollChildren;
        if (loading.value && (!list.value || !list.value.length)) {
          scrollChildren = [
            h("div", { class: "text-center text-muted py-5 small" }, "…"),
          ];
        } else if (list.value && list.value.length) {
          scrollChildren = list.value.map(function (it) {
            return rowVNode(it);
          });
        } else {
          scrollChildren = [
            h("div", { class: "text-center text-muted py-5 small" }, emptyHint),
          ];
        }

        const children = [];

        if (props.hint) {
          children.push(h("p", { class: "text-muted small mb-2" }, props.hint));
        }

        children.push(
          h("div", { class: "d-flex gap-2 mb-3 panel-entity-picker-search" }, [
            h(ElInput, {
              modelValue: keyword.value,
              "onUpdate:modelValue": function (v) {
                keyword.value = v;
                scheduleDebouncedSearch();
              },
              clearable: true,
              placeholder: props.placeholder,
              class: "flex-grow-1",
              onKeyup: onSearchKeyup,
            }),
            h(ElButton, {
              type: "primary",
              onClick: runSearchImmediate,
            }, props.searchButtonText),
          ]),
        );

        children.push(
          h(
            "div",
            {
              class: "panel-entity-picker-list border rounded position-relative",
            },
            [
              loading.value
                ? h("div", {
                    class: "position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center bg-white bg-opacity-75",
                    style: { zIndex: 1 },
                  })
                : null,
              h(
                "div",
                {
                  class: "panel-entity-picker-list__scroll",
                },
                scrollChildren,
              ),
            ],
          ),
        );

        children.push(
          h("div", { class: "mt-3 pt-2 border-top" }, [
            h(ElButton, {
              type: "success",
              class: "w-100",
              disabled: !selected.value,
              onClick: confirmSelection,
            }, props.confirmButtonText),
          ]),
        );

        return h(
          ElDialog,
          {
            modelValue: props.modelValue,
            "onUpdate:modelValue": function (v) {
              emit("update:modelValue", v);
            },
            title: props.title,
            width: "600px",
            destroyOnClose: true,
            appendToBody: true,
            class: "panel-entity-picker-dialog",
          },
          {
            default: function () {
              return children;
            },
          },
        );
      };
    },
  });

  window.PanelEntityPickerDialog = PanelEntityPickerDialog;
})();
