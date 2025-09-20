$(function () {
  const forms = document.querySelectorAll(".needs-validation");

  // Trigger form submission
  $(document).on('click', '.submit-form', function(event) {
    const form = $(this).attr('form');

    if ($(`form#${form}`).find('button[type="submit"]').length > 0) {
      $(`form#${form}`).find('button[type="submit"]')[0].click();
    } else {
      $(`form#${form}`).submit();
    }
  });

  // Add loading animation for form submission
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
        // Remove .is-invalid except elements with other-error class, other-error represents custom error messages, not required field errors
        $('.is-invalid').each(function (index, el) {
          if (!$(el).hasClass('other-error')) {
            $(el).removeClass('is-invalid');
          }
        });
        $('.invalid-feedback').removeClass('d-block');

        // Get required input elements with empty values
        const requiredInputs = document.querySelectorAll('input[required], textarea[required], select[required]');
        // Check if these elements have error messages, if not, show .invalid-feedback after parent element
        requiredInputs.forEach((el) => {
          if (!$(el).val()) {
            if (!$(el).next('.invalid-feedback').length) {
              $(el).parent().next('.invalid-feedback').addClass('d-block');
            }
          }
        });

        let isErrorMsg = false;
        // If error input is in tab page, highlight corresponding tab
        $('.invalid-feedback').each(function (index, el) {
          if ($(el).css('display') == 'block') {
            isErrorMsg = true;

            // Compatible with element ui input, autocomplete components, show error UI in traditional submission
            if ($(el).siblings('div[class^="el-"]')) {
              $(el).siblings('div[class^="el-"]').find('.el-input__inner').addClass('error-invalid-input')
            }

            if ($(el).parents('.tab-pane')) {
              // Highlight corresponding tab
              $(el).parents('.tab-pane').each(function (index, el) {
                const id = $(el).prop('id');
                $(`a[href="#${id}"], button[data-bs-target="#${id}"]`).addClass('error-invalid')[0].click();
              })
            }

            // Scroll page to error input position, scroll only once
            if ($('#content').data('scroll') != 1) {
              $('#content').data('scroll', 1);
              setTimeout(() => {
                $('#content').animate({
                  scrollTop: $(el).offset().top - 140
                }, 200, () => {
                  $('#content').data('scroll', 0);
                });
              }, 100);
            }
          }
        });

        if (isErrorMsg) {
          layer.closeAll('loading');
        }
      },
      false
    );
  });
});
