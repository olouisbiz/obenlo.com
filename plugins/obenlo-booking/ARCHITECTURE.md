# Obenlo Booking Plugin: Architecture & File Reference

This document provides a complete map of the Obenlo Booking plugin structure following its successful modularization. The platform is now built on a "One Class, One Role" mandate to ensure scalability and ease of maintenance.

## 📂 Plugin Root (`/obenlo-booking/`)

| File / Folder | Role |
| :--- | :--- |
| `obenlo-booking.php` | **The Heart.** The main plugin bootstrap file. It defines constants, handles activation/deactivation, and initializes all domain-specific modules. |
| `includes/` | **The Brain.** Contains all logic classes, handlers, and modular components. |
| `vendor/` | **Third-Party.** Contains external dependencies such as PHP WebPush for notifications. |
| `composer.json` | Project configuration and dependency manifest for Composer. |
| `diag.php` | A specialized diagnostic utility for debugging environment-specific issues. |

---

## 📂 Logic & Modules (`/includes/`)

### 🛠️ Admin Dashboard (Modularized)
*Logic for the Site Administrator's control panel.*

| File | Responsibility |
| :--- | :--- |
| `class-admin-dashboard.php` | **Router.** The central hub for the Admin interface. It handles permissions, sidebar navigation, and delegates tab rendering to specialized modules. |
| `class-admin-overview.php` | Renders the main statistics, revenue charts, and system status. |
| `class-admin-listings.php` | Management of all Listings (Stays, Experiences, etc.). |
| `class-admin-users.php` | User management, roles, and profile auditing. |
| `class-admin-bookings.php` | Global oversight of all guest reservations. |
| `class-admin-payments.php` | Financial logs and gateway configurations. |
| `class-admin-settings.php` | General plugin configuration and behavioral toggles. |
| `class-admin-messages.php` | Internal messaging system oversight. |
| `class-admin-demo.php` | Specialized logic for managing Obenlo Demo Accounts. |
| `class-admin-verifications.php` | Handling of host KYC and identity authentication. |

### 🏠 Host Dashboard (Modularized)
*Logic for the Seller/Host interface.*

| File | Responsibility |
| :--- | :--- |
| `class-host-dashboard.php` | **Router.** The main controller for the Host-facing dashboard. |
| `class-host-overview.php` | Host-specific earnings summaries and pending task alerts. |
| `class-host-listings.php` | Interface for hosts to create and edit their own inventory. |
| `class-host-bookings.php` | Management of guest arrivals and check-ins. |
| `class-host-payouts.php` | Host wallet management and payout request triggers. |
| `class-host-availability.php` | Fine-grained calendar control and vacation mode. |

### 💳 Core Engines & Utilities
*The heavy-lifting backend systems.*

| File | Responsibility |
| :--- | :--- |
| `class-payments.php` | The abstract payment gateway handler. |
| `class-stripe.php` | Implementation of Stripe Checkout. |
| `class-paypal.php` | Implementation of PayPal Smart Buttons. |
| `class-notifications.php` | The platform's unified notification engine. |
| `class-communication.php` | The internal Peer-to-Peer messaging logic. |
| `class-post-types.php` | Registration of Custom Post Types. |
| `class-roles.php` | Definition of capabilities for Hosts, Guests, and Support. |
| `class-virtual-security.php` | Logic for secure virtual session links. |
