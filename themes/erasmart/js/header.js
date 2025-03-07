$(function () {
  // header-box
  // 页面向下滚动时，给header-box添加 active
  const headerContentHeight = $('.header-box').outerHeight(true);

  $(window).scroll(function () {
    if ($(window).scrollTop() > 0) {
      $('.header-box').addClass('active');
      if (!$('.header-content-placeholder').length && !$('body').hasClass('page-home'))
      $('.header-box').before('<div class="header-content-placeholder" style="height: ' + headerContentHeight + 'px;"></div>');
    } else {
      $('.header-box').removeClass('active');
      $('.header-content-placeholder').remove();
    }
  });
});
