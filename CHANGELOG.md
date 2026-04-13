# CHANGELOG: Obenlo Platform

All notable changes to the **Obenlo Platform** will be documented in this file.

## [1.7.0] - 2026-04-13
### Added
- **Hierarchical Category Workflow**: Enforced a strict parent-child relationship for listing categories. Main Listings are now restricted to top-level Industry Categories (Stay, Experience, Service, Event).
- **Contextual Sub-Category Selection**: Bookable units now offer specialized sub-category selection based on the parent's industry (e.g., "Beauty" as a child of "Service").
- **Smart Category Inheritance**: Child listings now automatically inherit their parent's industry category if no specific sub-type is selected during creation.

### Changed
- **Dashboard UI Optimization**: Refined the category selection interface in the Host Dashboard to streamline the business creation process.

## [1.6.9] - 2026-04-13
### Added
- **Expanded Industry Categories**: Added **Ticket** (under Event) and **Beauty** (under Service) subcategories to support a wider range of booking types.
- **Smart Category Logic**: Implemented parent-aware industry detection in the dashboard and listing templates, ensuring subcategories automatically inherit their parent's pricing models and booking rules.
- **Enhanced Demo Management**: Added administrative controls for Demo Host Logos, Storefront Banners, and Weekly Availability to the Demo Listing form.
- **Demo Asset Migration**: Assets and availability settings defined for Demo Listings are now seamlessly transferred to the host's permanent profile upon account handover.

### Fixed
- **Relaxed Booking Availability**: Removed strict enforcement of operating hours during the booking process. Guests can now book at any time, with hours serving as an informational guide rather than a hard block.
- **Demo Storefront Parity**: Fixed a bug where custom demo business hours were not reflecting correctly on the simulated host storefront.
- **Booking Form Stability**: Fixed a client-side validation error that was disabling the "Reserve" button incorrectly for out-of-hours bookings.

## [1.6.8] - 2026-04-11
### Added
- **Automated Page Restoration Logic**: Safeguards platform stability by ensuring all core pages (Login, Dashboard, Account, Policy pages, etc.) exist in the database with correct templates and shortcodes.
- **Improved Performance**: Implemented a one-time execution guard for database migrations to optimize site speed after platform updates.

### Fixed
- **Dashboard UI Bug**: Fixed undefined `$user` warning in Host Dashboard Overview tab, ensuring consistent display of host profile names.
- **Link Stability**: Corrected login and support shortcode links across the theme to ensure seamless platform navigation.

### Removed
- **Legacy Payment Gateways**: Fully decommissioned and removed MonCash and Natcash logic and legacy files.
- **Unified Versioning**: Established a project-wide versioning standard where all core plugins and the theme share the same version number for better ecosystem synchronization.


## [1.6.7] - 2026-04-07
### Added
- **Broadcast History Management**: Site Admins can now review, filter, and delete past platform announcements directly from the dashboard.
- **Announcements Feed**: Restricted broadcast viewing to "Announcements" tabs in both Host and Guest dashboards for better UX.

### Fixed
- **Audience Filtering**: Fixed a critical leak where "Guest Only" broadcasts were being received by Hosts due to role matching inconsistencies.
- **Branding Consistency**: Standardized the naming from "Broadcasts" to "Announcements" across the entire Guest/Host facing interface.

## [1.6.6] - 2026-04-07
### Fixed
- **Refund Synchronization**: Fixed a bug where refunds were deducting from host balance even if funds hadn't been released yet.
- **Completion Guard**: Restricted booking completion to "Confirmed" bookings only to prevent accidental fund release on cancelled or refunded trips.
- **Broadcast Standardization**: Centralized the global broadcast functionality within the Site Admin Dashboard and removed it from the Host Dashboard to ensure administrator-only access for core platform announcements.

## [1.6.5] - 2026-04-07
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
