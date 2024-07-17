export default {
  msg(params = {}, callback = null) {
    let msg = typeof params === 'string' ? params : params.msg || '';
    let time = params.time || 2000;
    layer.msg(msg, {time}, callback);
  },

  alert(params = {}, callback = null) {
    let top = 70;
    let id = Math.random().toString(36).substring(7);
    let msg = typeof params === 'string' ? params : params.msg || '';
    let type = params.type || 'success';
    let icon = 'bi-check-circle-fill';
    if (type != 'success') {
      icon = 'bi-exclamation-circle-fill';
    }

    $('.is-alert').each(function () {
      top += $(this).outerHeight() + 10;
    });

    let html = '';
    html += ` <div id="alert-${id}" class="alert alert-${type} alert-dismissible is-alert position-fixed me-4 z-3">`;
    html += `   <i class="bi ${icon}"></i>`;
    html += '   ' + msg;
    html += '   <button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
    html += ' </div>';

    $('body').append(html);

    $('#alert-' + id).css({right: '-100%',top}).animate({right: '0'}, 200);

    window.setTimeout(function () {
      $('#alert-' + id).animate({right: '-100%'}, 200, function () {
        $(this).remove();
        top = 40;
        $('.is-alert').each(function () {
          $(this).animate({top}, 100);
          top += $(this).outerHeight() + 10;
        });

        if (callback !== null) {
          callback();
        }
      });
    }, 5000);
  },

  imgUploadAjax(file, _self, callback = null) {
    if (file.type.indexOf('image') === -1) {
        alert('请上传图片文件');
        return;
    }

    let formData = new FormData();
    formData.append('image', file);
    formData.append('type', _self.parents('.is-up-file').data('type'));
    _self.find('.img-loading').removeClass('d-none');
    axios.post(urls.upload_images, formData, {}).then(function (res) {
      callback(res);
    }).catch(function (err) {
      inno.msg(err.response.data.message);
    }).finally(function () {
      _self.find('.img-loading').addClass('d-none');
    });
  },

  // bootstrap 表单验证, js 验证
  validateAndSubmitForm(form, callback) {
    $(document).on('click', `${form} .form-submit`, function(event) {
      if ($(form)[0].checkValidity() === false) {
        event.preventDefault();
        event.stopPropagation();
      }

      $(form).addClass('was-validated');

      if ($(form)[0].checkValidity() === true) {
        callback($(form).serialize());
      }
    })

    $(document).on('keypress', `${form} input`, function(event) {
      if (event.keyCode === 13) {
        $(`${form} .form-submit`).trigger('click');
      }
    })
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
      layer.confirm(lang.delete_confirm, {icon: 3, title: lang.hint, btn: [lang.confirm, lang.cancel]}, function(index) {
        layer.close(index);
        layer.load(2, {shade: [0.3,'#fff'] })
        axios.delete(api).then((res) => {
          if (handleResponseInternally) {
            inno.msg(res.message)
            location.reload();
          }
          resolve(res);
        }).catch((err) => {
          reject(err);
          inno.msg(err.response.data.message)
        }).finally(() => {
          layer.closeAll('loading');
        });
      });
    });
  },
};