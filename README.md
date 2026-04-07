# Obenlo - The Premium African & Caribbean Booking Engine

Welcome to the **Obenlo Platform**. This document is the definitive manual for the Obenlo ecosystem, covering technical architecture, administrative operations, and user guides for both Hosts and Guests.

---

## 1. Platform Overview
Obenlo is a bespoke, 100% custom-built booking platform specifically optimized for **Stays, Experiences, Events, and Services**. It focuses on the unique needs of the Caribbean and African markets, integrating local payout methods (MonCash/NatCash) with global standards (Stripe/PayPal).

### Design Philosophy
*   **"Mobile-First" Premium UX**: Every interface is designed to behave like a native app.
*   **The Crimson Identity**: Primary brand color: `#e61e4d`.
*   **Glassmorphism**: Use of frosted glass effects for high-end aesthetic appeal.
*   **Zero Dead-Ends**: Every sub-navigation includes a clear "← Back" path.

---

## 2. Technical Architecture: The "Obenlo Twin-Stack"
To ensure extreme performance and design flexibility, Obenlo separates its concerns into two core layers:

### A. The Presentation Layer (Theme)
*   **Location**: `wp-content/themes/obenlo/`
*   **Role**: Handles all visuals, typography, loops, and interactive frontend components.
*   **Key Templates**:
    *   `single-listing.php`: The conversion engine for listing details.
    *   `archive-listing.php`: The high-performance search results page.
    *   `dashboard-*.php`: Bespoke, sidebar-based dashboards for logged-in users.

### B. The Logic Layer (Core Plugin)
*   **Location**: `wp-content/plugins/obenlo-booking/`
*   **Role**: Manages the database, business logic, financial rules, and API integrations.
*   **Primary Systems**:
    *   **Custom Post Types**: `listing`, `booking`, `refund`, `obenlo_payout_req`.
    *   **User Meta**: Balances (`_obenlo_host_balance`), Payout info (`_obenlo_moncash_number`), Verification status.
    *   **Validation**: Robust logic to prevent overlapping bookings or duplicate payouts.

---

## 3. Host Guide (Operating as a Provider)

### Onboarding
*   **Become a Host**: New hosts apply via the [Become a Host](https://obenlo.com/become-a-host/) portal.
*   **Automated Demos**: Upon application, Obenlo automatically generates a "Demo Storefront" to show hosts how their listing will look.

### Managing Inventory
*   **Parent Listings**: Create a "Master" listing (e.g., "Grand Villa").
*   **Units/Child Listings**: Add specific units under the parent (e.g., "Room 101", "Room 102").
*   **Status Toggles**: Instantly "Hide" or "Show" listings to manage seasonal availability.
*   **Event Management**: Specialized tools for ticket quantities and time-based availability specifically for Events and Experiences.

### Financials & Payouts (v1.6.5 Logic)
*   **Earnings**: Your earnings are calculated immediately upon payment but are held by the platform.
*   **Release of Funds**: Funds are only released to your **Withdrawable Balance** once you mark a booking as **"Completed"**.
*   **Withdrawal**: Once funds are in your balance, you can request a payout via the **Payout Settings** tab in your dashboard.
*   **Support Methods**: MonCash (Haiti), NatCash (Haiti), PayPal, and Stripe Connect.

---

## 4. Guest Guide (The Traveler Experience)

### Booking a Trip
*   **Search**: Use the global search to find Stays, Experiences, or Events.
*   **Payment**: Pay securely via Card (Stripe), PayPal, or local Mobile Money (MonCash).
*   **Confirmation**: You will receive an instant confirmation email and a dashboard update once your payment is verified.

### Managing Trips
*   **My Trips**: View all upcoming and past bookings in your dashboard.
*   **Cancellation**: You can cancel a booking that is "Pending" at any time.
*   **Refund Requests**: If a trip is "Confirmed" but you cannot attend, you may "Request a Refund" via the trip details page.

### Refunds (v1.6.6 Logic)
*   Refund requests are reviewed by the Host and the Site Admin.
*   If approved, the refund is processed back to your original payment method.

---

## 5. Site Admin Guide (Platform Operations)

### Dashboard Tabs
*   **Listings**: Manage all global inventory, including the ability to permanently delete or hide demo listings.
*   **Users**: Manage Host/Guest accounts. Admins can view host payout details directly here.
*   **Payouts (NEW)**: A dedicated system for reviewing and fulfilling withdrawal requests.
*   **MonCash One-Click**: Admins can pay local hosts instantly using the integrated MonCash API button.

### Content Management
*   **Obenlo Love**: Manage guest testimonies and platform reviews.
*   **Marketing Control**: Hide/Show virtual hosts and demo content to maintain a high-quality public presence.

---

## 6. Core Static Pages & Legal Templates
The Obenlo theme includes several standardized templates for user guides and legal requirements:
*   **How It Works (`page-how-it-works.php`)**: The primary educational portal for new users.
*   **Refund Policy (`page-refund-policy.php`)**: Detailed rules on transactional disputes and timeframes.
*   **Guest Rules (`page-guest-rules.php`)**: Community standards and property guidelines.
*   **FAQ (`page-faq.php`)**: Common troubleshooting for the booking engine.
*   **Trust & Safety (`page-trust-safety.php`)**: Platform security and verification protocols.

---

## 7. Maintenance & Versioning

### Repository Sync
To reset the local environment to the latest stable production build:
```powershell
# Run in wp-content
git fetch origin; git reset --hard origin/main; git clean -fd
```

### Module Versioning
Current Platform Version: **1.6.6**
*   *Last Major Update: April 2026*
*   *Key Feature: Completion-Based Payout Tracking*

---
*Developed & Maintained by the Obenlo Team*
