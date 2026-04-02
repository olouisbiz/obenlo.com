jQuery(document).ready(function($) {
    console.log('Obenlo Social Sharing v1.2.9 - Universal Save Loaded');

    var currentBtnData = {};

    function closePicker() {
        $('#obenlo-social-picker').fadeOut(150);
        $('#obenlo-social-picker-overlay').fadeOut(150);
    }

    function showToast(msg, duration = 3000) {
        $('.obenlo-toast').remove();
        var $toast = $('<div class="obenlo-toast" style="position:fixed; top:20%; left:50%; transform:translateX(-50%); background:#e61e4d; color:#fff; padding:15px 25px; border-radius:12px; z-index:10000001; font-size:16px; font-weight:700; box-shadow:0 10px 30px rgba(0,0,0,0.4); text-align:center; width:80%; max-width:300px; border:2px solid #fff;">' + msg + '</div>');
        $('body').append($toast);
        setTimeout(function() { $toast.fadeOut(400, function() { $(this).remove(); }); }, duration);
    }

    // Handle "Push to Social" clicks
    $(document).on('click', '.obenlo-social-push-btn', function(e) {
        var $btn = $(this);
        currentBtnData = $btn.data();
        
        // Detection
        var isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
        var isMacDesktop = navigator.userAgent.includes('Macintosh') && (!navigator.maxTouchPoints || navigator.maxTouchPoints === 0);

        if (!isMobile || isMacDesktop) {
            return true; 
        }

        e.preventDefault();
        $('#share-to-fb').attr('href', $btn.attr('href'));
        $('#obenlo-social-picker-overlay').show();
        $('#obenlo-social-picker').show();
    });

    // Close Actions
    $(document).on('click', '#obenlo-close-picker, #obenlo-social-picker-overlay', function() {
        closePicker();
    });

    // Instagram "Universal Save" Workflow
    $(document).on('click', '#share-to-ig', function() {
        var type = currentBtnData.type || 'listing';
        var template = (type === 'listing') ? obenloSocialObj.listing_template : obenloSocialObj.post_template;
        var caption = template;
        
        if (currentBtnData.title) caption = caption.replace('{title}', currentBtnData.title);
        if (currentBtnData.price) caption = caption.replace('{price}', currentBtnData.price);
        if (currentBtnData.location) caption = caption.replace('{location}', currentBtnData.location);
        if (currentBtnData.excerpt) caption = caption.replace('{excerpt}', currentBtnData.excerpt);

        // 1. Copy Caption to Clipboard
        var dummy = document.createElement("textarea");
        document.body.appendChild(dummy);
        dummy.value = caption;
        dummy.select();
        document.execCommand("copy");
        document.body.removeChild(dummy);
        
        // 2. Open Native "Save Image" Dialog (Reliable for Gallery)
        if (currentBtnData.image && navigator.share) {
            saveImageAndLaunch(caption);
        } else {
            showToast("Caption copied! Opening Instagram...");
            launchInstagram();
        }
    });

    async function saveImageAndLaunch(caption) {
        try {
            const response = await fetch(currentBtnData.image);
            const blob = await response.blob();
            const file = new File([blob], 'Obenlo-Listing.jpg', { type: 'image/jpeg' });

            if (navigator.canShare && navigator.canShare({ files: [file] })) {
                // This triggers the OS share sheet where user can tap "Save Image"
                showToast("Step 1: Tap 'Save Image' below<br>Step 2: We'll open Instagram!");
                
                await navigator.share({
                    files: [file]
                });
                
                // After they close the share sheet, wait then open IG
                setTimeout(function() {
                    launchInstagram();
                }, 1500);
            } else {
                launchInstagram();
            }
        } catch (err) {
            console.log('Universal Save failed', err);
            launchInstagram();
        }
    }

    function launchInstagram() {
        showToast("Caption copied! Opening Instagram...");
        setTimeout(function() {
            window.location.href = "instagram://camera";
            setTimeout(function() {
                if (!document.hidden) window.location.href = "https://www.instagram.com/";
            }, 500);
            closePicker();
        }, 1000);
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
