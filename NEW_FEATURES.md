# New Features Implementation

This document describes all the newly implemented features in the Secure Access API.

## 1. Email to Set Password After Account Creation ✅

When an admin creates a user account, instead of setting a default password, the system now:
- Generates a unique password setup token
- Sends an email to the new user with the token
- Token expires after 24 hours
- User can set their own password using the token

### Endpoints:
- **POST** `/api/set-password`
  - Body: `{ email, token, password, password_confirmation }`
  - Sets the user's password using the token sent via email

### Related Files:
- `app/Notifications/SetPasswordNotification.php` - Email notification
- `app/Http/Controllers/Api/AuthController.php` - setPassword method
- `app/Http/Controllers/Api/EstateUserController.php` - Modified to send setup email
- Migration: `2025_10_24_000001_add_password_setup_fields_to_users_table.php`

---

## 2. Visitor Code Expiration Handling ✅

Visitor codes now properly handle expiration:
- Automatic expiration marking via scheduled command
- Real-time expiration check when verifying codes
- Status updated to "expired" when code expires

### Features:
- Hourly cron job to mark expired codes
- Manual command: `php artisan visitor-codes:expire`
- Automatic status update when attempting to verify expired code

### Related Files:
- `app/Console/Commands/ExpireVisitorCodes.php` - Command to expire codes
- `app/Console/Kernel.php` - Scheduled to run hourly
- `app/Http/Controllers/Api/VisitorCodeController.php` - Updated verification logic

---

## 3. Success Message for Already Verified Codes ✅

When trying to verify an already verified visitor code:
- Returns HTTP 200 (success) instead of 400 (bad request)
- Includes success message: "Visitor code has already been verified"
- Returns the visitor code data including user and verifier info

### Endpoint:
- **POST** `/api/visitor-codes/verify-by-code`
  - Body: `{ code }`
  - Returns success even if already verified

### Related Files:
- `app/Http/Controllers/Api/VisitorCodeController.php` - verifyByCode method

---

## 4. Maintainer Role and Permissions ✅

New user role "Maintainer" with specific permissions:

### Maintainer Can:
- View all visitor codes
- Verify visitor codes
- Set time in/out for visitors
- View activities and statistics

### Maintainer Cannot:
- Manage users
- View or manage complaints
- View or manage announcements

### Endpoints:
- **GET** `/api/maintainer/visitor-codes` - View all visitor codes
- **GET** `/api/maintainer/activities` - View all activities
- **GET** `/api/maintainer/activities/statistics` - Activity statistics
- **POST** `/api/visitor-codes/{id}/time-in` - Set time in
- **POST** `/api/visitor-codes/{id}/time-out` - Set time out
- **POST** `/api/visitor-codes/{id}/verify` - Verify code
- **POST** `/api/visitor-codes/verify-by-code` - Verify by code string

### Related Files:
- `app/Http/Middleware/MaintainerMiddleware.php` - New middleware
- `app/Http/Kernel.php` - Registered middleware
- `routes/api.php` - New maintainer routes

---

## 5. Complaint Reply Functionality ✅

Admins can now reply to complaints:
- Multiple replies per complaint
- Replies tracked with user and timestamp
- Automatic status update to "in_progress" when first reply is added
- Users can view replies on their complaints

### Endpoints:
- **POST** `/api/admin/complaints/{id}/reply`
  - Body: `{ message }`
  - Admin only - Add reply to complaint
- **GET** `/api/complaints/{id}` - View complaint with replies (users)
- **GET** `/api/admin/complaints/{id}` - View complaint with replies (admin)

### Database:
- New table: `complaint_replies`
  - `id`, `complaint_id`, `user_id`, `message`, `created_at`, `updated_at`

### Related Files:
- `app/Models/ComplaintReply.php` - New model
- `app/Models/Complaint.php` - Added replies relationship
- `app/Http/Controllers/Api/ComplaintController.php` - reply method
- Migration: `2025_10_24_000002_create_complaint_replies_table.php`

---

## 6. Announcement System ✅

Complete announcement system for admins to create, update, and delete announcements:

