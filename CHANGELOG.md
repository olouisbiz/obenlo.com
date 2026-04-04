# Changelog

All notable changes to the **Obenlo Platform** will be documented in this file.

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
