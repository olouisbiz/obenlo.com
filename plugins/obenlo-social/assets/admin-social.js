jQuery(document).ready(function($) {
    console.log('Obenlo Social Sharing v1.2.3 - Bulletproof Mobile Loaded');

    // Create the Social Picker (if not exists)
    function createPicker() {
        if ($('#obenlo-social-picker').length) return;
        $('body').append(`
            <div id="obenlo-social-picker-overlay" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:9999998;"></div>
            <div id="obenlo-social-picker" style="display:none; position:fixed; top:50%; left:50%; transform:translate(-50%, -50%); width:90%; max-width:320px; background:#fff; border-radius:16px; box-shadow:0 20px 60px rgba(0,0,0,0.5); z-index:9999999; padding:20px; border:3px solid #e61e4d; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
                    <strong style="color:#e61e4d; font-size:18px;">Social Share</strong>
                    <span id="obenlo-close-picker" style="cursor:pointer; color:#999; font-size:32px; line-height:1;">&times;</span>
                </div>
                <div style="display:flex; flex-direction:column; gap:12px;">
                    <a id="share-to-fb" href="#" target="_blank" style="padding:16px; border-radius:10px; background:#1877f2; color:#fff; text-decoration:none; text-align:center; font-weight:700; font-size:16px;">Share to Facebook</a>
                    <button id="share-to-ig" style="padding:16px; border-radius:10px; background:linear-gradient(45deg, #f09433 0%,#e6683c 25%,#dc2743 50%,#cc2366 75%,#bc1888 100%); color:#fff; border:none; cursor:pointer; font-weight:700; font-size:16px;">Instagram Feed/Stories</button>
                    <button id="share-to-native" style="padding:12px; border-radius:10px; background:#f0f0f0; color:#333; border:1px solid #ccc; cursor:pointer; font-weight:600; font-size:14px;">Other Apps (Native)</button>
                </div>
            </div>
        `);
        
        // Background overlay click to close
        $(document).on('click', '#obenlo-social-picker-overlay', function() {
            closePicker();
        });
        
        $('#obenlo-close-picker').on('click', closePicker);
    }

    function closePicker() {
        $('#obenlo-social-picker').fadeOut(200);
        $('#obenlo-social-picker-overlay').fadeOut(200);
    }

    var currentBtnData = {};

    // Initial Picker Creation
    createPicker();

    // Handle "Push to Social" clicks
    $(document).on('click', '.obenlo-social-push-btn', function(e) {
        var $btn = $(this);
        currentBtnData = $btn.data();
        
        // Detection
        var isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
        var isMacDesktop = navigator.userAgent.includes('Macintosh') && (!navigator.maxTouchPoints || navigator.maxTouchPoints === 0);

        // On Desktops (Mac/Windows): Fallback to direct link instantly
        if (!isMobile || isMacDesktop) {
            console.log('Desktop: Opening direct link.');
            return true; // Let browser handle <a>
        }

        // On Phone: Show Menu
        e.preventDefault();
        $('#share-to-fb').attr('href', $btn.attr('href'));
        $('#obenlo-social-picker-overlay').show();
        $('#obenlo-social-picker').fadeIn(200);
    });

    // Handle IG click with safe async execution
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
                console.log('Share failed', err);
                closePicker();
            }
        }
    }

    $(document).on('click', '#share-to-native', function() {
        if (navigator.share) {
           navigator.share({
               title: currentBtnData.title,
               text: currentBtnData.title,
               url: currentBtnData.url
           }).then(closePicker);
        }
    });
});
