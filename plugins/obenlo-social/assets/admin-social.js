jQuery(document).ready(function($) {
    $('#obenlo-social-push-btn').on('click', function(e) {
        e.preventDefault();
        
        var $btn = $(this);
        var $feedback = $('#obenlo-social-feedback');
        var postId = $btn.data('post-id');

        if (!confirm('Are you sure you want to push this to Facebook and Instagram?')) {
            return;
        }

        $btn.prop('disabled', true).text('Pushing...');
        $feedback.text('Connecting to Meta APIs...').css('color', '#666');

        $.ajax({
            url: obenloSocialObj.ajax_url,
            type: 'POST',
            data: {
                action: 'obenlo_social_push',
                post_id: postId,
                security: obenloSocialObj.nonce
            },
            success: function(response) {
                if (response.success) {
                    $feedback.text(response.data.message).css('color', 'green');
                    $btn.text('Push Successful!');
                    setTimeout(function() {
                        $btn.prop('disabled', false).text('Push to Social Again');
                    }, 3000);
                } else {
                    $feedback.text('Error: ' + response.data.message).css('color', 'red');
                    $btn.prop('disabled', false).text('Try Again');
                }
            },
            error: function() {
                $feedback.text('Server error. Please check logs.').css('color', 'red');
                $btn.prop('disabled', false).text('Try Again');
            }
        });
    });
});
