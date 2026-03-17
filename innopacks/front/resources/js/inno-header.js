$(function () {
  // Page header scroll effect handling
  const headerContentHeight = $('.header-box').outerHeight(true);

  $(window).scroll(function () {
    if ($(this).scrollTop() > 100) {
      $('.header-box').addClass('header-fixed');
      // If not on homepage and no placeholder exists, add placeholder to prevent page jumping
      if (!$('body').hasClass('page-home') && !$('.header-placeholder').length) {
        $('.header-box').before('<div class="header-placeholder" style="height:' + headerContentHeight + 'px"></div>');
      }
    } else {
      $('.header-box').removeClass('header-fixed');
      $('.header-placeholder').remove();
    }
  });
});
