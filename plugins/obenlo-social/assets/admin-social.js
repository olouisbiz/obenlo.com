jQuery(document).ready(function($) {
    // Handle "Push to Social" button clicks (works for both sidebar and dash table)
    $(document).on('click', '#obenlo-social-push-btn, .obenlo-social-push-btn', function(e) {
        e.preventDefault();
        
        var $btn = $(this);
        var postId = $btn.data('post-id');
        
        // Find feedback element - either by ID (sidebar) or by relative row class (table)
        var $feedback = $('#obenlo-social-feedback');
        if ($btn.hasClass('obenlo-social-push-btn')) {
            $feedback = $btn.siblings('.obenlo-social-status');
            if (!$feedback.length) {
                $feedback = $('<span class="obenlo-social-status" style="margin-left:10px; font-size:0.85em; font-weight:600;"></span>');
                $btn.after($feedback);
            }
        }

        if (!confirm('Are you sure you want to push this to Obenlo\'s Facebook and Instagram feeds?')) {
            return;
        }

        var originalText = $btn.text();
        $btn.prop('disabled', true).text('Pushing...');
        $feedback.text('Connecting...').css('color', '#666');

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
                    $btn.text('Pushed');
                    setTimeout(function() {
                        $btn.prop('disabled', false).text(originalText);
                    }, 5000);
                } else {
                    $feedback.text('Error: ' + response.data.message).css('color', 'red');
                    $btn.prop('disabled', false).text('Retry');
                }
            },
            error: function() {
                $feedback.text('Network error.').css('color', 'red');
                $btn.prop('disabled', false).text('Retry');
            }
        });
    });
});
