# Obenlo Profile & Identity Logic Guide

This guide summarizes how Obenlo manages user identities, trust, and host-specific profiles. Use this to help craft professional, trust-building profiles for hosts and guests.

## 1. User Roles & Capabilities
Obenlo uses a custom role system to separate platform functions:

- **Guest**: The default traveler/buyer. Can browse, book, message hosts, and leave reviews.
- **Host**: A service provider. Can create listings, manage bookings, and access a specialized Host Dashboard.
- **Support Agent**: Specialized role for platform employees to handle tickets and disputes.
- **Administrator**: Full system access for Obenlo staff.

---

## 2. The Host Identity (Storefront)
Hosts don't just have a profile; they have a **Storefront**. This is their professional public identity on the platform.

### Core Profile Fields:
- **Identity**: Identity (Store Name), Tagline (Elevator pitch), and Bio (Store Description).
- **Visuals**: Primary Logo (Avatar) and Brand Banner (Cover Photo).
- **Social Trust**: Direct links to Instagram and Facebook handles.
- **Multimedia**: Video Intro URL (e.g., YouTube/Vimeo) to personalize the experience.
- **Expertise**: Specialty fields to highlight what defines their service.

---

## 3. Trust & Verification Workflow
Verification is critical to the Obenlo ecosystem to prevent fraud and build guest confidence.

### The Verification Funnel:
1.  **Onboarding**: New hosts are guided through a specialized onboarding page.
2.  **ID Upload**: Hosts must upload a government-issued identity document.
3.  **Manual Review**: Obenlo administrators manually review the document.
4.  **Status States**:
    - `not_started`: Default state.
    - `pending`: Document uploaded, awaiting review.
    - `verified`: Identity confirmed. A "Verified Host" badge appears on their profile.
    - `rejected`: Document invalid or suspicious. Host must re-submit.

---

## 4. Reputational Logic (Reviews)
Obenlo gives hosts control over their reputation while maintaining guest transparency:

- **Approval Power**: Hosts can view and approve reviews before they appear publicly on their storefront.
- **Public Replies**: Hosts are encouraged to reply to reviews. Public replies demonstrate responsiveness and care to future guests.

---

## 5. Host Financials & Payouts
- **Balance Tracking**: The platform tracks a Host's "Current Balance" based on completed bookings.
- **Platform Fee**: Obenlo automatically calculates and deducts a platform fee from each transaction upon completion.
- **Payout Management**: Hosts must link their bank or payment details. Payouts are typically triggered once a minimum threshold (e.g., ) is met.

---

## 6. Communication & Safety
- **P2P Messaging**: Built-in chat system for guests to inquire before booking.
- **Support Tickets**: Hosts and Guests have access to a Help Center to open "Support Tickets" for assistance or dispute resolution.
- **Global Broadcasts**: Admins send platform-wide alerts for policy changes or maintenance.
