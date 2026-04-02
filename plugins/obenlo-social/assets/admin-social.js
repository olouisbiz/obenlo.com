jQuery(document).ready(function($) {
    console.log('Obenlo Social Sharing Loaded');

    // Handle "Push to Social" button clicks
    $(document).on('click', '.obenlo-social-push-btn', function(e) {
        e.preventDefault();
        
        var $btn = $(this);
        var data = $btn.data();
        var type = data.type || 'listing';
        var template = (type === 'listing') ? obenloSocialObj.listing_template : obenloSocialObj.post_template;
        
        // Build the caption by replacing tags
        var caption = template;
        caption = caption.replace('{title}', data.title || '');
        caption = caption.replace('{price}', data.price || '');
        caption = caption.replace('{location}', data.location || '');
        caption = caption.replace('{excerpt}', data.excerpt || '');

        var shareData = {
            title: data.title,
            text: caption,
            url: data.url
        };

        // 1. Try Native Web Share API (Best for Mobile/PWA)
        if (navigator.share) {
            navigator.share(shareData)
                .then(() => console.log('Successful share'))
                .catch((error) => console.log('Error sharing:', error));
        } 
        // 2. Fallback for Desktop (Facebook Sharer)
        else {
            var fbUrl = 'https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent(data.url) + '&quote=' + encodeURIComponent(caption);
            window.open(fbUrl, 'SocialShare', 'width=600,height=400');
        }
    });
});
