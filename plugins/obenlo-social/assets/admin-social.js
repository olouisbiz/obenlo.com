jQuery(document).ready(function($) {
    console.log('Obenlo Social Sharing Loaded (v1.0.2)');

    // Handle "Push to Social" clicks
    $(document).on('click', '.obenlo-social-push-btn', function(e) {
        var $btn = $(this);
        var data = $btn.data();
        
        // Detect if we are on a Mobile device with Share support
        var isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);

        if (isMobile && navigator.share) {
            e.preventDefault(); // Stop the link from opening normally
            
            var type = data.type || 'listing';
            var template = (type === 'listing') ? obenloSocialObj.listing_template : obenloSocialObj.post_template;
            
            // Build the caption
            var caption = template;
            caption = caption.replace('{title}', data.title || '');
            caption = caption.replace('{price}', data.price || '');
            caption = caption.replace('{location}', data.location || '');
            caption = caption.replace('{excerpt}', data.excerpt || '');

            navigator.share({
                title: data.title,
                text: caption,
                url: data.url
            }).then(() => console.log('Successful mobile share'))
              .catch((error) => console.log('Error sharing:', error));
        } else {
            // On Desktop: We allow the native <a> tag to open the Facebook link in a new tab.
            // This is 100% foolproof and cannot be blocked by popup blockers.
            console.log('Desktop detected: Using direct link for sharing.');
        }
    });
});
