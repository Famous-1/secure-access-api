# New API Endpoints Quick Reference

## Authentication Endpoints

### Set Password (New User Setup)
```
POST /api/set-password
Body: {
  "email": "user@example.com",
  "token": "uuid-token-from-email",
  "password": "newpassword",
  "password_confirmation": "newpassword"
}
```

---

## Maintainer Endpoints (Requires Admin or Maintainer Role)

### View All Visitor Codes
```
GET /api/maintainer/visitor-codes
Headers: Authorization: Bearer {token}
Query Params: ?status=active&from_date=2025-01-01&to_date=2025-12-31
```

### View All Activities
```
GET /api/maintainer/activities
Headers: Authorization: Bearer {token}
```

### Activity Statistics
```
GET /api/maintainer/activities/statistics
Headers: Authorization: Bearer {token}
```

---

## Visitor Code Endpoints (Updated)

### Verify by Code
```
POST /api/visitor-codes/verify-by-code
Headers: Authorization: Bearer {token}
Body: {
  "code": "ABC123"
}
Response: Success even if already verified (HTTP 200)
```

### Set Time In
```
POST /api/visitor-codes/{id}/time-in
Headers: Authorization: Bearer {token}
```

### Set Time Out
```
POST /api/visitor-codes/{id}/time-out
Headers: Authorization: Bearer {token}
```

---

## Complaint Endpoints (Updated)

### Reply to Complaint (Admin Only)
```
POST /api/admin/complaints/{id}/reply
Headers: Authorization: Bearer {token}
Body: {
  "message": "Reply message here..."
}
```

### Update Complaint Status (Admin Only)
```
PUT /api/admin/complaints/{id}
Headers: Authorization: Bearer {token}
Body: {
  "status": "acknowledged|in_progress|resolved|closed",
  "admin_notes": "Optional notes..."
}
```

### View Complaint with Replies (User)
```
GET /api/complaints/{id}
Headers: Authorization: Bearer {token}
Response includes: complaint details + all replies
```

### View Complaint with Replies (Admin)
```
GET /api/admin/complaints/{id}
Headers: Authorization: Bearer {token}
Response includes: complaint details + all replies + user info
```

---

## Announcement Endpoints (New)

### Create Announcement (Admin Only)
```
POST /api/admin/announcements
Headers: Authorization: Bearer {token}
Body: {
  "title": "Important Notice",
  "content": "Announcement content...",
  "priority": "low|normal|high|urgent",
  "published_at": "2025-10-24 12:00:00",  // Optional
  "expires_at": "2025-10-31 23:59:59",    // Optional
  "is_active": true
}
```

### Update Announcement (Admin Only)
```
PUT /api/admin/announcements/{id}
Headers: Authorization: Bearer {token}
Body: {
  "title": "Updated Title",           // Optional
  "content": "Updated content...",     // Optional
  "priority": "high",                  // Optional
  "published_at": "2025-10-24 12:00:00", // Optional
  "expires_at": "2025-10-31 23:59:59",   // Optional
  "is_active": false                   // Optional
}
```

### Delete Announcement (Admin Only)
```
DELETE /api/admin/announcements/{id}
Headers: Authorization: Bearer {token}
```

### View All Announcements (Admin)
```
GET /api/admin/announcements
Headers: Authorization: Bearer {token}
Query Params: ?priority=high&is_active=true
Response: All announcements including inactive
```

### View Active Announcements (All Users)
```
GET /api/announcements
Headers: Authorization: Bearer {token}
Query Params: ?priority=urgent
Response: Only active, non-expired, published announcements
```

### View Specific Announcement (All Users)
```
GET /api/announcements/{id}
Headers: Authorization: Bearer {token}
```

---

## User Management (Admin Only - Updated)

### Create User
```
POST /api/admin/users
Headers: Authorization: Bearer {token}
Body: {
  "firstname": "John",
  "lastname": "Doe",
  "email": "john@example.com",
  "phone": "+1234567890",
  "apartment_unit": "A101",
  "full_address": "123 Main St",
  "usertype": "resident|admin|maintainer",
  "status": "active|inactive|suspended"
}
Response: User created + email sent with password setup link
```

---

## Complaint Status Values

Available status values for complaints:
- `pending` - Initial status when complaint is submitted
- `acknowledged` - Admin has seen the complaint
- `in_progress` - Admin is working on it (auto-set when first reply is added)
- `resolved` - Issue has been resolved
- `closed` - Complaint is closed

---

## Priority Levels for Announcements

- `low` - General information
- `normal` - Standard announcement (default)
- `high` - Important announcement
- `urgent` - Critical/urgent announcement

---

## User Types

- `admin` - Full access to all features
- `maintainer` - Access to visitor codes and activities only
- `resident` - Regular user with access to own data
- `user` - Standard user
- `vendor` - Vendor user
- `installer` - Installer user

---

## Permission Matrix

| Feature | Admin | Maintainer | Resident/User |
|---------|-------|------------|---------------|
| User Management | ✅ | ❌ | ❌ |
| Visitor Codes (View All) | ✅ | ✅ | ❌ |
| Visitor Codes (Own) | ✅ | ✅ | ✅ |
| Verify Visitor Codes | ✅ | ✅ | ❌ |
| Complaints (View All) | ✅ | ❌ | ❌ |
| Complaints (Own) | ✅ | ❌ | ✅ |
| Reply to Complaints | ✅ | ❌ | ❌ |
| Announcements (Manage) | ✅ | ❌ | ❌ |
| Announcements (View) | ✅ | ✅ | ✅ |
| Activities (View All) | ✅ | ✅ | ❌ |
| Activities (Own) | ✅ | ✅ | ✅ |

---

## Response Format

All responses follow this format:

### Success Response
```json
{
  "success": true,
  "message": "Operation successful",
  "data": { /* response data */ }
}
```

### Error Response
```json
{
  "success": false,
  "message": "Error message",
  "errors": { /* validation errors if any */ }
}
```

---

## Testing Examples

### 1. Test Password Setup Email
```bash
# As Admin, create a user
curl -X POST http://localhost:8000/api/admin/users \
  -H "Authorization: Bearer {admin-token}" \
  -H "Content-Type: application/json" \
  -d '{
    "firstname": "Test",
    "lastname": "User",
    "email": "test@example.com",
    "phone": "+1234567890",
    "apartment_unit": "A101",
    "full_address": "123 Test St",
    "usertype": "resident",
    "status": "active"
  }'

# Check email for token, then set password
curl -X POST http://localhost:8000/api/set-password \
  -H "Content-Type: application/json" \
  -d '{
    "email": "test@example.com",
    "token": "token-from-email",
    "password": "newpassword123",
    "password_confirmation": "newpassword123"
  }'
```

### 2. Test Complaint Reply
```bash
# As Admin, reply to complaint
curl -X POST http://localhost:8000/api/admin/complaints/1/reply \
  -H "Authorization: Bearer {admin-token}" \
  -H "Content-Type: application/json" \
  -d '{
    "message": "We are working on your complaint. Thank you for your patience."
  }'
```

### 3. Test Announcement Creation
```bash
# As Admin, create announcement
curl -X POST http://localhost:8000/api/admin/announcements \
  -H "Authorization: Bearer {admin-token}" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Maintenance Schedule",
    "content": "Building maintenance will be performed on Sunday from 9 AM to 5 PM.",
    "priority": "high",
    "expires_at": "2025-10-31 23:59:59"
  }'
```

---

## Scheduled Tasks

Run this command to manually expire visitor codes:
```bash
php artisan visitor-codes:expire
```

To set up automatic expiration (runs hourly), ensure your cron is configured:
```bash
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

