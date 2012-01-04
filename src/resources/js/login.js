(function ($, undefined) {

  $(document).ready(function () {
    var login = $('#login')
      .wrap('<div id="loginWrap"><div id="loginContainer"></div></div>');
    if (login.length > 0) {
      $('html,body').addClass('login');
    }
  });

}) (jQuery);
