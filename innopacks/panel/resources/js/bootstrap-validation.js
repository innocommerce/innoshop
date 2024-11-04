$(function () {
  const forms = document.querySelectorAll(".needs-validation");

  // 触发表单提交
  $(document).on('click', '.submit-form', function(event) {
    const form = $(this).attr('form');

    if ($(`form#${form}`).find('button[type="submit"]').length > 0) {
      $(`form#${form}`).find('button[type="submit"]')[0].click();
    } else {
      $(`form#${form}`).submit();
    }
  });

  // 表单保存统一添加加载动画
  $(document).on('submit', 'form', function(event) {
    if (!$(this).hasClass('no-load')) {
      layer.load(2, { shade: [0.2, '#fff'] });
    }
  });


  Array.prototype.slice.call(forms).forEach(function (form) {
    form.addEventListener(
      "submit",
      function (event) {
        const invalidInputs = form.querySelectorAll('.is-invalid');
        if (invalidInputs.length > 0 || !form.checkValidity()) {
          event.preventDefault();
          event.stopPropagation();
        }

        form.classList.add("was-validated");
        $('.nav-link, .nav-item').removeClass('error-invalid');
        //  .is-invalid 除了 有 other-error class 的元素，其他的都移除， other-error 代表可能是自定义的错误提示，非必填的错误
        $('.is-invalid').each(function (index, el) {
          if (!$(el).hasClass('other-error')) {
            $(el).removeClass('is-invalid');
          }
        });
        $('.invalid-feedback').removeClass('d-block');

        // 获取 required 属性的 input 并且值为空的元素
        const requiredInputs = document.querySelectorAll('input[required], textarea[required], select[required]');
        // 判断这些元素后面是否有错误提示信息，如果没有 则取父元素后面的 .invalid-feedback 显示
        requiredInputs.forEach((el) => {
          if (!$(el).val()) {
            if (!$(el).next('.invalid-feedback').length) {
              $(el).parent().next('.invalid-feedback').addClass('d-block');
            }
          }
        });

        let isErrorMsg = false;
        // 如果错误输入框在 tab 页面，则高亮显示对应的选项卡
        $('.invalid-feedback').each(function (index, el) {
          if ($(el).css('display') == 'block') {
            isErrorMsg = true;

            // 兼容使用 element ui input、autocomplete 组件，在传统提交报错ui显示
            if ($(el).siblings('div[class^="el-"]')) {
              $(el).siblings('div[class^="el-"]').find('.el-input__inner').addClass('error-invalid-input')
            }

            if ($(el).parents('.tab-pane')) {
              //高亮显示对应的选项卡
              $(el).parents('.tab-pane').each(function (index, el) {
                const id = $(el).prop('id');
                $(`a[href="#${id}"], button[data-bs-target="#${id}"]`).addClass('error-invalid')[0].click();
              })
            }

            // 页面滚动到错误输入框位置 只滚动一次
            if ($('#content').data('scroll') != 1) {
              $('#content').data('scroll', 1);
              setTimeout(() => {
                $('#content').animate({
                  scrollTop: $(el).offset().top - 140
                }, 200, () => {
                  $('#content').data('scroll', 0);
                });
              }, 200);
            }
          }
        });

        if (isErrorMsg) {
          inno.msg('请检查表单是否填写正确');
        }
      },
      false
    );
  });
});
