jQuery(document).ready(function($) {
    console.log('Obenlo Social Sharing Loaded (v1.1.0 - Image Support)');

    // Handle "Push to Social" clicks
    $(document).on('click', '.obenlo-social-push-btn', async function(e) {
        var $btn = $(this);
        var data = $btn.data();
        
        // Detection Logic
        var isMobile = /Android|iPhone|iPad|iPod/i.test(navigator.userAgent);
        var isMacDesktop = navigator.userAgent.includes('Macintosh') && (!navigator.maxTouchPoints || navigator.maxTouchPoints === 0);

        // On Phone/Mobile: Use Native Share API with Image Support
        if (isMobile && navigator.share) {
            e.preventDefault();
            
            var type = data.type || 'listing';
            var template = (type === 'listing') ? obenloSocialObj.listing_template : obenloSocialObj.post_template;
            
            // Build the caption
            var caption = template;
            caption = caption.replace('{title}', data.title || '');
            caption = caption.replace('{price}', data.price || '');
            caption = caption.replace('{location}', data.location || '');
            caption = caption.replace('{excerpt}', data.excerpt || '');

            try {
                let shareData = {
                    title: data.title,
                    text: caption,
                    url: data.url
                };

                // Try to include image if available (Crucial for Instagram Feed/Stories)
                if (data.image) {
                    const response = await fetch(data.image);
                    const blob = await response.blob();
                    const file = new File([blob], 'post-image.jpg', { type: 'image/jpeg' });
                    
                    if (navigator.canShare && navigator.canShare({ files: [file] })) {
                        shareData.files = [file];
                    }
                }

                await navigator.share(shareData);
                console.log('Successful mobile share');
            } catch (error) {
                console.log('Share error or cancelled:', error);
                // Fallback to the link if share fails
                window.open($btn.attr('href'), '_blank');
            }
        } 
        // On Mac Desktop: Force the direct Facebook link (avoiding the Airdrop menu)
        else if (isMacDesktop) {
            console.log('Mac Desktop detected: Opening direct Facebook share link.');
            // Let the <a> tag handle it (it opens in a new tab)
        }
        else {
            // Other Desktops: Let the <a> tag handle it
            console.log('Generic Desktop detected: Following direct link.');
        }
    });
});
