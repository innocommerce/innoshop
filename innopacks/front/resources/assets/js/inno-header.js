$(function () {
  var $header = $('#appHeader');
  var $overlay = $('#searchOverlay');

  // Search overlay
  $(document).on('click', '.search-overlay-btn', function () {
    $overlay.addClass('active');
    $('body').css('overflow', 'hidden');
    setTimeout(function () {
      $overlay.find('input').focus();
    }, 300);
  });

  function closeSearchOverlay() {
    $overlay.removeClass('active');
    $('body').css('overflow', '');
  }

  $(document).on('click', '.search-overlay__close', closeSearchOverlay);
  $(document).on('click', '.search-overlay__backdrop', closeSearchOverlay);

  $(document).on('keydown', function (e) {
    if (e.key === 'Escape' && $overlay.hasClass('active')) {
      closeSearchOverlay();
    }
  });

  // Sticky header
  if (!$header.length) {
    return;
  }

  var headerHeight = $header.outerHeight(true);

  $(window).scroll(function () {
    if ($(this).scrollTop() > 100) {
      if (!$header.hasClass('header-fixed')) {
        $header.addClass('header-fixed');
        if (!$('body').hasClass('page-home') && !$('.header-placeholder').length) {
          $header.before('<div class="header-placeholder" style="height:' + headerHeight + 'px"></div>');
        }
      }
    } else {
      $header.removeClass('header-fixed');
      $('.header-placeholder').remove();
    }
  });
});
