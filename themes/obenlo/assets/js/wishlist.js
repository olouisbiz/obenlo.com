jQuery(document).ready(function($) {
    $(document).on('click', '.wishlist-heart', function(e) {
        e.preventDefault();
        e.stopPropagation();

        const $btn = $(this);
        const listingId = $btn.data('listing-id');
        const $svg = $btn.find('svg');

        if ($btn.hasClass('loading')) return;

        $btn.addClass('loading');
        $btn.css('transform', 'scale(0.9)');

        $.ajax({
            url: obenlo_wishlist.ajax_url,
            type: 'POST',
            data: {
                action: 'obenlo_toggle_wishlist',
                listing_id: listingId,
                nonce: obenlo_wishlist.nonce
            },
            success: function(response) {
                $btn.removeClass('loading');
                $btn.css('transform', 'scale(1)');
                
                if (response.success) {
                    if (response.data.added) {
                        $btn.addClass('active');
                        $svg.css('fill', 'var(--obenlo-primary)');
                    } else {
                        $btn.removeClass('active');
                        $svg.css('fill', 'rgba(0,0,0,0.5)');
                    }
                } else if (response.data.require_login) {
                    // Redirect to login if not logged in
                    window.location.href = '/login?redirect_to=' + encodeURIComponent(window.location.href);
                } else {
                    console.error(response.data.message);
                }
            },
            error: function() {
                $btn.removeClass('loading');
                $btn.css('transform', 'scale(1)');
                console.error('Wishlist error: Could not connect to server.');
            }
        });
    });
});
