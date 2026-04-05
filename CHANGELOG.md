# Changelog

All notable changes to the **Obenlo Platform** will be documented in this file.

## [1.5.0] - 2026-04-05
### Added
- **Premium Mobile Navigation**: Implemented a fixed bottom navigation system for both Site Admin and Frontend Host Dashboards, providing an app-like experience on mobile.
- **Card-Based Table Logic**: Standardized administrative tables across all dashboards to automatically transform into high-end card layouts on mobile devices.
- **Responsive Form Layouts**: Added intelligent wrapping for business hours and vacation blocks to ensure a seamless touch experience on smaller screens.

### Improved
- **UI/UX Consistency**: Unified the design language (Inter typography, Crimson accents, shadow-based interaction feedback) across all administrative interfaces.
- **Horizontal Stability**: Resolved overlapping content issues in mobile card views by implementing a space-between flexbox layout for labels and data.

## [1.4.0] - 2026-04-05
### Added
- **Guest Refund System**: Guests can now request refunds for Confirmed, Declined, or Cancelled (if paid) bookings directly from "My Trips".
- **Guest Cancellation**: Added ability for guests to cancel Pending and Pending Payment bookings.
- **Host Refund Management**: Real-time "Approve" and "Reject" actions for hosts within their dashboard.
- **Automated Financial Sync**: Host balance (`_obenlo_host_balance`) is automatically adjusted on refund approval.
- **Refund Status Workflow**: Comprehensive backend for tracking refund requests via a new `refund` post type.

### Improved
- **Dashboard Visibility**: Unified access to booking actions across all device types.
- **Secure Transaction Handling**: Enhanced nonce-based security for all guest and host financial actions.

## [1.3.0] - 2026-04-04
### Added
- Unified version synchronization across the entire ecosystem (Theme + 5 Plugins).
- Introduced `CHANGELOG.md` for professional version tracking.
- Created `sync.ps1` maintenance script for effortless repository synchronization.
- **Host Dashboard**: Visual badges for **[PARENT]** and **[UNIT]** listings to improve inventory oversight.
- **Notifications**: Refined booking alerts to trigger only upon **payment completion**, eliminating noise from abandoned/unpaid booking attempts.

### Fixed
- **Host Storefront**: Synchronized "Hosted by" data key to prioritize the custom Store Name over the internal WordPress username.
- **Branding**: Public Display Name now automatically syncs with the Store Name when saved in the dashboard.
- **Dashboard Bug**: Fixed a copy-paste error where Host Specialties were being overwritten by the Store Name.
- **PWA**: Refined the install prompt logic and notification request sequence.

---

## [1.0.0] - 2026-03-25
### Initial Release
- Baseline release of the bespoke Obenlo Booking engine and Premium Theme.
