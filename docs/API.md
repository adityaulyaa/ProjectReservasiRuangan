# API Routes Documentation

## Public Routes (No Auth Required)

### Homepage & Facilities
```
GET  /                              PublicController@index
GET  /facilities                    PublicController@facilities
GET  /facilities/{id}/availability  PublicController@facilityAvailability
```

---

## Authentication Routes (Laravel Breeze)

```
GET   /login                     Auth\LoginController (Breeze)
POST  /login                     Auth\LoginController (Breeze)
POST  /logout                    Auth\LogoutController (Breeze)
GET   /register                  Auth\RegisterController (Breeze)
POST  /register                  Auth\RegisterController (Breeze)
GET   /forgot-password           Auth\PasswordResetController (Breeze)
POST  /forgot-password           Auth\PasswordResetController (Breeze)
```

---

## User Routes (Auth + Verified Required)

### Dashboard
```
GET  /dashboard                   User\DashboardController@index
```

### Profile
```
GET    /profile                   ProfileController@edit
PATCH  /profile                   ProfileController@update
DELETE /profile                   ProfileController@destroy
```

### Reservations (Auth + Role:user)
```
GET   /reservations               User\ReservationController@index
GET   /reservations/create        User\ReservationController@create
POST  /reservations               User\ReservationController@store
GET   /reservations/{id}          User\ReservationController@show
POST  /reservations/{id}/cancel   User\ReservationController@cancel
```

### Reports (Auth + Role:user)
```
GET   /reports                    User\ReportController@index
GET   /reports/create             User\ReportController@create
POST  /reports                    User\ReportController@store
GET   /reports/{id}               User\ReportController@show
```

---

## Staff Routes (Auth + Role:staff)

**Prefix:** `/staff`

### Dashboard
```
GET  /staff/dashboard             Staff\DashboardController@index
```

### Reservations Management
```
GET   /staff/reservations/queue          Staff\ReservationController@queue
POST  /staff/reservations/{id}/approve   Staff\ReservationController@approve
POST  /staff/reservations/{id}/reject    Staff\ReservationController@reject
POST  /staff/reservations/{id}/cancel    Staff\ReservationController@cancel
```

### Reports Management
```
GET   /staff/reports/queue                Staff\ReportController@queue
GET   /staff/reports/{id}                 Staff\ReportController@show
POST  /staff/reports/{id}/status          Staff\ReportController@updateStatus
POST  /staff/reports/{id}/maintenance     Staff\ReportController@markMaintenance
POST  /staff/reports/{id}/active          Staff\ReportController@markActive
```

---

## Admin Routes (Auth + Role:admin)

**Prefix:** `/admin`

### Dashboard
```
GET  /admin/dashboard             Admin\DashboardController@index
```

### Facilities Management
```
GET     /admin/facilities              Admin\FacilityController@index
GET     /admin/facilities/create       Admin\FacilityController@create
POST    /admin/facilities              Admin\FacilityController@store
GET     /admin/facilities/{id}/edit    Admin\FacilityController@edit
PATCH   /admin/facilities/{id}         Admin\FacilityController@update
DELETE  /admin/facilities/{id}         Admin\FacilityController@destroy
```

### User Management
```
GET   /admin/users                Admin\UserController@index
GET   /admin/users/create         Admin\UserController@create
POST  /admin/users                Admin\UserController@store
POST  /admin/users/{id}/verify    Admin\UserController@verify
POST  /admin/users/{id}/reject    Admin\UserController@reject
```

### Reports Overview
```
GET  /admin/reports               Admin\ReportController@index
GET  /admin/reports/{id}          Admin\ReportController@show
GET  /admin/reports/export        Admin\ReportController@export
```

---

## Middleware

| Middleware | Description |
|------------|-------------|
| `auth` | User must be logged in |
| `verified` | Email must be verified |
| `role:admin` | User role must be 'admin' |
| `role:staff` | User role must be 'staff' |
| `role:user` | User role must be 'user' |

---

## Response Format

### Success Response
```json
{
  "success": true,
  "message": "Operation successful",
  "data": { ... }
}
```

### Error Response
```json
{
  "success": false,
  "message": "Error message",
  "errors": { ... }
}
```

### Validation Error
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "field_name": ["Error message"]
  }
}
```

---

## Testing Routes

```bash
# List all routes
php artisan route:list

# Filter by name
php artisan route:list --name=admin

# Filter by method
php artisan route:list --method=POST
```
