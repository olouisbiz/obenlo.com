jQuery(document).ready(function($) {
    console.log('Obenlo Social Sharing v1.2.0 - Dual Choice Loaded');

    // Create a simple, sleek Social Picker (if not exists)
    if (!$('#obenlo-social-picker').length) {
        $('body').append(`
            <div id="obenlo-social-picker" style="display:none; position:fixed; bottom:20px; left:50%; transform:translateX(-50%); width:300px; background:#fff; border-radius:12px; box-shadow:0 10px 25px rgba(0,0,0,0.2); z-index:100000; padding:15px; border:2px solid #e61e4d; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Oxygen-Sans,Ubuntu,Cantarell,'Helvetica Neue',sans-serif;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                    <strong style="color:#e61e4d;">Share Listing</strong>
                    <span id="obenlo-close-picker" style="cursor:pointer; color:#999; font-size:20px;">&times;</span>
                </div>
                <div style="display:flex; flex-direction:column; gap:10px;">
                    <a id="share-to-fb" href="#" target="_blank" style="padding:12px; border-radius:8px; background:#1877f2; color:#fff; text-decoration:none; text-align:center; font-weight:600;">Post to Facebook</a>
                    <button id="share-to-ig" style="padding:12px; border-radius:8px; background:linear-gradient(45deg, #f09433 0%,#e6683c 25%,#dc2743 50%,#cc2366 75%,#bc1888 100%); color:#fff; border:none; cursor:pointer; font-weight:600;">Instagram Feed / Stories</button>
                    <button id="share-to-native" style="padding:10px; border-radius:8px; background:#f4f4f4; color:#333; border:1px solid #ddd; cursor:pointer; font-size:12px;">Other Apps (Native)</button>
                </div>
            </div>
        `);
    }

    var currentBtnData = {};

    // Handle "Push to Social" clicks
    $(document).on('click', '.obenlo-social-push-btn', function(e) {
        e.preventDefault();
        var $btn = $(this);
        currentBtnData = $btn.data();
        
        // Detection Logic
        var isMobile = /Android|iPhone|iPad|iPod/i.test(navigator.userAgent);
        var isMacDesktop = navigator.userAgent.includes('Macintosh') && (!navigator.maxTouchPoints || navigator.maxTouchPoints === 0);

        // On Mac Desktop: Just open Facebook directly (bypass everything)
        if (isMacDesktop) {
            window.open($btn.attr('href'), '_blank');
            return;
        }

        // On Mobile/Admin Dash: Show the Menu
        $('#share-to-fb').attr('href', $btn.attr('href'));
        $('#obenlo-social-picker').fadeIn(200);
    });

    // Close Picker
    $('#obenlo-close-picker').on('click', function() {
        $('#obenlo-social-picker').fadeOut(200);
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

                // CRUCIAL: To force Instagram FEED, we ONLY send the Image + Caption (no link URL)
                if (currentBtnData.image) {
                    const response = await fetch(currentBtnData.image);
                    const blob = await response.blob();
                    const file = new File([blob], 'post.jpg', { type: 'image/jpeg' });
                    
                    if (navigator.canShare && navigator.canShare({ files: [file] })) {
                        shareData.files = [file];
                    }
                }

                await navigator.share(shareData);
                $('#obenlo-social-picker').hide();
            } catch (error) {
                console.log('IG Share failed', error);
                alert('Instagram app not detected or share cancelled.');
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
            }).then(() => $('#obenlo-social-picker').hide());
        }
    });
});
