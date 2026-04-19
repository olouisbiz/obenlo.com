# Obenlo Developer & Administrative Guide

Welcome to the technical heart of **Obenlo**, the premium Caribbean-centric service and booking engine.

## 🚀 Project Overview
Obenlo is a custom-engineered WordPress ecosystem designed for high-concurrency booking, real-time scheduling, and localized Caribbean payments. It uses a **Hybrid Architecture** (PHP/React/PWA) to deliver a mobile-first, app-like experience.

---

## 🛠 Tech Stack
| Tier | Technology |
| :--- | :--- |
| **Backend** | WordPress (CMS), Custom PHP Class-based Architecture |
| **Frontend** | Vanilla CSS (Premium Aesthetics), React (Planner Subsystem) |
| **Logic** | custom-built `obenlo-booking` plugin |
| **Mobile** | PWA (Service Workers, Offline Manifest) |
| **Payments** | Stripe, PayPal |

---

## 📂 Architecture: Modular Plugins
Obenlo is decentralized across several specialized plugins for maintainability. The core booking engine follows a **"One Class, One Role"** modular architecture:

1. **`obenlo-booking`**: The core "Engine." Handles:
   - CPTs: `listing`, `booking`, `ticket`, `broadcast`.
   - Logic: Commission calculation, Availability, Payouts.
   - Payments: Multi-gateway orchestration.
2. **`obenlo-pwa`**: Handles offline experience, push notifications, and "Add to Home" logic.
3. **`obenlo-i18n`**: Advanced translation engine and language filtering.
4. **`obenlo-seo`**: Smart SEO Engine 2.0. Handles location-aware meta, BreadcrumbList schema, and rich snippets.
5. **`obenlo-social`**: Provides viral sharing engines, WhatsApp auto-posting, and frontend quick tools for hosts.

---

## 📖 Documentation & Reference
To maintain a clean repository root, all core logic and guides are centralized in the [`/wp-content/docs/`](file:///c:/Users/obenc/Local%20Sites/obenlo/app/public/wp-content/docs/) directory:
- **`logic-reference.md`**: Core business rules for Listings and Profiles.
- **`deployment-guide.md`**: Protocol for migrating between environments.
- **`ARCHITECTURE.md`**: (Located in `/plugins/obenlo-booking/`) Detailed map of the modular PHP classes.

---

## 🏗 Developer Onboarding

### 1. The Listing Loop
Listings are hierarchical. A parent `listing` (e.g., a Hotel) can have multiple children `listing` (e.g., specific rooms). Use `post_parent` to query child entities.

### 2. Booking Workflows
The `Obenlo_Booking_Payments` class handles the transition of a booking from `pending_payment` ➡️ `confirmed`.
- **Status Change Hook:** `Obenlo_Booking_Notifications::notify_booking_event($booking_id, 'booking_confirmed')`
- **Earnings:** Earnings are calculated upon confirmation but only released to the `_obenlo_host_balance` meta upon completion.

### 3. Frontend Customization
The `obenlo` theme is decoupled from standard WP templates. Dynamic content is rendered through **Modular Template Parts** to ensure high performance and easy debugging:
- `page-account.php`: Unified Dashboard hub using fragments in `template-parts/account/`.
- `single-listing.php`: Dynamic listing renderer using fragments in `template-parts/listing/`.

---

## 👑 Site Administrator Quick-Start

### 1. Announcement Management (New!)
Admins can send broadcasts from the **Site Admin Dashboard**.
- **Location:** Dashboard > Obenlo Dashboard > Support Tab.
- **Auto-Formatting:** All announcements are automatically wrapped in a premium crimson-themed template.

### 2. Support Filtering
Manage host/guest disputes using the **Support Status Filters**:
- Toggle between **Open**, **Resolved**, and **All** tickets.
- Use the **ID DESC** sort to find the newest issues instantly.

### 3. Identity Verification
Review host KYC and authentication requests through the **Verifications Hub**.
- **Audit Log:** Every verification transition is logged to ensure platform safety.
- **Demo Management:** Use the Admin Demo Manager for onboarding specialized test listings.

---

## 📈 Performance & Scaling
- **Caching:** Page elements are heavily cached. After making sidebar changes, ensure a **Hard Refresh (Ctrl + F5)**.
- **Optimization:** Use `wp_postmeta` efficiently; always index searches by meta values for listing discovery.

---

**Obenlo Technical Team**
*v1.9.0 - "Recurring Sessions & Logic Sync" Release*
