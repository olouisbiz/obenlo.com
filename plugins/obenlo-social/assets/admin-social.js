jQuery(document).ready(function($) {
    console.log('Obenlo Social Sharing v1.2.7 - Media-First Loaded');

    var currentBtnData = {};

    function closePicker() {
        $('#obenlo-social-picker').fadeOut(150);
        $('#obenlo-social-picker-overlay').fadeOut(150);
    }

    function showToast(msg) {
        var $toast = $('<div style="position:fixed; top:20px; left:50%; transform:translateX(-50%); background:#333; color:#fff; padding:10px 20px; border-radius:30px; z-index:10000000; font-size:14px; font-weight:600; box-shadow:0 5px 15px rgba(0,0,0,0.3);">' + msg + '</div>');
        $('body').append($toast);
        setTimeout(function() { $toast.fadeOut(400, function() { $(this).remove(); }); }, 2500);
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

        // Phone: Show Centered Modal
        e.preventDefault();
        $('#share-to-fb').attr('href', $btn.attr('href'));
        $('#obenlo-social-picker-overlay').show();
        $('#obenlo-social-picker').show();
    });

    // Close Actions
    $(document).on('click', '#obenlo-close-picker, #obenlo-social-picker-overlay', function() {
        closePicker();
    });

    // Handle Instagram Share (Media-First)
    $(document).on('click', '#share-to-ig', function() {
        var type = currentBtnData.type || 'listing';
        var template = (type === 'listing') ? obenloSocialObj.listing_template : obenloSocialObj.post_template;
        var caption = template;
        
        if (currentBtnData.title) caption = caption.replace('{title}', currentBtnData.title);
        if (currentBtnData.price) caption = caption.replace('{price}', currentBtnData.price);
        if (currentBtnData.location) caption = caption.replace('{location}', currentBtnData.location);
        if (currentBtnData.excerpt) caption = caption.replace('{excerpt}', currentBtnData.excerpt);

        // 1. Copy to Clipboard
        var dummy = document.createElement("textarea");
        document.body.appendChild(dummy);
        dummy.value = caption;
        dummy.select();
        document.execCommand("copy");
        document.body.removeChild(dummy);
        
        showToast("Caption copied! Paste it in Instagram.");

        // 2. Share ONLY Image to force Feed/Stories
        if (navigator.share) {
            shareOnlyImage();
        }
    });

    async function shareOnlyImage() {
        try {
            if (currentBtnData.image) {
                const response = await fetch(currentBtnData.image);
                const blob = await response.blob();
                // Use a descriptive but standard filename
                const file = new File([blob], 'Obenlo-Listing.jpg', { type: 'image/jpeg' });
                
                if (navigator.canShare && navigator.canShare({ files: [file] })) {
                    // Sharing ONLY the file is the key for Instagram Feed
                    await navigator.share({
                        files: [file]
                    });
                } else {
                    // Fallback if files can't be shared
                    showToast("Direct image share not supported. Using standard share.");
                    await navigator.share({ title: currentBtnData.title, url: currentBtnData.url });
                }
            } else {
                await navigator.share({ title: currentBtnData.title, url: currentBtnData.url });
            }
            closePicker();
        } catch (err) {
            console.log('Share error:', err);
            closePicker();
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
