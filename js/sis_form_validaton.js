var SISFormValidation = {
    serialize_form(form){
      return _.object(_.map(form.serializeArray(), function(item){return [item.name, item.value]; }));
    },

    validate : function(form_selector, form_rules, form_submit_handler) {
            var form_object = $(form_selector);
            var error = $('.alert-danger', form_object);
            var success = $('.alert-success', form_object);

            form_object.validate({
                errorElement: 'span', //default input error message container
                errorClass: 'help-block help-block-error', // default input error message class
                focusInvalid: false, // do not focus the last invalid input
                ignore: "",  // validate all fields including form hidden input
                rules: form_rules,
                invalidHandler: function (event, validator) { //display error alert on form submit
                    success.hide();
                    error.show();
                },
                highlight: function (element) { // hightlight error inputs
                    $(element).closest('.form-group').addClass('has-error'); // set error class to the control group
                },
                unhighlight: function (element) { // revert the change done by hightlight
                    $(element).closest('.form-group').removeClass('has-error'); // set error class to the control group
                },
                success: function (label) {
                    label.closest('.form-group').removeClass('has-error'); // set success class to the control group
                },
                errorPlacement: function(error, element) {
                    if ($(element).attr("type") == "checkbox" || $(element).attr("type") == "radio") {
                        error.insertAfter($(element).closest("div"));
                    } else {
                        error.insertAfter(element);
                    }
                },
                submitHandler: function (form, event) {
                    event.preventDefault();
                    success.show();
                    error.hide();
                    if (form_submit_handler) form_submit_handler(SISFormValidation.serialize_form(form_object));
                }
            });
    }
}
