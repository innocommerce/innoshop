$(function () {
  $('.footer-link-title .footer-link-icon').on('click', function () {
    $(this).toggleClass('active');
    $(this).parent().next().slideToggle();
  })
})