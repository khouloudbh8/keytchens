    $(document).ready(function(){
        var successText = $("#success-text")
        successText.hide()
        var submitButton = $('#submit-form')
        $("#contact-form").submit(function(){
            var form = $(this);
            var formData = {
                name: $("#name").val(),
                email: $("#email").val(),
                phone: $("#phone").val(),
                message: $("#message").val(),
                restaurant: $("#restaurant").val(),
            };

            $.ajax({
                url: "https://keytchens.com/external/contact",
                data: formData,
                type: "POST",
                dataType: 'json',
                endoe: true,
                success: function (e) {
                    successText.show()
                    submitButton.attr("disabled",true)
                },
                error:function(e){
                    successText.show()
                    submitButton.attr("disabled",true)
                }
            });
            return false;
        });
    });