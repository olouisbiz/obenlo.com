<?php
/**
 * Template Name: Host Landing Page
 * Description: A high-converting landing page to acquire native local businesses.
 */

get_header(); ?>

<style>
    .host-hero {
        background: linear-gradient(135deg, #e61e4d 0%, #a81033 100%);
        color: white;
        padding: 80px 20px;
        text-align: center;
        border-radius: 0 0 40px 40px;
        margin-bottom: 50px;
    }
    .host-hero h1 {
        font-size: 3.5rem;
        font-weight: 800;
        margin-bottom: 20px;
        color: white;
    }
    .host-hero p {
        font-size: 1.5rem;
        max-width: 800px;
        margin: 0 auto 30px auto;
        opacity: 0.9;
    }
    .host-hero .cta-btn {
        display: inline-block;
        background: white;
        color: #e61e4d;
        padding: 15px 40px;
        font-size: 1.2rem;
        font-weight: bold;
        border-radius: 50px;
        text-decoration: none;
        transition: transform 0.2s;
    }
    .host-hero .cta-btn:hover {
        transform: translateY(-3px);
    }
    
    .benefits-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 30px;
        max-width: 1200px;
        margin: 0 auto 60px auto;
        padding: 0 20px;
    }
    .benefit-card {
        background: white;
        padding: 40px;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        text-align: center;
    }
    .benefit-card h3 {
        font-size: 1.5rem;
        margin: 20px 0 10px 0;
        color: #333;
    }
    .benefit-card p {
        color: #666;
        line-height: 1.6;
    }
    .benefit-icon {
        font-size: 3rem;
        color: #e61e4d;
    }

    .comparison-section {
        background: #f9fafb;
        padding: 60px 20px;
        text-align: center;
    }
    .comparison-table {
        max-width: 800px;
        margin: 0 auto;
        background: white;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        border-collapse: collapse;
        width: 100%;
    }
    .comparison-table th, .comparison-table td {
        padding: 20px;
        border-bottom: 1px solid #eee;
        text-align: left;
    }
    .comparison-table th {
        background: #f3f4f6;
        font-weight: 600;
        color: #333;
    }
    .comparison-table .obenlo-col {
        background: #fff1f2;
        color: #e61e4d;
        font-weight: bold;
    }
    .comparison-table tr:last-child td {
        border-bottom: none;
    }
</style>

<div class="host-landing-wrapper">
    <!-- HERO SECTION -->
    <section class="host-hero">
        <h1>Grow Your Business with Obenlo</h1>
        <p>Join thousands of tours, spas, and local services reaching millions of global travelers. Stop paying 30% commissions to traditional OTAs.</p>
        <a href="<?php echo esc_url(home_url('/login#signup')); ?>" class="cta-btn">Become a Host Today</a>
    </section>

    <!-- BENEFITS SECTION -->
    <section class="benefits-grid">
        <div class="benefit-card">
            <div class="benefit-icon">💸</div>
            <h3>Keep More of Your Money</h3>
            <p>Traditional booking platforms take 25-30% of your revenue. Obenlo takes a flat 10% processing fee. You worked hard for it, you keep it.</p>
        </div>
        <div class="benefit-card">
            <div class="benefit-icon">🤖</div>
            <h3>Automated Marketing</h3>
            <p>Our built-in AI automatically optimizes your listing for Google SEO, and pushes your availability directly to our social media networks.</p>
        </div>
        <div class="benefit-card">
            <div class="benefit-icon">📅</div>
            <h3>Advanced Booking Tools</h3>
            <p>Manage your calendar, sync with Google Calendar, and assign bookings to your staff members all from one beautiful dashboard.</p>
        </div>
    </section>

    <!-- COMPARISON SECTION -->
    <section class="comparison-section">
        <h2 style="font-size:2.5rem; margin-bottom:40px; color:#333;">Why switch to Obenlo?</h2>
        <table class="comparison-table">
            <thead>
                <tr>
                    <th>Feature</th>
                    <th>Traditional OTAs</th>
                    <th class="obenlo-col">Obenlo Native</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Platform Commission</td>
                    <td>25% - 30%</td>
                    <td class="obenlo-col">10% Flat</td>
                </tr>
                <tr>
                    <td>Payout Speed</td>
                    <td>30 days after experience</td>
                    <td class="obenlo-col">Instant via Stripe/PayPal</td>
                </tr>
                <tr>
                    <td>Customer Data Ownership</td>
                    <td>They hide the customer email</td>
                    <td class="obenlo-col">You own your customer data</td>
                </tr>
                <tr>
                    <td>AI SEO & Social Sync</td>
                    <td>❌ No</td>
                    <td class="obenlo-col">✅ Yes</td>
                </tr>
            </tbody>
        </table>
        
        <div style="margin-top: 50px;">
            <a href="<?php echo esc_url(home_url('/login#signup')); ?>" style="display:inline-block; background:#e61e4d; color:white; padding:15px 40px; border-radius:50px; text-decoration:none; font-weight:bold; font-size:1.2rem;">Start Hosting for Free</a>
            <p style="margin-top:15px; color:#666;">Takes 5 minutes. No credit card required.</p>
        </div>
    </section>
</div>

<?php get_footer(); ?>
