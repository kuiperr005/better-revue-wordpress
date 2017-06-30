jQuery(function($){

    function validateEmail(email) {
        var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        return re.test(email);
    }

    $('body').on('click','.revue-subscribeform button[type="submit"]', function(e) {
        e.preventDefault();
        var container = $(this).parent();
        var loader = container.find('.revue-ajax-loader');
        var $fldEmail = container.find('input[name="revue_email"]');

        var email = $fldEmail.val();

        if (!validateEmail(email)) {
            $fldEmail.css('border', '1px solid red');
            return;
        }

        loader.css('display', 'inline-block');

        var data = {
            action : 'rkmd_revue_subscribe',
            email : email
        };

        $.post(rkmd_revue_ajaxurl, data, function(res) {
            container.html(res.thank_you);
            loader.css('display', 'none');
        });
    });
});