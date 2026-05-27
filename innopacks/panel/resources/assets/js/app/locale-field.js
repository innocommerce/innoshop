/**
 * locale-field.js — Multi-language field Modal behavior.
 * Handles smart fill (translate or copy), per-language translate, sync between modal and main form,
 * and frontend default locale validation.
 */
function getPanelBaseUrl() {
  if (typeof urls !== "undefined" && urls.panel_base) {
    return urls.panel_base;
  }
  return "";
}

const localeField = {
  init() {
    this.bindEditAllButton();
    this.bindSmartFillButton();
    this.bindTranslateButtons();
    this.bindModalSync();
    this.bindFrontLocaleHintClick();
    this.bindFormValidation();
    this.bindPrimaryInputChange();
  },

  /**
   * Bind "edit all languages" button to open modal programmatically.
   */
  bindEditAllButton() {
    $(document).on("click", ".locale-edit-all-btn", function () {
      const modalId = $(this).data("modal-id");
      if (modalId) {
        $("#" + modalId).modal("show");
      }
    });
  },

  /**
   * Enable/disable translate buttons based on whether the primary locale has text.
   */
  toggleButtonsState($modal) {
    const $wrapper = $modal.closest(".locale-field-wrapper");
    const primaryLocale = $wrapper.data("panel-locale");
    const $primaryInput = $modal.find('.locale-modal-row[data-locale="' + primaryLocale + '"] input.form-control, .locale-modal-row[data-locale="' + primaryLocale + '"] textarea.form-control');
    const hasText = !!($primaryInput.val() || "").trim();

    // Only disable the footer smart-fill button; per-row buttons handle empty source gracefully
    $modal.find(".locale-smart-fill-btn").prop("disabled", !hasText);
  },

  /**
   * Listen for changes on the primary locale input inside modal to toggle buttons.
   */
  bindPrimaryInputChange() {
    $(document).on("input", ".locale-field-wrapper .modal .locale-modal-row input.form-control, .locale-field-wrapper .modal .locale-modal-row textarea.form-control", function () {
      const $modal = $(this).closest(".modal");
      const $wrapper = $modal.closest(".locale-field-wrapper");
      const primaryLocale = $wrapper.data("panel-locale");
      const rowLocale = $(this).closest(".locale-modal-row").data("locale");

      // Only re-evaluate when the primary locale row changes
      if (rowLocale === primaryLocale) {
        localeField.toggleButtonsState($modal);
      }
    });
  },

  /**
   * Sync values between main form input and modal inputs.
   * - On modal show: copy main form primary value → modal primary input
   * - On modal close (confirm/close): copy modal primary value → main form input
   */
  bindModalSync() {
    $(document).on("show.bs.modal", ".locale-field-wrapper .modal", function () {
      const $modal = $(this);
      const $wrapper = $modal.closest(".locale-field-wrapper");
      const primaryLocale = $wrapper.data("panel-locale");

      // Sync main form → modal primary input
      const mainValue = $wrapper.find("> .input-group .locale-primary-input").val() || "";
      const $modalPrimaryRow = $modal.find('.locale-modal-row[data-locale="' + primaryLocale + '"]');
      $modalPrimaryRow.find("input.form-control, textarea.form-control").val(mainValue);

      // Enable/disable translate buttons based on primary text
      localeField.toggleButtonsState($modal);
    });

    $(document).on("hidden.bs.modal", ".locale-field-wrapper .modal", function () {
      const $modal = $(this);
      const $wrapper = $modal.closest(".locale-field-wrapper");
      const primaryLocale = $wrapper.data("panel-locale");
      const frontLocale = $wrapper.data("front-locale");

      // Sync modal primary input → main form
      const $modalPrimaryRow = $modal.find('.locale-modal-row[data-locale="' + primaryLocale + '"]');
      const modalValue = $modalPrimaryRow.find("input.form-control, textarea.form-control").val() || "";
      $wrapper.find("> .input-group .locale-primary-input").val(modalValue);

      // Update front locale hint
      if (frontLocale && frontLocale !== primaryLocale) {
        const $frontRow = $modal.find('.locale-modal-row[data-locale="' + frontLocale + '"]');
        const frontValue = $frontRow.find("input.form-control, textarea.form-control").val() || "";
        localeField.updateFrontHint($wrapper, frontValue);
      }
    });
  },

  /**
   * Update the front locale hint status (filled/empty).
   */
  updateFrontHint($wrapper, value) {
    const $hint = $wrapper.find(".locale-front-hint");
    if ($hint.length === 0) return;

    const hasValue = value && value.trim();
    $hint.toggleClass("locale-front-filled", !!hasValue).toggleClass("locale-front-empty", !hasValue);
    $hint.find(".locale-front-hint-filled").toggle(!!hasValue);
    $hint.find(".locale-front-hint-empty").toggle(!hasValue);
  },

  /**
   * Click on the empty front locale hint to open the modal.
   */
  bindFrontLocaleHintClick() {
    $(document).on("click", ".locale-front-hint.locale-front-empty", function () {
      const $wrapper = $(this).closest(".locale-field-wrapper");
      const modalId = $wrapper.data("modal-id");
      if (modalId) {
        $("#" + modalId).modal("show");
      }
    });
  },

  /**
   * Validate frontend default locale on form submit.
   */
  bindFormValidation() {
    $(document).on("submit", "form", function () {
      const $form = $(this);
      let firstEmpty = null;

      $form.find(".locale-field-wrapper[data-front-required]").each(function () {
        const $wrapper = $(this);
        const frontLocale = $wrapper.data("front-locale");
        const modalId = $wrapper.data("modal-id");
        const fieldLabel = $wrapper.data("field-label");

        if (!frontLocale || !modalId) return;

        // Check the modal's front locale input value; fall back to primary input when no modal exists (single language)
        let $frontInput = $wrapper.find('.locale-modal-row[data-locale="' + frontLocale + '"] input.form-control, .locale-modal-row[data-locale="' + frontLocale + '"] textarea.form-control');
        if ($frontInput.length === 0) {
          $frontInput = $wrapper.find("> .input-group .locale-primary-input");
        }
        const val = $frontInput.val() || "";

        if (!val.trim()) {
          if (!firstEmpty) {
            firstEmpty = { $wrapper, modalId, fieldLabel, frontLocale };
          }
        }
      });

      if (firstEmpty) {
        // Show error and open modal
        const msg = (typeof panelLocaleMessages !== "undefined" && panelLocaleMessages.frontRequired)
          ? panelLocaleMessages.frontRequired.replace(":locale", firstEmpty.frontLocale).replace(":field", firstEmpty.fieldLabel)
          : `Please fill in the storefront default language (${firstEmpty.frontLocale}) ${firstEmpty.fieldLabel}.`;

        if (window.inno && window.inno.alert) {
          window.inno.alert({ msg: msg, type: "danger" });
        }

        // Open modal and highlight the empty row
        const $modal = $("#" + firstEmpty.modalId);
        $modal.modal("show");
        $modal.on("shown.bs.modal", function () {
          const $row = $modal.find('.locale-modal-row[data-locale="' + firstEmpty.frontLocale + '"]');
          $row.addClass("border-warning").removeClass("border-success");
          // Scroll to the row
          $row[0].scrollIntoView({ behavior: "smooth", block: "center" });
          // Remove highlight after a few seconds
          setTimeout(function () {
            $row.removeClass("border-warning").addClass("border-success");
          }, 3000);
        });

        return false;
      }

      return true;
    });
  },

  /**
   * Bind per-language translate buttons inside locale modals.
   */
  bindTranslateButtons() {
    $(document).on("click", ".locale-translate-btn", function () {
      const $btn = $(this);
      $btn.blur();
      const $row = $btn.closest(".locale-modal-row");
      const $wrapper = $row.closest(".locale-field-wrapper");
      const sourceLocale = $btn.data("source");
      const targetLocale = $btn.data("locale-target");

      // Get source text from modal row (which may have been edited)
      const $sourceRow = $wrapper.find('.locale-modal-row[data-locale="' + sourceLocale + '"]');
      let sourceText = $sourceRow.find("input.form-control, textarea.form-control").val() || "";

      if (!sourceText.trim()) {
        const msg = $btn.data("msg-empty") || "Source language text is empty.";
        if (window.inno && window.inno.alert) {
          window.inno.alert({ msg: msg, type: "warning" });
        }
        return;
      }

      const $targetInput = $row.find("input.form-control, textarea.form-control");

      // Show loading state
      $btn.prop("disabled", true);
      const originalHtml = $btn.html();
      $btn.html('<span class="spinner-border spinner-border-sm" role="status"></span>');

      const url = getPanelBaseUrl() + "/translations/translate-text";
      console.log("[locale-field] translate:", { url, source: sourceLocale, target: targetLocale, text: sourceText });

      axios
        .post(url, {
          source: sourceLocale,
          target: targetLocale,
          text: sourceText,
        })
        .then(function (res) {
          console.log("[locale-field] translate response:", res.data);
          res.data.forEach(function (item) {
            $targetInput.val(item.result);
          });
          // Flash green to confirm
          $targetInput.addClass("is-valid");
          setTimeout(function () { $targetInput.removeClass("is-valid"); }, 2000);
        })
        .catch(function (err) {
          console.error("[locale-field] translate error:", err);
          if (window.inno && window.inno.alert) {
            window.inno.alert({
              msg: err.response?.data?.message || err.message,
              type: "danger",
            });
          }
        })
        .finally(function () {
          $btn.prop("disabled", false);
          $btn.html(originalHtml);
        });
    });
  },

  /**
   * Bind smart fill button in modal footer.
   * If translator is available: translate primary language text to each empty language via API.
   * If no translator: copy primary language text directly to each empty language field.
   */
  bindSmartFillButton() {
    $(document).on("click", ".locale-smart-fill-btn", function () {
      const $btn = $(this);
      $btn.blur();
      const $wrapper = $btn.closest(".locale-field-wrapper");
      const hasTranslator = $btn.data("has-translator") === true || $btn.data("has-translator") === "true";
      const primaryLocale = $btn.data("primary-locale");
      const msgNoEmpty = $btn.data("msg-no-empty");
      const msgTranslated = $btn.data("msg-translated");
      const msgCopied = $btn.data("msg-copied");

      // Get primary language text from modal primary row (user may have edited it)
      const $modalPrimaryRow = $wrapper.find('.locale-modal-row[data-locale="' + primaryLocale + '"]');
      const primaryText = $modalPrimaryRow.find("input.form-control, textarea.form-control").val() || "";

      if (!primaryText.trim()) {
        return;
      }

      // Collect empty language rows
      const emptyRows = [];
      $wrapper.find(".locale-modal-row").each(function () {
        const $row = $(this);
        const locale = $row.data("locale");
        if (locale === primaryLocale) return;

        const $input = $row.find("input.form-control, textarea.form-control");
        if (!$input.val() || !$input.val().trim()) {
          emptyRows.push({ $row, locale, $input });
        }
      });

      if (emptyRows.length === 0) {
        if (window.inno && window.inno.alert) {
          window.inno.alert({
            msg: msgNoEmpty,
            type: "info",
          });
        }
        return;
      }

      if (hasTranslator) {
        // Translate via API for each empty language
        $btn.prop("disabled", true);
        const originalHtml = $btn.html();
        $btn.html('<span class="spinner-border spinner-border-sm me-1" role="status"></span>' + $btn.text().trim());
        let completed = 0;
        const total = emptyRows.length;

        emptyRows.forEach(function (item) {
          axios
            .post(`${getPanelBaseUrl()}/translations/translate-text`, {
              source: primaryLocale,
              target: item.locale,
              text: primaryText,
            })
            .then(function (res) {
              res.data.forEach(function (translation) {
                item.$input.val(translation.result);
              });
            })
            .catch(function (err) {
              // Fallback to copy on error
              item.$input.val(primaryText);
            })
            .finally(function () {
              completed++;
              if (completed === total) {
                $btn.prop("disabled", false);
                $btn.html(originalHtml);
                if (window.inno && window.inno.alert) {
                  window.inno.alert({
                    msg: msgTranslated.replace(":count", completed),
                    type: "success",
                  });
                }
              }
            });
        });
      } else {
        // Direct copy
        emptyRows.forEach(function (item) {
          item.$input.val(primaryText);
        });

        if (window.inno && window.inno.alert) {
          window.inno.alert({
            msg: msgCopied.replace(":count", emptyRows.length),
            type: "success",
          });
        }
      }
    });
  },
};

export default localeField;
