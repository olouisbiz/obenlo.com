jQuery(document).ready(function($) {
    console.log('Obenlo Social Sharing v1.2.4 - Pre-rendered Menu Loaded');

    var currentBtnData = {};

    function closePicker() {
        $('#obenlo-social-picker').fadeOut(100);
        $('#obenlo-social-picker-overlay').fadeOut(100);
    }

    // Handle "Push to Social" clicks
    $(document).on('click', '.obenlo-social-push-btn', function(e) {
        var $btn = $(this);
        currentBtnData = $btn.data();
        
        // Detection Logic
        var isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
        var isMacDesktop = navigator.userAgent.includes('Macintosh') && (!navigator.maxTouchPoints || navigator.maxTouchPoints === 0);

        // On Desktops: Let the native <a> link work (opens Facebook)
        if (!isMobile || isMacDesktop) {
            console.log('Desktop detected: Opening direct link.');
            return true; 
        }

        // On Phone: Show the pre-rendered Menu
        e.preventDefault();
        $('#share-to-fb').attr('href', $btn.attr('href'));
        
        // Ensure the overlay and picker are shown properly
        $('#obenlo-social-picker-overlay').show();
        $('#obenlo-social-picker').show().css('opacity', '0').animate({'opacity': '1'}, 200);
    });

    // Close Actions
    $(document).on('click', '#obenlo-close-picker, #obenlo-social-picker-overlay', function() {
        closePicker();
    });

    // Handle Instagram Share
    $(document).on('click', '#share-to-ig', function() {
        performIGShare();
    });

    async function performIGShare() {
        var type = currentBtnData.type || 'listing';
        var template = (type === 'listing') ? obenloSocialObj.listing_template : obenloSocialObj.post_template;
        var caption = template;
        
        if (currentBtnData.title) caption = caption.replace('{title}', currentBtnData.title);
        if (currentBtnData.price) caption = caption.replace('{price}', currentBtnData.price);
        if (currentBtnData.location) caption = caption.replace('{location}', currentBtnData.location);
        if (currentBtnData.excerpt) caption = caption.replace('{excerpt}', currentBtnData.excerpt);

        if (navigator.share) {
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
    }

    // Native Share Fallback (Other Apps)
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
