# CHANGELOG: Obenlo Platform

All notable changes to the **Obenlo Platform** will be documented in this file.

## [1.6.4] - 2026-04-07
### Added
- **Payout Management System**: Created a dedicated administrative tab for managing host withdrawal requests.
- **Unified Payout Controls**: Consolidated "Pending" and "Processed" payout tables into a single, high-performance view.
- **Host Payout Insights**: Added a "Payout Settings" column to the User Management dashboard, allowing admins to instantly view host payment methods and account details.
- **Automated Disbursement**: Direct "MonCash API" button in the Payouts tab for instantaneous funds transfer to Haitian hosts.
### Added
- **Administrative Deletion Logic**: Implemented permanent deletion for Demo Listings and User Accounts (Host/Guest) directly from the Site Admin Dashboard.
- **Cascading Deletion**: Deleting a Demo Listing now automatically removes all associated child units.
- **Safety Confirmations**: Added browser-level confirmation prompts for all destructive administrative actions.
### Added
- **Demo Visibility Control**: New "Hide/Show" toggle for administrators in the Demo Listing Manager. 
- **Private Storefronts**: Hidden demo listings and their associated virtual host profiles are now strictly excluded from public site queries and direct visitor access (404).

## [1.6.1] - 2026-04-07
### Fixed
- Listing Edit permissions for Administrators (resolving 'Invalid listing' error on demo listings).

## [1.6.0] - 2026-04-07
### Added
- **Automated Host Onboarding**: Sync Google Form submissions directly to Obenlo via REST API.
- **Instant Demo Creation**: Automated generation of "Demo" storefronts with instant, public-facing preview links.
- **Branded Outreach System**: Automatic, professional recruitment emails sent from `info@obenlo.com` upon form submission.
- **Smart Keyword Mapping**: Resilient form-to-field matching that allows for flexible question naming in Google Forms.
- **Footer Navigation**: Added "Request a Demo" CTA and streamlined "Become a Host" accessibility.

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
