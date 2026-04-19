<?php $user = wp_get_current_user(); $user_id = $user->ID; ?>
<div id="obenlo-refund-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); backdrop-filter:blur(5px); z-index:9999; align-items:center; justify-content:center;">
    <div style="background:#fff; border-radius:24px; width:100%; max-width:500px; padding:40px; box-shadow:0 25px 50px -12px rgba(0,0,0,0.25); position:relative;">
        <button onclick="closeRefundModal()" style="position:absolute; top:20px; right:20px; background:none; border:none; font-size:24px; color:#aaa; cursor:pointer;">&times;</button>
        <h3 id="refund-listing-title" style="margin-top:0; font-size:1.5rem; font-weight:900; color:#222;">Request Refund</h3>
        <p style="color:#666; margin-bottom:25px;">Please provide a reason for your refund request. The host and Obenlo team will review this shortly.</p>
        
        <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="POST">
            <input type="hidden" name="action" value="obenlo_request_refund">
            <input type="hidden" id="refund-booking-id" name="booking_id" value="">
            <?php wp_nonce_field('request_refund', 'refund_nonce'); ?>
            
            <label style="display:block; font-weight:700; margin-bottom:10px; color:#444;">Reason for Refund</label>
            <textarea name="refund_reason" required rows="4" style="width:100%; padding:15px; border:1px solid #eee; border-radius:15px; font-size:1rem; outline:none; transition:all 0.2s; margin-bottom:25px; border: 1.5px solid #eee;" onfocus="this.style.borderColor='#e61e4d'" onblur="this.style.borderColor='#eee'"></textarea>
            
            <div style="display:flex; gap:15px;">
                <button type="button" onclick="closeRefundModal()" style="flex:1; background:#f9fafb; color:#222; border:none; padding:14px; border-radius:12px; font-weight:700; cursor:pointer;">Cancel</button>
                <button type="submit" style="flex:1; background:#e61e4d; color:#fff; border:none; padding:14px; border-radius:12px; font-weight:700; cursor:pointer; box-shadow:0 4px 12px rgba(230,30,77,0.3);">Submit Request</button>
            </div>
        </form>
    </div>
</div>

<script>
function openRefundModal(bookingId, listingTitle) {
    document.getElementById('refund-booking-id').value = bookingId;
    document.getElementById('refund-listing-title').textContent = 'Refund: ' + listingTitle;
    const modal = document.getElementById('obenlo-refund-modal');
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeRefundModal() {
    const modal = document.getElementById('obenlo-refund-modal');
    modal.style.display = 'none';
    document.body.style.overflow = 'auto';
}

// Close on background click
window.onclick = function(event) {
    const modal = document.getElementById('obenlo-refund-modal');
    if (event.target == modal) {
        closeRefundModal();
    }
}
</script>
