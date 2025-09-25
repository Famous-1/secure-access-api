# Secure Access Estate Management API Documentation

## Overview
This API provides estate management functionality including user management, visitor codes, complaints/suggestions, and activity tracking.

## Authentication
All API endpoints require authentication using Laravel Sanctum. Include the Bearer token in the Authorization header:
```
Authorization: Bearer {token}
```

## Base URL
```
/api
```

## User Management (Admin Only)

### Get All Users
```
GET /api/admin/users
```
**Query Parameters:**
- `usertype` - Filter by user type (resident, admin, maintainer)
- `status` - Filter by status (active, inactive, suspended)

**Response:**
```json
{
  "success": true,
  "data": {
    "current_page": 1,
    "data": [...],
    "per_page": 15,
    "total": 10
  }
}
```

### Create User
```
POST /api/admin/users
```
**Request Body:**
```json
{
  "firstname": "John",
  "lastname": "Doe",
  "email": "john@example.com",
  "phone": "1234567890",
  "apartment_unit": "Apt 101, Block A",
  "full_address": "123 Main Street, City, State 12345",
  "usertype": "resident",
  "status": "active"
}
```

### Get User Details
```
GET /api/admin/users/{id}
```

### Update User
```
PUT /api/admin/users/{id}
```

### Delete User
```
DELETE /api/admin/users/{id}
```

## Visitor Codes

### Get My Visitor Codes
```
GET /api/visitor-codes
```
**Query Parameters:**
- `status` - Filter by status (active, used, expired, cancelled)

### Create Visitor Code
```
POST /api/visitor-codes
```
**Request Body:**
```json
{
  "visitor_name": "Alice Brown",
  "phone_number": "9876543210",
  "destination": "Apt 101, Block A",
  "number_of_visitors": 2,
  "expires_at": "2024-01-01 18:00:00",
  "additional_notes": "Family visit"
}
```

### Get Visitor Code Details
```
GET /api/visitor-codes/{id}
```

### Verify Visitor Code (Admin/Maintainer)
```
POST /api/visitor-codes/{id}/verify
```

### Cancel Visitor Code
```
POST /api/visitor-codes/{id}/cancel
```

### Verify by Code String (Admin/Maintainer)
```
POST /api/visitor-codes/verify-by-code
```
**Request Body:**
```json
{
  "code": "ABC123"
}
```

### Get All Visitor Codes (Admin/Maintainer)
```
GET /api/admin/visitor-codes
```
**Query Parameters:**
- `status` - Filter by status
- `from_date` - Filter from date
- `to_date` - Filter to date

## Complaints & Suggestions

### Get My Complaints
```
GET /api/complaints
```
**Query Parameters:**
- `type` - Filter by type (complaint, suggestion)
- `status` - Filter by status (pending, in_progress, resolved, closed)
- `severity` - Filter by severity (low, medium, high, critical)
- `category` - Filter by category

### Create Complaint/Suggestion
```
POST /api/complaints
```
**Request Body:**
```json
{
  "type": "complaint",
  "category": "Maintenance",
  "severity": "medium",
  "title": "Broken Elevator",
  "description": "The elevator in Block A has been out of order for 2 days."
}
```

### Get Complaint Categories
```
GET /api/complaints/categories
```

### Get Complaint Details
```
GET /api/complaints/{id}
```

### Update Complaint (Admin)
```
PUT /api/complaints/{id}
```
**Request Body:**
```json
{
  "status": "in_progress",
  "admin_notes": "Working on it"
}
```

### Get All Complaints (Admin)
```
GET /api/admin/complaints
```

### Get Complaint Statistics (Admin)
```
GET /api/admin/complaints/statistics
```

## Activities

### Get My Activities
```
GET /api/activities
```
**Query Parameters:**
- `action` - Filter by action
- `from_date` - Filter from date
- `to_date` - Filter to date
- `recent_days` - Filter by recent days

### Get Recent Activities
```
GET /api/activities/recent
```
**Query Parameters:**
- `days` - Number of recent days (default: 7)
- `limit` - Number of activities (default: 10)
- `user_id` - Filter by user (Admin only)

### Get Activity Details
```
GET /api/activities/{id}
```

### Get Available Actions
```
GET /api/activities/actions
```

### Get All Activities (Admin)
```
GET /api/admin/activities
```

### Get Activity Statistics (Admin)
```
GET /api/admin/activities/statistics
```

## User Types

### Resident
- Can create visitor codes
- Can submit complaints/suggestions
- Can view their own activities
- Can view their own visitor codes

### Admin
- Full access to all endpoints
- Can manage users
- Can view all activities
- Can verify visitor codes
- Can manage complaints

### Maintainer
- Can verify visitor codes
- Can view all activities
- Can view all visitor codes

## Sample Data

The system comes with sample data including:
- Admin user: `admin@secure-access.com` / `admin123`
- Sample residents with visitor codes
- Sample complaints and activities

## Error Responses

All endpoints return consistent error responses:

```json
{
  "success": false,
  "message": "Error description",
  "errors": {
    "field": ["Validation error message"]
  }
}
```

## Status Codes

- `200` - Success
- `201` - Created
- `400` - Bad Request
- `401` - Unauthorized
- `403` - Forbidden
- `404` - Not Found
- `422` - Validation Error
- `500` - Server Error
