# API & Request Validation Guide

## Request Validation Rules

### Booking Creation Request

```php
// routes/web.php
Route::post('/booking', [BookingController::class, 'store'])->validate([
    'service_id' => 'required|exists:services,id',
    'scheduled_date' => 'required|date|after:now|before:+60 days',
    'notes' => 'nullable|string|max:500',
    'voucher_id' => 'nullable|exists:vouchers,id',
]);
```

**Response (Success):**
```json
{
    "success": true,
    "booking": {
        "id": 1,
        "booking_code": "BK-20240225120530-ABC123",
        "user_id": 1,
        "service_id": 2,
        "scheduled_date": "2024-03-10 14:00:00",
        "status": "pending",
        "total_price": 450000,
        "discount_amount": 50000,
        "created_at": "2024-02-25T12:05:30Z"
    }
}
```

**Response (Failure - Double Booking):**
```json
{
    "success": false,
    "message": "User already has a booking at this time",
    "booking": null
}
```

### Car Listing Search

```php
// Query Parameters
GET /garage?search=Toyota&min_price=100000000&max_price=500000000&fuel_type=petrol&year=2023
```

**Search Parameters:**
- `search`: Brand, model, or color (nullable)
- `min_price`: Minimum price in Rupiah
- `max_price`: Maximum price in Rupiah
- `fuel_type`: petrol|diesel|hybrid|electric
- `year`: Vehicle year

### Voucher Claim Request

```php
// routes/web.php
Route::post('/voucher/{voucher}/claim', [VoucherController::class, 'store'])
```

**Validation:**
- Voucher must be active
- Voucher must not be expired
- User must not have previously claimed the voucher
- Voucher must have uses remaining

## Error Handling

### Error Response Structure

```json
{
    "success": false,
    "message": "User-friendly error message",
    "errors": {
        "field_name": ["Error detail 1", "Error detail 2"]
    }
}
```

### Common HTTP Status Codes

- `200`: Success
- `201`: Created
- `400`: Bad Request (validation error)
- `401`: Unauthorized
- `403`: Forbidden (role/permission issue)
- `404`: Not Found
- `422`: Unprocessable Entity (validation failed)
- `500`: Server Error

## Authentication Endpoints

### Login

```php
POST /login
Content-Type: application/json

{
    "email": "user@example.com",
    "password": "password"
}
```

### Register

```php
POST /register
Content-Type: application/json

{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password",
    "password_confirmation": "password"
}
```

### Logout

```php
POST /logout
Authorization: Bearer {token}
```

## Rate Limiting

Rate limits are applied per IP address:
- **General**: 60 requests per minute
- **Login**: 5 attempts per minute
- **Booking**: 10 bookings per hour per user

## Pagination

List endpoints support pagination:

```php
GET /garage?page=1&per_page=12
```

**Response:**
```json
{
    "data": [...],
    "current_page": 1,
    "per_page": 12,
    "total": 145,
    "last_page": 13,
    "from": 1,
    "to": 12
}
```

## Eager Loading Queries

All list endpoints use eager loading to prevent N+1 queries:

```php
// Bookings with relationships
Booking::with('user', 'service', 'voucher')
    ->where('status', 'pending')
    ->get();

// Cars with seller info
Car::with('seller')
    ->available()
    ->get();

// Testimonials with user details
Testimonial::with('user')
    ->approved()
    ->get();
```
