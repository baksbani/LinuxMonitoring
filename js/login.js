var Login = {

    submit_login_form : function(){
      var jqxhr = $.post( "rest/v1/login", { email: $('#email').val() }).done(function(data) {
        //Utils.set_to_localstorage('user', data);
        window.location=data.redirect_uri;
        //console.log(data);
      }).fail(function(error) {
        $('.alert').html(error.responseJSON.error).show();
      });
      return false;
    },

    logout : function(){
      Utils.remove_from_localstorage('user');
      window.location="/login.html";
    },

    init: function() {
      $('.alert').hide();
      $('.alert').click(function(){
        $('.alert').hide();
      });

      $('#login-form').validate({
          errorElement: 'div', //default input error message container
          errorClass: 'has-error', // default input error message class
          focusInvalid: false,

          submitHandler: function(form) {
            return Login.submit_login_form();
          }
      });
    }

};
