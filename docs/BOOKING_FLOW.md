# Booking Flow & Tests

This document focuses on the Slot Booking UX and the server-side logic behind it.

## User Flow

1. Choose room on /user/slot-booking
2. Slot picker: /user/slot-booking/room/{id}
   - Date carousel (14 days)
   - Hour grid (06:00–24:00), 1-hour blocks
   - Click to select start; click adjacent hours to extend the range
  - Summary updates in real time (date & range) — tanpa harga
3. Continue → Form Booking (GET /user/slot-booking/form) with hidden inputs
4. Submit (POST /user/slot-booking) → Booking created with status "proses"

## Availability API

GET /user/slot-booking/available?id_room=ID&tanggal=YYYY-MM-DD

Response example:

```
{
  "date": "2025-11-11",
  "room_id": 3,
  "free": [ { "start": "06:00", "end": "09:00", "duration": 3 }, ... ],
  "occupied": [ { "start": "09:00", "end": "11:00", "status": "proses" } ],
  "blocked": false
}
```

Notes:
- Blocked = true when a Jadwal Reguler covers that date (full-day off)
- Occupied joins all bookings (status proses|diterima) overlapping the day

## Non-Pricing

Sistem non-berbayar: tidak ada tarif per jam/segmen maupun total biaya.
Metode `Room::priceForRange($start, $end)` dinonaktifkan dan selalu menghasilkan 0 untuk kompatibilitas mundur.

## Tests

Run the full suite:

```
php vendor/bin/phpunit --testdox
```

Included tests:
- Feature/SlotAvailableTest: blocked day and occupied intervals
- Unit/OverlapTimeTest: utilitas waktu dan rentang jam yang bersinggungan
- Feature/SlotBookingStoreTest: create multi-hour booking, reject overlapping booking

## Troubleshooting

- If the hour grid shows an error banner, the API likely returned 500 or HTML. The UI falls back to base hours so users can still select; check storage/logs/laravel.log.
- Ensure you’re logged in; availability requires auth.
