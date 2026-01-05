<?php
/**
 * Template Part: Auth Gate View
 * Location: /obenlo-theme/templates/view-auth-gate.php
 */
?>
<div class="auth-gate-wrapper" style="max-width: 450px; margin: 60px auto; padding: 40px; background: #fff; border-radius: 20px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); border: 1px solid #f1f5f9;">
    <div style="text-align: center; margin-bottom: 30px;">
        <h2 style="font-size: 1.8rem; font-weight: 800; color: #1e293b;">Welcome to <span style="color: #6366f1;">Obenlo</span></h2>
        <p style="color: #64748b;">Sign in or create an account to continue.</p>
    </div>

    <form id="obenlo-login-form" style="display: flex; flex-direction: column; gap: 15px;">
        <input type="email" name="email" placeholder="Email Address" required style="padding: 12px; border: 1px solid #e2e8f0; border-radius: 8px;">
        <input type="password" name="password" placeholder="Password" required style="padding: 12px; border: 1px solid #e2e8f0; border-radius: 8px;">
        <button type="submit" style="background: #6366f1; color: white; padding: 12px; border: none; border-radius: 8px; font-weight: bold; cursor: pointer;">Sign In</button>
    </form>

    <div style="margin: 20px 0; text-align: center; border-bottom: 1px solid #f1f5f9; line-height: 0.1em;">
        <span style="background:#fff; padding:0 10px; color: #94a3b8; font-size: 0.8rem;">OR</span>
    </div>

    <button onclick="document.getElementById('obenlo-register-form').style.display='flex'; this.style.display='none'; document.getElementById('obenlo-login-form').style.display='none';" style="width: 100%; background: transparent; border: 1px solid #e2e8f0; padding: 10px; border-radius: 8px; color: #64748b; cursor: pointer;">Create a New Account</button>

    <form id="obenlo-register-form" style="display: none; flex-direction: column; gap: 15px;">
        <input type="email" name="email" placeholder="Email Address" required style="padding: 12px; border: 1px solid #e2e8f0; border-radius: 8px;">
        <input type="password" name="password" placeholder="Create Password" required style="padding: 12px; border: 1px solid #e2e8f0; border-radius: 8px;">
        <select name="role" style="padding: 12px; border: 1px solid #e2e8f0; border-radius: 8px; background: white;">
            <option value="subscriber">I am a Traveler</option>
            <option value="obenlo_vendor">I am a Host</option>
        </select>
        <button type="submit" style="background: #10b981; color: white; padding: 12px; border: none; border-radius: 8px; font-weight: bold; cursor: pointer;">Complete Signup</button>
    </form>
</div>