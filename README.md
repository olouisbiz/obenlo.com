# Obenlo Developer & Administrative Guide

Welcome to the technical heart of **Obenlo**, the premium global discovery, booking, and affiliate platform.

## 🚀 Project Overview
Obenlo is a custom-engineered WordPress ecosystem designed for high-concurrency booking, real-time scheduling, and automated global affiliate monetization. It uses a **Hybrid Architecture** to deliver a mobile-first, app-like experience alongside powerful AI-driven content generation.

---

## 🛠 Tech Stack
| Tier | Technology |
| :--- | :--- |
| **Backend** | WordPress (CMS), Custom PHP Class-based Architecture |
| **Frontend** | Vanilla CSS (Premium Aesthetics) |
| **Core Logic** | custom-built `obenlo-booking` plugin |
| **AI & Automation** | custom-built `obenlo-ai` plugin (OpenAI & Affiliate API integrations) |

---

## 📂 Architecture: Modular Plugins
Obenlo is decentralized across several specialized plugins for maintainability. The system follows a **"One Class, One Role"** modular architecture:

1. **`obenlo-booking`**: The core "Engine." Handles:
   - CPTs: `listing`, `booking`, `ticket`, `broadcast`.
   - **Engine Manager**: A scalable interface that routes listings to different native checkout flows or external affiliate redirects.
   - Payments: Multi-gateway orchestration (Stripe, PayPal).
2. **`obenlo-ai`**: The Automation Hub. Handles:
   - **API Importers:** Integrations with SeatGeek, Viator, Travelpayouts, and Groupon via CJ Affiliate.
   - **AI Content:** Automated blog generation and SEO optimization.
3. **`obenlo-pwa`**: Handles offline experience and "Add to Home" logic.
4. **`obenlo-seo`**: Smart SEO Engine 2.0. Handles location-aware meta and rich snippets.

---

## 🤖 Affiliate Integrations & Auto-Importers
Obenlo features a suite of automated API Importers that pull in global listings and automatically embed affiliate tracking or native booking widgets.

### Supported Integrations
- **🎟️ SeatGeek:** Fetches sports and live entertainment events. Converts the booking form to an affiliate deep-link.
- **🗺️ Viator:** Fetches global tours and experiences. Generates dynamic "Book on Viator" redirects using affiliate IDs.
- **🏨 Travelpayouts (Hotels):** Fetches luxury stays. Dynamically embeds native Hotellook booking widgets right into the listing.
- **💆 Groupon (CJ Affiliate):** Fetches local deals, dining, and spa services. Secures deep-link tracking via Commission Junction.

### The Engine Manager
Every listing has an assigned `_obenlo_listing_engine` (e.g., `seatgeek`, `travelpayouts`, `slot`, `nightly`). The Engine Manager reads this meta and dynamically alters the frontend booking form and checkout logic based on the engine's requirements.

---

## 📖 Documentation & Reference
To maintain a clean repository root, all core logic and guides are centralized in the `/wp-content/docs/` directory:
- **`logic-reference.md`**: Core business rules for Listings and Profiles.
- **`deployment-guide.md`**: Protocol for migrating between environments.
- **`ARCHITECTURE.md`**: Detailed map of the modular PHP classes.

---

## 👑 Site Administrator Quick-Start

### 1. The Auto-Importer Dashboard
Admins can populate the site instantly using the Auto-Importers.
- **Location:** Dashboard > Obenlo Agent.
- **Setup:** API Keys (SeatGeek, Viator, Travelpayouts Token/Marker, CJ Affiliate Token) must be added in the Obenlo Agent Settings before importing.

### 2. Announcement Management
Admins can send broadcasts from the **Site Admin Dashboard**. All announcements are automatically wrapped in a premium crimson-themed template.

### 3. Identity Verification & Support
Review host KYC requests and manage host/guest disputes using the built-in Support Status Filters.

---

## 📈 Performance & Scaling
- **Caching:** Page elements are heavily cached. After making sidebar or API integration changes, ensure a **Hard Refresh**.
- **Optimization:** Use `wp_postmeta` efficiently; always index searches by meta values for listing discovery.

---

**Obenlo Technical Team**
*v2.0.0 - "Automated Global Affiliate Integrations" Release*
