// Show message notification
export function msg(params = {}, callback = null) {
  let message = typeof params === 'string' ? params : params.msg || '';
  let time = params.time || 2000;
  layer.msg(message, {time}, callback);
}

// Show alert notification
export function alert(params = {}, callback = null) {
  let message = typeof params === 'string' ? params : params.msg || '';
  let type = params.type || 'success';
  let icon = type === 'success' ? 1 : 2;

  layer.msg(message, {
    icon: icon,
    shade: 0.3,
    shadeClose: true,
    time: 5000
  }, callback);
}

// Bootstrap form validation
export function validateAndSubmitForm(form, callback) {
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
}

// Open login modal
export function openLogin() {
  var area = window.innerWidth < 768 ? '94%' : '500px';

  layer.open({
    type: 2,
    title: '',
    area: area,
    content: `${urls.front_login}?iframe=true`,
    success: function(layero, index) {
      var iframe = $(layero).find('iframe');
      iframe.css('height', iframe[0].contentDocument.body.offsetHeight + 20);
      $(layero).css('top', (window.innerHeight - iframe[0].offsetHeight) / 2);
    }
  });
}

// Get base URL
export function getBase() {
  let url = document.querySelector('base').href;
  if (url.endsWith('/')) {
    url = url.slice(0, -1);
  }
  return url;
}

// Set app content minimum height
export function setAppContentMinHeight(){
  let appHeaderHeight = $('#appHeader').outerHeight();
  let appFooterHeight = $('#appFooter').outerHeight(true);
  let windowHeight = $(window).outerHeight();
  $('#appContent').css('min-height', (windowHeight - appHeaderHeight - appFooterHeight - 48) + 'px');
}
