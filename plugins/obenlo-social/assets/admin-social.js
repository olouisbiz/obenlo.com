jQuery(document).ready(function($) {
    console.log('Obenlo Social Sharing v1.2.2 - Centered Picker Loaded');

    // Create a simple, sleek Social Picker (if not exists)
    if (!$('#obenlo-social-picker').length) {
        $('body').append(`
            <div id="obenlo-social-picker" style="display:none; position:fixed; top:50%; left:50%; transform:translate(-50%, -50%); width:90%; max-width:320px; background:#fff; border-radius:16px; box-shadow:0 20px 50px rgba(0,0,0,0.3); z-index:9999999; padding:20px; border:2px solid #e61e4d; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
                    <strong style="color:#e61e4d; font-size:18px;">Share Listing</strong>
                    <span id="obenlo-close-picker" style="cursor:pointer; color:#999; font-size:28px; line-height:1;">&times;</span>
                </div>
                <div style="display:flex; flex-direction:column; gap:12px;">
                    <a id="share-to-fb" href="#" target="_blank" style="padding:14px; border-radius:10px; background:#1877f2; color:#fff; text-decoration:none; text-align:center; font-weight:600; font-size:16px;">Post to Facebook</a>
                    <button id="share-to-ig" style="padding:14px; border-radius:10px; background:linear-gradient(45deg, #f09433 0%,#e6683c 25%,#dc2743 50%,#cc2366 75%,#bc1888 100%); color:#fff; border:none; cursor:pointer; font-weight:600; font-size:16px;">Instagram Feed / Stories</button>
                    <button id="share-to-native" style="padding:12px; border-radius:10px; background:#f4f4f4; color:#333; border:1px solid #ddd; cursor:pointer; font-size:14px;">Other Apps (Native)</button>
                </div>
                <div id="obenlo-picker-overlay" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:-1;"></div>
            </div>
        `);
        
        // Background overlay click to close
        $(document).on('click', '#obenlo-picker-overlay', function() {
            $('#obenlo-social-picker').fadeOut(200);
            $('#obenlo-picker-overlay').hide();
        });
    }

    var currentBtnData = {};

    // Handle "Push to Social" clicks
    $(document).on('click', '.obenlo-social-push-btn', function(e) {
        var $btn = $(this);
        currentBtnData = $btn.data();
        
        // Detection Logic
        var isMobile = /Android|iPhone|iPad|iPod/i.test(navigator.userAgent);
        var isMacDesktop = navigator.userAgent.includes('Macintosh') && (!navigator.maxTouchPoints || navigator.maxTouchPoints === 0);

        // On Desktops (Mac/Windows): Just let the native <a> link work (opens Facebook in new tab)
        if (!isMobile || isMacDesktop) {
            console.log('Desktop detected: Following direct link.');
            return; // Don't prevent default, just let the link open
        }

        // On Mobile/Phone: Intercept and show the Picker
        e.preventDefault();
        $('#share-to-fb').attr('href', $btn.attr('href'));
        $('#obenlo-picker-overlay').show();
        $('#obenlo-social-picker').fadeIn(200);
    });

    // Close Picker
    $('#obenlo-close-picker').on('click', function() {
        $('#obenlo-social-picker').fadeOut(200);
        $('#obenlo-picker-overlay').hide();
    });

    // Handle Instagram Share (Mobile Only)
    $('#share-to-ig').on('click', async function() {
        var type = currentBtnData.type || 'listing';
        var template = (type === 'listing') ? obenloSocialObj.listing_template : obenloSocialObj.post_template;
        var caption = template.replace('{title}', currentBtnData.title || '').replace('{price}', currentBtnData.price || '').replace('{location}', currentBtnData.location || '').replace('{excerpt}', currentBtnData.excerpt || '');

        if (navigator.share) {
            try {
                let shareData = {
                    title: currentBtnData.title,
                    text: caption
                };

                if (currentBtnData.image) {
                    const response = await fetch(currentBtnData.image);
                    const blob = await response.blob();
                    const file = new File([blob], 'post.jpg', { type: 'image/jpeg' });
                    
                    if (navigator.canShare && navigator.canShare({ files: [file] })) {
                        shareData.files = [file];
                    }
                }

                await navigator.share(shareData);
                $('#obenlo-close-picker').click();
            } catch (error) {
                console.log('IG Share failed', error);
            }
        }
    });

    // Native Share (Everything else)
    $('#share-to-native').on('click', function() {
        var type = currentBtnData.type || 'listing';
        var template = (type === 'listing') ? obenloSocialObj.listing_template : obenloSocialObj.post_template;
        var caption = template.replace('{title}', currentBtnData.title || '').replace('{price}', currentBtnData.price || '').replace('{location}', currentBtnData.location || '').replace('{excerpt}', currentBtnData.excerpt || '');

        if (navigator.share) {
            navigator.share({
                title: currentBtnData.title,
                text: caption,
                url: currentBtnData.url
            }).then(() => $('#obenlo-close-picker').click());
        }
    });
});
