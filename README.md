# Obenlo - Developer Manual & Platform Guide

Welcome to the **Obenlo Ecosystem**. This document serves as the primary manual for developers and architects. Obenlo is a premium, 100% bespoke booking platform designed for Stays, Experiences, Services, and Events.

---

## 1. Platform Philosophy & Design
Obenlo is built with a **Mobile-First, "Glassy" Aesthetic** using the following UI standards:
*   **The Crimson Accent**: Primary actions and branding use `#e61e4d`.
*   **Premium Gradient Headers**: Standardized static and legal pages utilize a `linear-gradient(135deg, #e61e4d 0%, #ff5a5f 100%)`.
*   **Minimalist Dead-Ends**: Every sub-page must include explicit "**← Back**" navigation (e.g., "Back to Listings", "Back to Explore").
*   **Audit Compliance**: All legal documents must feature a "Last Updated" field in the header area.

---

## 2. Core Architecture: The "Twin-Stack"
Obenlo splits concerns between the **Theme** and **Core Plugin** to ensure stability and presentation flexibility.

### A. The Theme (`wp-content/themes/obenlo/`)
*   **Responsibility**: Front-end styling, layout loops, typography, and premium animations.
*   **Key Files**:
    *   `single-listing.php`: The primary listing detail view with dynamic booking logic.
    *   `author.php`: The "Host Storefront" profile view.
    *   `archive-listing.php`: The global listing search and results loop.
    *   `dashboard-*.php`: Bespoke dashboard layouts for Guests and Hosts.

### B. The Core Plugin (`wp-content/plugins/obenlo-booking/`)
*   **Responsibility**: Business logic, database management, and custom post types.
*   **Key Systems**:
    *   **Custom Post Types**: `listing`, `booking`, `testimony` (Obenlo Love), `ticket`.
    *   **User Roles**: `guest` (Booker) and `host` (Service Provider).
    *   **Payouts**: Managed via `class-payout-manager.php`.
    *   **Reviews**: Handled through `class-reviews.php` supporting parent/child listing aggregation.

---

## 3. The Standalone Plugin Ecosystem
Obenlo utilizes specialized standalone plugins to maintain a modular architecture:

| Plugin | Purpose | Key Features |
| :--- | :--- | :--- |
| **`obenlo-i18n`** | **Global Reach** | Replaces legacy localized logic with a high-performance translation engine. |
| **`obenlo-pwa`** | **Mobile Optimization** | Serves `sw.js` and `manifest.json`. Handles WebPush subscriptions and "Add to Home Screen" prompts. |
| **`obenlo-seo`** | **Visibility** | Automatically generates meta tags, social preview data, and platform sitemaps. |
| **`obenlo-social`** | **Engagement** | Manages social sharing hooks and platform-wide social connectivity. |

---

## 4. Financial Systems & Payments
Obenlo supports multiple global and local payment methods via native class implementations:
*   **Stripe**: Native integration for global cards.
*   **PayPal**: Standard gateway for digital wallets.
*   **MonCash**: (NEW) Integration with Digicel MonCash for mobile payments in Haiti.
*   **NatCash**: (NEW) Integration with Natcom NatCash for expanded mobile payment coverage.

*Admin configuration is found at `/site-admin/?tab=payments`.*

---

## 5. Development Guidelines & Debugging
*   **Error Handling**: Utilize `obenlo_redirect_with_error()` to ensure users are redirected back with clean, white-labeled error messages.
*   **PWA Debugging**: Append `?debug_pwa=1` to any URL to see real-time service worker status.
*   **Global Styling**: Most premium styles (gradients, glassy cards) are defined in `index.css` and `pwa.css`.
*   **Real-time Logic**: Uses vanilla JS `setInterval` to ping AJAX endpoints, avoiding the need for heavy socket dependencies.

---

## 6. Maintenance & Versioning

### 🔄 Repository Synchronization
To reset your local environment and sync with the latest stable version from GitHub:
```powershell
# Run in wp-content directory
git fetch origin; git reset --hard origin/main; git clean -fd
```

---

## 📝 Changelog

### [1.3.0] - 2026-04-04
#### Added
- **Global Sync**: Unified all plugins and theme to **v1.3.0**.
- **Admin Visibility**: Added **[PARENT]** and **[UNIT]** badges to the listing management table.
- **Maintenance**: Standardized centralized version tracking and sync workflows.

#### Fixed
- **Host Storefront**: Corrected "Hosted by" data key to prioritize custom Store Names.
- **Data Synchronization**: Fixed specialties overwriting store names in the host dashboard.
- **PWA**: Refined mobile install prompt sequences.

---
*Manual Updated: April 2026*
*Maintained by the Obenlo Development Team*
