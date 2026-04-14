jQuery(document).ready(function($) {
    console.log('Obenlo Social Sharing v1.3.0 - Visual Mode Loaded');

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
        
        e.preventDefault();
        
        // Populate Caption Editor
        var caption = currentBtnData.caption || '';
        $('#obenlo-social-caption-edit').val(caption);

        // Set Preview Image
        if (currentBtnData.image) {
            $('#obenlo-social-preview-img').attr('src', currentBtnData.image);
        }
        
        // Update Links Initially
        updateShareLinks();

        $('#obenlo-social-picker-overlay').show();
        $('#obenlo-social-picker').show();
    });

    // Real-time link updating as user edits caption
    $(document).on('input', '#obenlo-social-caption-edit', function() {
        updateShareLinks();
    });

    function updateShareLinks() {
        var caption = $('#obenlo-social-caption-edit').val();
        var url = currentBtnData.url;
        
        // WhatsApp: https://wa.me/?text=[CAPTION]%20[URL]
        var waUrl = "https://wa.me/?text=" + encodeURIComponent(caption + "\n\n" + url);
        $('#share-to-wa').attr('href', waUrl);

        // Facebook
        var fbUrl = "https://www.facebook.com/sharer/sharer.php?u=" + encodeURIComponent(url);
        $('#share-to-fb').attr('href', fbUrl);
    }

    // Close Actions
    $(document).on('click', '#obenlo-close-picker, #obenlo-social-picker-overlay', function() {
        closePicker();
    });

    // Instagram Button Logic (Copies Caption + Opens IG)
    $(document).on('click', '#share-to-ig', function() {
        var caption = $('#obenlo-social-caption-edit').val();

        // Copy Caption to Clipboard
        var dummy = document.createElement("textarea");
        document.body.appendChild(dummy);
        dummy.value = caption;
        dummy.select();
        document.execCommand("copy");
        document.body.removeChild(dummy);
        
        showToast("Caption copied! Opening Instagram...");

        setTimeout(function() {
            window.location.href = "instagram://camera";
            setTimeout(function() {
                if (!document.hidden) window.location.href = "https://www.instagram.com/";
            }, 500);
            closePicker();
        }, 1200);
    });

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
