jQuery(document).ready(function($) {
    console.log('Obenlo Social Sharing Loaded (v1.0.1)');

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

        // Detect if we are on a Mobile device
        var isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);

        // 1. On Mobile: Use Native Web Share API (Best for Phone/PWA)
        if (isMobile && navigator.share) {
            navigator.share(shareData)
                .then(() => console.log('Successful share'))
                .catch((error) => {
                    console.log('Error sharing:', error);
                    // Fallback if native share is cancelled or fails
                    openSocialPopup(data.url, caption);
                });
        } 
        // 2. On Desktop (Mac/Windows): Use Social Popups (Reliable & avoids system blocks)
        else {
            openSocialPopup(data.url, caption);
        }

        function openSocialPopup(url, text) {
            var fbUrl = 'https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent(url) + '&quote=' + encodeURIComponent(text);
            var popup = window.open(fbUrl, 'SocialShare', 'width=600,height=500,location=no,menubar=no,status=no,toolbar=no');
            
            if (!popup || popup.closed || typeof popup.closed == 'undefined') {
                alert('Popup blocked! Please allow popups for Obenlo to share to Facebook.');
            }
        }
    });
});
