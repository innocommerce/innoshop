(() => {
  window.is = window.is || {};

  const alert = (params = {}, callback = null) => {
    let top = 40;
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
    html += ` <div id="alert-${id}" class="alert alert-${type} alert-dismissible is-alert position-fixed me-2 z-3">`;
    html += `   <i class="bi ${icon}"></i>`;
    html += '   ' + msg;
    html += '   <button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
    html += ' </div>';

    $('body').append(html);

    $('#alert-' + id).css({right: '-100%',top}).animate({right: '0'}, 200);

    window.setTimeout(function () {
      $('#alert-' + id).animate({right: '-100%'}, 200, function () {
        $(this).remove();
        reposition()
        if (callback !== null) {
          callback();
        }
      });
    }, 5000);
  }

  $(document).on('click', '.is-alert .btn-close', function () {
    reposition()
  })

  // 重新计算位置
  const reposition = () => {
    let top = 40;
    $('.is-alert').each(function () {
      $(this).animate({top}, 100);
      top += $(this).outerHeight() + 10;
    });
  }

  is.alert = alert;
})()
