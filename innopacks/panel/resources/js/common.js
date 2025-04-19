export default {
  getTranslate() {
    $("#translate-button").click(function () {
      const source_locale = $("#source-locale").val();
      const input = $(`input[data-locale="${source_locale}"]`);

      const text = input.val();

      axios
        .post(`${urls.base_url}/translations/translate-text`, {
          source: source_locale,
          target: $("#target-locale").val(),
          text: text,
        })
        .then(function (res) {
          res.data.forEach(function (item) {
            const target_input = $(
              `input[name="translations[${item.locale}][name]"]`
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
        .post(`${urls.base_url}/translations/translate-html`, {
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
      // 首先尝试从locale-code的data属性获取
      const localeCodeData = $(this)
        .closest(".locale-code")
        .data("locale-code");
      if (localeCodeData && localeCodeData !== "undefined") {
        current_source = localeCodeData;
      } else {
        // 如果data属性不存在或为undefined，则从输入框的name属性中获取
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
        .post(`${urls.base_url}/translations/translate-text`, {
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
  randomString(length = 32) {
    let str = "";
    const chars =
      "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
    for (let i = 0; i < length; i++) {
      str += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    return str;
  },

  msg(params = {}, callback = null) {
    let msg = typeof params === "string" ? params : params.msg || "";
    let time = params.time || 2000;
    layer.msg(msg, { time }, callback);
  },

  alert(params = {}, callback = null) {
    let top = 70;
    let id = Math.random().toString(36).substring(7);
    let msg = typeof params === "string" ? params : params.msg || "";
    let type = params.type || "success";
    let icon = "bi-check-circle-fill";
    if (type != "success") {
      icon = "bi-exclamation-circle-fill";
    }

    $(".is-alert").each(function () {
      top += $(this).outerHeight() + 10;
    });

    let html = "";
    html += ` <div id="alert-${id}" class="alert alert-${type} alert-dismissible is-alert position-fixed me-4 z-3">`;
    html += `   <i class="bi ${icon}"></i>`;
    html += "   " + msg;
    html +=
      '   <button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
    html += " </div>";

    $("body").append(html);

    $("#alert-" + id)
      .css({ right: "-100%", top })
      .animate({ right: "0" }, 200);

    window.setTimeout(function () {
      $("#alert-" + id).animate({ right: "-100%" }, 200, function () {
        $(this).remove();
        top = 40;
        $(".is-alert").each(function () {
          $(this).animate({ top }, 100);
          top += $(this).outerHeight() + 10;
        });

        if (callback !== null) {
          callback();
        }
      });
    }, 5000);
  },

  imgUploadAjax(file, _self, callback = null) {
    if (file.type.indexOf("image") === -1) {
      alert("请上传图片文件");
      return;
    }

    let formData = new FormData();
    formData.append("image", file);
    formData.append("type", _self.parents(".is-up-file").data("type"));
    _self.find(".img-loading").removeClass("d-none");

    axios
      .post(urls.upload_images, formData, {})
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

  // bootstrap 表单验证, js 验证
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
   * 删除确认 弹出框
   * @param {string} api 删除接口
   * @param {boolean} handleResponseInternally 是否内部处理响应
   * @returns {Promise}
   * @example
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
  // 获取url中的参数, url 传入的url，name 要获取的参数名
  getQueryString(name, url = window.location.href) {
    var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)", "i");
    var r = url.split("?")[1] ? url.split("?")[1].match(reg) : null;
    if (r != null) return unescape(r[2]);
    return null;
  },
};
