/**
 * Obenlo Core Bridge
 * Handles AJAX for Checkout, Auth, and Interactions
 */
jQuery(document).ready(function($) {

    // 1. STRIPE CHECKOUT HANDLER
    $('#obenlo-book-now').on('click', function(e) {
        e.preventDefault();
        const button = $(this);
        const listingId = button.data('id');

        button.text('Processing...').prop('disabled', true);

        $.ajax({
            url: obenlo_vars.ajaxurl,
            type: 'POST',
            data: {
                action: 'obenlo_checkout',
                listing_id: listingId,
                nonce: obenlo_vars.nonce
            },
            success: function(response) {
                if (response.success && response.data.url) {
                    window.location.href = response.data.url;
                } else {
                    alert('Error: ' + (response.data || 'Could not initiate checkout.'));
                    button.text('Book Experience').prop('disabled', false);
                }
            }
        });
    });

    // 2. AUTH GATE HANDLER (Login & Register)
    $('#obenlo-login-form, #obenlo-register-form').on('submit', function(e) {
        e.preventDefault();
        const form = $(this);
        const formData = form.serialize();
        const action = form.attr('id') === 'obenlo-login-form' ? 'obenlo_user_login' : 'obenlo_user_signup';

        $.ajax({
            url: obenlo_vars.ajaxurl,
            type: 'POST',
            data: formData + '&action=' + action + '&nonce=' + obenlo_vars.nonce,
            success: function(response) {
                if (response.success) {
                    window.location.href = response.data.redirect;
                } else {
                    alert(response.data);
                }
            }
        });
    });
});