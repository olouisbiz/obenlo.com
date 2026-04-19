# Changelog - Obenlo Booking

All notable changes to this project will be documented in this file.

## [2.0.0] - 2026-04-19
### Added
- **Modular Booking Engines**: Completely restructured the booking system into a factory-based architecture (`Obenlo_Engine_Manager`) managing 6 isolated engines: `Nightly`, `Fixed Block`, `Session`, `Logistics`, `Slot`, and `Inquiry`.
- **Dynamic Host Dashboard**: The host UI is now AJAX-driven. Engine modules automatically load their specific pricing and settings fields dynamically when a listing subcategory changes.
- **Smart Logistics Mapping**: Added service date and time fallbacks, properly distinguishing mobile services (Slot engine) from Route services (Logistics engine).
- **Subcategory Taxonomy Resolver**: Subcategory lookup now handles WP auto-suffixed slugs and delegates fallback efficiently.



## [1.9.0] - 2026-04-19
### Added
- **Schedule / Session Runs**: Fully functional repeater system for recurring Experiences, Tours, and Classes.
- **Category Expansion**: New support for `Class`, `Show`, and `Event` sub-categories in the booking engine.
- **Dynamic Filtering**: Guest view now automatically filters recurring session times based on the selected date's day of the week.
- **Service Engine**: Added slot-based pricing support for subcategories like Barber, Hairdresser, and Beauty services.

### Fixed
- **Encoding Repairs**: Performed a deep scan and fixed all broken character sequences (`ðŸ“`, `â˜…`, etc.) across the entire platform.
- **Data Integrity**: Migrated recurring session data to a robust JSON-based meta key `_obenlo_session_runs`.
- **Admin Visibility**: Fixed icons and ownership labels in the Admin Demo Listing Manager.

### Changed
- **Premium UI**: Standardized high-resolution emojis (📍, 🏢, ✨, 🕒, 📦) across templates for a more premium look.
- **Booking Flow**: Updated the Single Listing sidebar to sync with the new Session/Run logic.

## [1.8.0] - 2026-04-15
### Changed
- Minor architectural modularization of the Host Dashboard.
