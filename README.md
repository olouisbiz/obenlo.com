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
| **Payments** | Stripe, PayPal, MonCash, Natcash |

---

## 📂 Architecture: Core Plugins
Obenlo is decentralized across several specialized plugins for maintainability:

1. **`obenlo-booking`**: The core "Engine." Handles:
   - CPTs: `listing`, `booking`, `ticket`, `broadcast`.
   - Logic: Commission calculation, Availability, Payouts.
   - Payments: Multi-gateway orchestration.
2. **`obenlo-pwa`**: Handles offline experience, push notifications, and "Add to Home" logic.
3. **`obenlo-i18n`**: Advanced translation engine and language filtering.
4. **`obenlo-seo`**: Custom SEO meta handling and structured data for listings.
5. **`obenlo-social`**: Auth bridges and viral sharing engines.

---

## 🏗 Developer Onboarding

### 1. The Listing Loop
Listings are hierarchical. A parent `listing` (e.g., a Hotel) can have multiple children `listing` (e.g., specific rooms). Use `post_parent` to query child entities.

### 2. Booking Workflows
The `Obenlo_Booking_Payments` class handles the transition of a booking from `pending_payment` ➡️ `confirmed`.
- **Status Change Hook:** `Obenlo_Booking_Notifications::notify_booking_event($booking_id, 'booking_confirmed')`
- **Earnings:** Earnings are calculated upon confirmation but only released to the `_obenlo_host_balance` meta upon completion.

### 3. Frontend Customization
The `obenlo` theme is decoupled from standard WP templates. Most dynamic content is rendered through Specialized Page Templates:
- `page-account.php`: Unified Host/Guest single-page dashboard.
- `single-listing.php`: Heavyweight dynamic listing renderer.

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

### 3. Local Payment Control
Haitian-specific gateways (MonCash/Natcash) require valid API keys in the Settings tab.
- **Conversion Rate:** Admins set the `obenlo_htg_exchange_rate` manually to match market fluctuations.

---

## 📈 Performance & Scaling
- **Caching:** Page elements are heavily cached. After making sidebar changes, ensure a **Hard Refresh (Ctrl + F5)**.
- **Optimization:** Use `wp_postmeta` efficiently; always index searches by meta values for listing discovery.

---

**Obenlo Technical Team**
*v1.6.8 - "Unified Platform" Standard*
