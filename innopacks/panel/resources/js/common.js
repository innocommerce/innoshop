export default {
  /**
   * Initialize translation functionality for the admin panel
   * Handles text translation, HTML content translation, and individual field translation
   */
  getTranslate() {
    $("#translate-button").click(function () {
      const source_locale = $("#source-locale").val();
      const input = $(`input[data-locale="${source_locale}"]`);

      const text = input.val();
      
      const sourceInputName = input.attr('name');
      let fieldName = 'name';
      
      if (sourceInputName) {
        const fieldMatch = sourceInputName.match(/translations\[[^\]]+\]\[([^\]]+)\]/);
        if (fieldMatch && fieldMatch[1]) {
          fieldName = fieldMatch[1];
        }
      }

      axios
        .post(`${urls.panel_base}/translations/translate-text`, {
          source: source_locale,
          target: $("#target-locale").val(),
          text: text,
        })
        .then(function (res) {
          res.data.forEach(function (item) {
            const target_input = $(
              `input[name="translations[${item.locale}][${fieldName}]"]`
            );
            target_input.val(item.result);
          });
        })
        .catch(function (err) {
          inno.alert({
            msg: err.response?.data?.message || err.message,
            type: "danger",
          });
        });
    });

    $("#translate-html").click(function () {
      const source_tab_code = $("#source-tab").val();
      const textarea = $(
        `textarea[name="translations[${source_tab_code}][content]"]`
      );
      const editor_id = textarea.attr("id");
      const editor = tinymce.get(editor_id);

      tinymce.triggerSave();
      let content = editor.getContent();
      axios
        .post(`${urls.panel_base}/translations/translate-html`, {
          source: source_tab_code,
          target: $("#target-tab").val(),
          text: content,
        })
        .then(function (res) {
          res.data.forEach((item) => {
            const inputs = $(`input[data-locale="${item.locale}"]`);
            inputs.each(function () {
              const rich_text_editor = tinymce.get(`content-${item.locale}`);

              if (rich_text_editor) {
                rich_text_editor.setContent(item.result);
              }
            });
          });
        })
        .catch(function (err) {
          inno.alert({
            msg: err.response?.data?.message || err.message,
            type: "danger",
          });
        });
    });

    $(".translate-submit").click(function () {
      const localeCodeContainer = $(this).closest(".form-row");

      const selectElement = $(this)
        .closest("div")
        .next("div")
        .find("select.form-select");
      const selectedOptionValue = selectElement.val();
      const textarea = localeCodeContainer.find("textarea");
      const inputarea = localeCodeContainer.find("input");

      let current_source;
      // First try to get from locale-code data attribute
      const localeCodeData = $(this)
        .closest(".locale-code")
        .data("locale-code");
      if (localeCodeData && localeCodeData !== "undefined") {
        current_source = localeCodeData;
      } else {
        // If data attribute doesn't exist or is undefined, get from input name attribute
        const inputName = textarea.length
          ? textarea.attr("name")
          : inputarea.attr("name");
        const matches = inputName.match(/translations\[(.*?)\]/);
        current_source = matches ? matches[1] : $(".source-locale").val();
      }

      let textareaValue;
      let currentTextareaName;
      if (textarea.is(".form-control")) {
        textareaValue = textarea.val();
        currentTextareaName = textarea.attr("name");
      } else if (textarea.is(".tinymce")) {
        const editor = tinymce.get(textarea.attr("id"));
        textareaValue = editor.getContent();
        // Remove p tags from content
        textareaValue = textareaValue.replace(/<p>/g, "").replace(/<\/p>/g, "");
        currentTextareaName = textarea.attr("name");
      } else {
        textareaValue = inputarea.val();
        currentTextareaName = inputarea.attr("name");
      }

      axios
        .post(`${urls.panel_base}/translations/translate-text`, {
          source: current_source,
          target: selectedOptionValue,
          text: textareaValue,
        })
        .then(function (res) {
          res.data.forEach(function (item) {
            const targetInputSelector = currentTextareaName.replace(
              `[${current_source}]`,
              `[${item.locale}]`
            );
            if (textarea.is(".tinymce")) {
              const targetEditor = tinymce.get(`content-${item.locale}`);

              if (targetEditor) {
                targetEditor.setContent(item.result);
              }
            } else if (textarea.is("textarea")) {
              $('textarea[name="' + targetInputSelector + '"]').val(
                item.result
              );
            } else {
              $('input[name="' + targetInputSelector + '"]').val(item.result);
            }
          });
        })
        .catch(function (err) {
          inno.alert({
            msg: err.response?.data?.message || err.message,
            type: "danger",
          });
        });
    });
  },
  
  /**
   * Generate a random string of specified length
   * @param {number} length - The length of the random string (default: 32)
   * @returns {string} - Generated random string containing letters and numbers
   */
  randomString(length = 32) {
    let str = "";
    const chars =
      "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
    for (let i = 0; i < length; i++) {
      str += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    return str;
  },

  /**
   * Display a message using layer.js
   * @param {string|object} params - Message string or configuration object
   * @param {function} callback - Optional callback function to execute after message is shown
   */
  msg(params = {}, callback = null) {
    let msg = typeof params === "string" ? params : params.msg || "";
    let time = params.time || 2000;
    layer.msg(msg, { time }, callback);
  },

  /**
   * Display an alert message with icon using layer.js
   * @param {string|object} params - Message string or configuration object
   * @param {function} callback - Optional callback function to execute after alert is shown
   */
  alert(params = {}, callback = null) {
    let msg = typeof params === "string" ? params : params.msg || "";
    let type = params.type || "success";
    let icon = type === "success" ? 1 : 2;
    
    layer.msg(msg, {
      icon: icon,
      shade: 0.3,
      shadeClose: true,
      time: 5000
    }, callback);
  },

  /**
   * Upload image file via AJAX
   * @param {File} file - The image file to upload
   * @param {jQuery} _self - jQuery object of the upload container element
   * @param {function} callback - Callback function to handle upload response
   */
  imgUploadAjax(file, _self, callback = null) {
    if (file.type.indexOf("image") === -1) {
      alert("Please upload an image file");
      return;
    }

    let formData = new FormData();
    formData.append("image", file);
    formData.append("type", _self.parents(".is-up-file").data("type"));
    _self.find(".img-loading").removeClass("d-none");

    axios
      .post(urls.panel_upload, formData, {})
      .then(function (res) {
        callback(res);
      })
      .catch(function (err) {
        inno.msg(err.response.data.message);
      })
      .finally(function () {
        _self.find(".img-loading").addClass("d-none");
      });
  },

  /**
   * Bootstrap form validation and submission handler
   * @param {string} form - CSS selector for the form element
   * @param {function} callback - Callback function to execute when form is valid
   */
  validateAndSubmitForm(form, callback) {
    $(document).on("click", `${form} .form-submit`, function (event) {
      if ($(form)[0].checkValidity() === false) {
        event.preventDefault();
        event.stopPropagation();
      }

      $(form).addClass("was-validated");

      if ($(form)[0].checkValidity() === true) {
        callback($(form).serialize());
      }
    });

    $(document).on("keypress", `${form} input`, function (event) {
      if (event.keyCode === 13) {
        $(`${form} .form-submit`).trigger("click");
      }
    });
  },

  /**
   * Show confirmation dialog for delete operations
   * @param {string} api - The API endpoint for delete operation
   * @param {boolean} handleResponseInternally - Whether to handle response internally (default: true)
   * @returns {Promise} - Promise that resolves with response or rejects with error
   */
  confirmDelete(api, handleResponseInternally = true) {
    return new Promise((resolve, reject) => {
      layer.confirm(
        lang.delete_confirm,
        { icon: 3, title: lang.hint, btn: [lang.confirm, lang.cancel] },
        function (index) {
          layer.close(index);
          layer.load(2, { shade: [0.3, "#fff"] });
          axios
            .delete(api)
            .then((res) => {
              if (handleResponseInternally) {
                inno.msg(res.message);
                location.reload();
              }
              resolve(res);
            })
            .catch((err) => {
              reject(err);
              inno.msg(err.response.data.message);
            })
            .finally(() => {
              layer.closeAll("loading");
            });
        }
      );
    });
  },
  
  /**
   * Get query string parameter from URL
   * @param {string} name - The parameter name to retrieve
   * @param {string} url - The URL to parse (default: current page URL)
   * @returns {string|null} - The parameter value or null if not found
   */
  getQueryString(name, url = window.location.href) {
    var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)", "i");
    var r = url.split("?")[1] ? url.split("?")[1].match(reg) : null;
    if (r != null) return unescape(r[2]);
    return null;
  },

  /**
   * Format string to slug format
   * @param {string} str - String to be formatted
   * @returns {string} - Formatted slug
   */
  formatSlug(str) {
    if (!str) return '';
    
    return str
      .toLowerCase() // Convert to lowercase
      .replace(/[^a-z0-9\-\s]/g, '-') // Replace non-alphanumeric characters (except hyphens and spaces) with hyphens
      .replace(/\s+/g, '-') // Replace spaces with hyphens
      .replace(/-+/g, '-'); // Replace multiple consecutive hyphens with single hyphen
  },

  /**
   * Initialize auto-formatting functionality for slug input fields
   */
  initSlugFormatting() {
    // Bind keyboard events to all input fields with name="slug"
    $(document).on('input keyup', 'input[name="slug"], input[name^="slug["]', function() {
      const $input = $(this);
      const cursorPosition = this.selectionStart;
      const originalValue = $input.val();
      const formattedValue = inno.formatSlug(originalValue);
      
      // Only update when value changes to avoid cursor position issues
      if (originalValue !== formattedValue) {
        $input.val(formattedValue);
        
        // Try to maintain cursor position
        const newCursorPosition = Math.min(cursorPosition, formattedValue.length);
        this.setSelectionRange(newCursorPosition, newCursorPosition);
      }
    });
  }
};
