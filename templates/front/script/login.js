(function($) {

  $(document).ready(function() {
    $('#login-form').on('submit', function(event) {
      event.preventDefault();

      const form = $(event.currentTarget);
      $('input', form).removeClass('error');

      $.post('/ajax/front/login.php', form.serialize(), function(response) {
        console.log(response);
        if ('success' in response && response.success) {
          window.location.reload();
        } else if ('errors' in response && response.errors.length) {
          ShowErrors(response.errors, form);

        }
      }, 'json');
    });

    $('#login-form .forget-toggler').on('click', function(event) {
      event.preventDefault();
      $('#login-form > div').toggle();
    });
  });

})(jQuery);
