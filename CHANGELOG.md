# CHANGELOG: Obenlo Platform

All notable changes to the **Obenlo Platform** will be documented in this file.

## [1.6.6] - 2026-04-09
### Fixed
- **Refund Synchronization**: Fixed a bug where refunds were deducting from host balance even if funds hadn't been released yet.
- **Completion Guard**: Restricted booking completion to "Confirmed" bookings only to prevent accidental fund release on cancelled or refunded trips.

## [1.6.5] - 2026-04-08
### Added
- **Payout Restriction**: Host earnings are now only released to their balance after a booking is marked as "Completed".
- **Refined Earnings Logic**: Separated calculation from balance updates for better financial auditability.
- **Improved Stability**: Added safeguards to prevent duplicate earnings being credited to host balances.

## [1.6.4] - 2026-04-07
### Added
- **Payout Management System**: Created a dedicated administrative tab for managing host withdrawal requests.
- **Unified Payout Controls**: Consolidated "Pending" and "Processed" payout tables into a single, high-performance view.
- **Host Payout Insights**: Added a "Payout Settings" column to the User Management dashboard, allowing admins to instantly view host payment methods and account details.
- **Automated Disbursement**: Direct "MonCash API" button in the Payouts tab for instantaneous funds transfer to Haitian hosts.
- **Administrative Deletion Logic**: Implemented permanent deletion for Demo Listings and User Accounts (Host/Guest) directly from the Site Admin Dashboard.
- **Cascading Deletion**: Deleting a Demo Listing now automatically removes all associated child units to maintain database integrity.
- **Demo Visibility Control**: New "Hide/Show" toggle for administrators in the Demo Listing Manager to manage public storefront availability.
- **Private Storefronts**: Hidden demo listings and their associated virtual host profiles are now strictly excluded from public site queries and direct visitor access.

### Improved
- **Safety Confirmations**: Added browser-level confirmation prompts for all destructive administrative actions (Delete/Restore).

## [1.6.1] - 2026-04-07
### Fixed
- **Permission Patch**: Resolved "Invalid listing" error for administrators when editing demo host listings.

## [1.6.0] - 2026-04-07
### Added
- **Automated Host Onboarding**: Sync Google Form submissions directly to Obenlo via REST API.
- **Instant Demo Creation**: Automated generation of "Demo" storefronts with instant, public-facing preview links.
- **Branded Outreach System**: Automatic, professional recruitment emails sent from `info@obenlo.com` upon form submission.
- **Footer Navigation**: Added "Request a Demo" CTA and streamlined "Become a Host" accessibility.

## [1.5.0] - 2026-04-05
### Added
- **Premium Mobile Navigation**: Implemented a fixed bottom navigation system for both Site Admin and Frontend Host Dashboards.
- **Card-Based Table Logic**: Standardized administrative tables across all dashboards to automatically transform into high-end card layouts on mobile.

## [1.4.0] - 2026-04-05
### Added
- **Guest Refund System**: Guests can now request refunds for Confirmed bookings directly from "My Trips".
- **Host Refund Management**: Real-time "Approve" and "Reject" actions for hosts within their dashboard.
- **Automated Financial Sync**: Host balance is automatically adjusted on refund approval (if already released).

## [1.0.0] - 2026-03-25
### Initial Release
- Baseline release of the bespoke Obenlo Booking engine and Premium Theme.
