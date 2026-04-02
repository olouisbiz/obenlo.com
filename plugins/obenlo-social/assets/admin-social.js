jQuery(document).ready(function($) {
    console.log('Obenlo Social Sharing v1.2.5 - Bottom Sheet Loaded');

    var currentBtnData = {};

    function closePicker() {
        $('#obenlo-social-picker').css('transform', 'translateY(100%)');
        setTimeout(function() {
            $('#obenlo-social-picker').hide();
            $('#obenlo-social-picker-overlay').fadeOut(150);
        }, 300);
    }

    // Handle "Push to Social" clicks
    $(document).on('click', '.obenlo-social-push-btn', function(e) {
        var $btn = $(this);
        currentBtnData = $btn.data();
        
        // Detection
        var isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
        var isMacDesktop = navigator.userAgent.includes('Macintosh') && (!navigator.maxTouchPoints || navigator.maxTouchPoints === 0);

        // Desktops: Standard Facebook Direct Link
        if (!isMobile || isMacDesktop) {
            return true; 
        }

        // Phone: Show Bottom Sheet
        e.preventDefault();
        $('#share-to-fb').attr('href', $btn.attr('href'));
        
        // Reset and Show
        $('#obenlo-social-picker').css({'display': 'block', 'transform': 'translateY(100%)'});
        $('#obenlo-social-picker-overlay').fadeIn(150);
        
        // Slide up animation
        setTimeout(function() {
            $('#obenlo-social-picker').css('transform', 'translateY(0)');
        }, 10);
    });

    // Close Actions
    $(document).on('click', '#obenlo-close-picker, #obenlo-social-picker-overlay', function() {
        closePicker();
    });

    // Handle Instagram Share
    $(document).on('click', '#share-to-ig', function() {
        var type = currentBtnData.type || 'listing';
        var template = (type === 'listing') ? obenloSocialObj.listing_template : obenloSocialObj.post_template;
        var caption = template;
        
        if (currentBtnData.title) caption = caption.replace('{title}', currentBtnData.title);
        if (currentBtnData.price) caption = caption.replace('{price}', currentBtnData.price);
        if (currentBtnData.location) caption = caption.replace('{location}', currentBtnData.location);
        if (currentBtnData.excerpt) caption = caption.replace('{excerpt}', currentBtnData.excerpt);

        if (navigator.share) {
            shareToIG(caption);
        }
    });

    async function shareToIG(caption) {
        try {
            var shareData = { title: currentBtnData.title, text: caption };
            if (currentBtnData.image) {
                const response = await fetch(currentBtnData.image);
                const blob = await response.blob();
                const file = new File([blob], 'post.jpg', { type: 'image/jpeg' });
                if (navigator.canShare && navigator.canShare({ files: [file] })) {
                    shareData.files = [file];
                }
            }
            await navigator.share(shareData);
            closePicker();
        } catch (err) {
            console.log('Share error:', err);
            closePicker();
        }
    }

    // Native Share Fallback
    $(document).on('click', '#share-to-native', function() {
        if (navigator.share) {
           navigator.share({
               title: currentBtnData.title,
               text: currentBtnData.title,
               url: currentBtnData.url
           }).then(closePicker).catch(() => closePicker());
        }
    });
});
