jQuery(document).ready( function($) {
    // ToDo: Assign this action to the form and serealize the form
    $("#fusedesk-contactform-submit").click( function(){
        if(!$('#fusedesk-contact-email').val().trim())
        {
            $('#fusedesk-contact-email').focus();
            return alert("Please enter your email address.");
        } else if(!$('#fusedesk-contact-name').val().trim()) {
            $('#fusedesk-contact-name').focus();
            return alert("Please enter your name.");
        } else if(!$('#fusedesk-message').val().trim()) {
            $('#fusedesk-message').focus();
            return alert("Please enter your message.");
        }

        $('#fusedesk-contact').slideUp();
        $('#fusedesk-casecreating').slideDown();

        // the_ajax_script.ajaxurl is a variable that will contain the url to the ajax processing file
        $.post(the_ajax_script.ajaxurl, $('#fusedesk-contact').serializeArray(), function(response) {
            // console.log(response);
            if(response.error)
            {
                $('#fusedesk-contact').slideDown();
                $('#fusedesk-casecreating').slideUp();
                alert(response.error);
            } else {
                $('#fusedesk-casecreating').slideUp();
                $('#fusedesk-caseopened').slideDown();
            }
        }, 'json');
        return false;
    });

    if($('#fusedesk-suggestions').length) {
        $('#fusedesk-message').on('change keyup paste', function () {
            var element = $(this);
            var currentVal = element.val(); // remember our current val as we're going to use this a bunch

            // If the value hasn't changed, this is an ignorable event so bail
            if (currentVal == element.data('oldval')) {
                return;
            }

            // store this new value so we can see if the value changes later
            element.data('oldval', currentVal);

            // Are we already searching?
            if (element.data('searching')) {
                return;
            }

            // Flag that we're in the middle of searching
            element.data('searching', true);

            $.get(the_ajax_script.ajaxurl, {
                'action': "fusedesk_search",
                'q': currentVal,
                'limit': $('#fusedesk-suggestions').data('limit'),
                'categories': $('#fusedesk-suggestions').data('categories')
            }, function (data) {
                element.data('searching', false);

                var suggestions = '';

                if (data.count) {
                    $.each(data.results, function (index, post) {
                        suggestions += '<li><a href="' + post.url + '" target="_blank">' + post.title + '</a></li>';
                    });
                    $('#fusedesk-suggestions ul').html(suggestions);
                    $('#fusedesk-suggestions').show();
                } else {
                    $('#fusedesk-suggestions').hide();
                    $('#fusedesk-suggestions ul').html('');
                }

            }, 'json');

        });
    }

});