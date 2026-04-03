# Obenlo Listing Logic Guide

This guide summarizes how Obenlo structures its listings. Use this information to help craft optimized, high-converting listings for stay, experience, or service hosts.

## 1. Hierarchical Architecture (Parent-Child)
Obenlo uses a 2-tier system for maximum flexibility:

- **Parent Listing (Business Profile)**: The high-level entity (e.g., a Boutique Hotel, a Tour Agency, or a Personal Trainer). This holds the general description, location, and primary identity.
- **Child Listing (Bookable Unit/Session)**: The specific item the guest actually books (e.g., "Suites with Ocean View", "Sunset Boat Tour", "1-Hour Yoga Session"). Each child has its own pricing, availability, and booking rules.

---

## 2. Core Listing Categories
Obenlo dynamically adapts its interface based on the category:

- **Stay**: Optimized for overnight bookings (Hotels, Villas, Rooms).
- **Experience**: Tailored for activities, tours, and guided adventures.
- **Event**: Built for one-time or recurring events with specific schedules (Classes, Shows, Festivals).
- **Service**: Designed for professional services (Chauffeurs, Cooks, Beauty Services).

---

## 3. Pricing Models
The platform supports 9 distinct pricing structures:

| Model | Use Case |
| :--- | :--- |
| per_night | Accommodations (Stays). |
| per_day | Equipment rentals or full-day services. |
| per_hour | Consultation, rental, or professional services. |
| per_session | Appointments or classes (requires specific duration). |
| per_person | Group tours or ticketed events. |
| per_event | Single flat fee for access to something. |
| per_donation | Fixed support amount (Non-profits/Causes). |
| custom_donation | "Pay what you want" model. |
| lat_fee | One-off services. |

---

## 4. Scheduling & Availability Logic
- **Fixed Events**: Can be locked to a specific date and time (e.g., April 15th, 2 PM - 4 PM).
- **Time Slots**: If equires_slots is enabled, the system automatically generates bookable windows based on the host's business hours.
- **Units vs. Capacity**:
    - **Capacity**: How many people fit in one unit (e.g., "Sleeps 4").
    - **Available Units**: How many of these items can be booked at the *same time* (e.g., "5 Deluxe Rooms available").

---

## 5. Location Types
- **Physical**: Requires a standard address.
- **Virtual**: Specifically built for the "Digital Nomad" economy. Supports Zoom/Google Meet links that are automatically shared with confirmed guests.

---

## 6. Optimization Features
- **Amenities / "What's Included"**: A customizable list to highlight value.
- **Add-ons (Upsells)**: Hosts can offer extras at checkout (e.g., "Airport Transfer", "Breakfast", "Equipment Rental").
- **Custom Cancellation Policies**: Supports global defaults or per-listing custom rules.