### Features:
- Create announcements with title, content, and priority
- Set publication date (schedule announcements)
- Set expiration date
- Priority levels: low, normal, high, urgent
- Active/inactive status
- Soft deletes

### Admin Endpoints:
- **GET** `/api/admin/announcements` - View all announcements (including inactive)
- **POST** `/api/admin/announcements` - Create new announcement
- **PUT** `/api/admin/announcements/{id}` - Update announcement
- **DELETE** `/api/admin/announcements/{id}` - Delete announcement

### User Endpoints:
- **GET** `/api/announcements` - View active announcements
- **GET** `/api/announcements/{id}` - View specific announcement

### Request Body (Create/Update):
```json
{
  "title": "Important Notice",
  "content": "Announcement content here...",
  "priority": "high",
  "published_at": "2025-10-24 12:00:00",
  "expires_at": "2025-10-31 23:59:59",
  "is_active": true
}
```

### Related Files:
- `app/Models/Announcement.php` - New model
- `app/Http/Controllers/Api/AnnouncementController.php` - New controller
- Migration: `2025_10_24_000003_create_announcements_table.php`
- `routes/api.php` - Announcement routes

---

## 7. Forgot Password for All User Types ✅

Password reset functionality now works for all user types (admin, maintainer, resident, user, etc.):
- Added missing database fields
- Updated User model to support password reset
- Works universally for all user types

### Endpoints:
- **POST** `/api/forgot-password`
  - Body: `{ email }`
  - Sends password reset token via email
- **POST** `/api/reset-password`
  - Body: `{ email, code, password, password_confirmation }`
  - Resets password using token

### Related Files:
- `app/Http/Controllers/Api/AuthController.php` - forgotPassword & resetPassword methods
- `app/Models/User.php` - Added password_reset fields
- Migration: `2025_10_24_000004_add_password_reset_fields_to_users_table.php`

---

## Summary of Changes

### New Files Created:
1. `app/Notifications/SetPasswordNotification.php`
2. `app/Console/Commands/ExpireVisitorCodes.php`
3. `app/Http/Middleware/MaintainerMiddleware.php`
4. `app/Models/ComplaintReply.php`
5. `app/Models/Announcement.php`
6. `app/Http/Controllers/Api/AnnouncementController.php`
7. 4 new migration files

### Modified Files:
1. `app/Models/User.php` - Added new fields
2. `app/Models/Complaint.php` - Added replies relationship
3. `app/Http/Controllers/Api/AuthController.php` - Added setPassword method
4. `app/Http/Controllers/Api/EstateUserController.php` - Modified user creation
5. `app/Http/Controllers/Api/VisitorCodeController.php` - Updated verification logic
6. `app/Http/Controllers/Api/ComplaintController.php` - Added reply method
7. `app/Console/Kernel.php` - Added scheduled command
8. `app/Http/Kernel.php` - Registered maintainer middleware
9. `routes/api.php` - Added new routes

### Database Migrations:
Run `php artisan migrate` to apply all changes:
```bash
php artisan migrate
```

### Scheduled Tasks:
Make sure your cron is configured to run Laravel's scheduler:
```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

This will run the visitor code expiration command every hour.

---

## Testing Recommendations

1. **Password Setup Email**: Create a user via admin and verify email is sent
2. **Visitor Code Expiration**: Create expired codes and run `php artisan visitor-codes:expire`
3. **Verified Code Re-verification**: Verify a code twice and check for success response
4. **Maintainer Role**: Create maintainer user and test permissions
5. **Complaint Replies**: Submit complaint as user, reply as admin, view as user
6. **Announcements**: Create announcements with different priorities and expiration dates
7. **Forgot Password**: Test password reset for different user types

---

## API Documentation Updates

All new endpoints follow the existing API pattern:
- Success responses include `success: true`
- Error responses include `success: false` and error messages
- Most endpoints require authentication via Sanctum
- Admin-only endpoints check for admin usertype
- Maintainer endpoints check for admin OR maintainer usertype

For complete API documentation, see `API_DOCUMENTATION.md`.

