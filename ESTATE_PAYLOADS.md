# Estate Management API - Payload Documentation

## Registration Payloads

### 1. Admin Registration (Creating New Estate)

When an admin registers and wants to create a new estate:

**Endpoint:** `POST /api/register`

**Payload:**
```json
{
  "firstname": "John",
  "lastname": "Doe",
  "phone": "1234567890",
  "email": "admin@estate.com",
  "password": "password123",
  "password_confirmation": "password123",
  "usertype": "admin",
  "avatar": null,
  "address": "Optional address",
  "company_name": null,
  "estate": {
    "name": "Sunset Estate",
    "code": "SUNSET-001",
    "address": "123 Main Street, City, State",
    "phone": "0987654321",
    "email": "info@sunsetestate.com",
    "description": "A beautiful residential estate"
  }
}
```

**Note:** 
- `estate.code` is optional - if not provided, it will be auto-generated as `{slugified-name}-{random-6-chars}`
- `estate.address`, `estate.phone`, `estate.email`, `estate.description` are all optional

---

### 2. Admin Registration (Using Existing Estate)

When an admin registers and wants to join an existing estate:

**Endpoint:** `POST /api/register`

**Payload:**
```json
{
  "firstname": "Jane",
  "lastname": "Smith",
  "phone": "1234567891",
  "email": "admin2@estate.com",
  "password": "password123",
  "password_confirmation": "password123",
  "usertype": "admin",
  "avatar": null,
  "address": null,
  "company_name": null,
  "estate_id": 1
}
```

**Note:** Either `estate` object OR `estate_id` is required for admin registration, but not both.

---

### 3. Regular User Registration (Resident/Maintainer/User)

Regular users must provide an existing `estate_id`:

**Endpoint:** `POST /api/register`

**Payload:**
```json
{
  "firstname": "Bob",
  "lastname": "Johnson",
  "phone": "1234567892",
  "email": "resident@estate.com",
  "password": "password123",
  "password_confirmation": "password123",
  "usertype": "resident",
  "avatar": null,
  "address": null,
  "company_name": null,
  "estate_id": 1
}
```

**Valid usertypes:** `user`, `vendor`, `admin`, `installer`, `resident`, `maintainer`

**Note:** `estate_id` is **required** for all non-admin users.

---

## Estate Management Payloads

### 4. Create Estate (Standalone - Super Admin)

**Endpoint:** `POST /api/admin/estates`

**Headers:** `Authorization: Bearer {token}` (Admin with no estate_id)

**Payload:**
```json
{
  "name": "Riverside Estate",
  "code": "RIVER-001",
  "address": "456 River Road, City, State",
  "phone": "1112223333",
  "email": "info@riversideestate.com",
  "description": "Estate description here"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Estate created successfully",
  "data": {
    "id": 2,
    "name": "Riverside Estate",
    "code": "RIVER-001",
    "address": "456 River Road, City, State",
    "phone": "1112223333",
    "email": "info@riversideestate.com",
    "description": "Estate description here",
    "is_active": true,
    "created_at": "2025-11-06T10:00:00.000000Z",
    "updated_at": "2025-11-06T10:00:00.000000Z"
  }
}
```

---

### 5. Get Current User's Estate

**Endpoint:** `GET /api/estate`

**Headers:** `Authorization: Bearer {token}`

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Sunset Estate",
    "code": "SUNSET-001",
    "address": "123 Main Street, City, State",
    "phone": "0987654321",
    "email": "info@sunsetestate.com",
    "description": "A beautiful residential estate",
    "is_active": true,
    "created_at": "2025-11-06T09:00:00.000000Z",
    "updated_at": "2025-11-06T09:00:00.000000Z"
  }
}
```

---

### 6. Update Estate (Admin Only)

**Endpoint:** `PUT /api/estate`

**Headers:** `Authorization: Bearer {token}` (Admin only)

**Payload:**
```json
{
  "name": "Sunset Estate Updated",
  "code": "SUNSET-002",
  "address": "123 Main Street, Updated City, State",
  "phone": "0987654322",
  "email": "newemail@sunsetestate.com",
  "description": "Updated description",
  "is_active": true
}
```

**Note:** All fields are optional. Only include fields you want to update.

**Response:**
```json
{
  "success": true,
  "message": "Estate updated successfully",
  "data": {
    "id": 1,
    "name": "Sunset Estate Updated",
    "code": "SUNSET-002",
    "address": "123 Main Street, Updated City, State",
    "phone": "0987654322",
    "email": "newemail@sunsetestate.com",
    "description": "Updated description",
    "is_active": true,
    "created_at": "2025-11-06T09:00:00.000000Z",
    "updated_at": "2025-11-06T10:30:00.000000Z"
  }
}
```

---

### 7. List All Estates (Super Admin Only)

**Endpoint:** `GET /api/admin/estates`

**Headers:** `Authorization: Bearer {token}` (Admin with no estate_id)

**Response:**
```json
{
  "success": true,
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "name": "Sunset Estate",
        "code": "SUNSET-001",
        "address": "123 Main Street",
        "phone": "0987654321",
        "email": "info@sunsetestate.com",
        "description": "A beautiful residential estate",
        "is_active": true,
        "created_at": "2025-11-06T09:00:00.000000Z",
        "updated_at": "2025-11-06T09:00:00.000000Z"
      }
    ],
    "per_page": 15,
    "total": 1
  }
}
```

---

## Registration Response

All registration endpoints return:

```json
{
  "success": true,
  "message": "User registered successfully. Please check your email to verify your account.",
  "data": {
    "id": 1,
    "firstname": "John",
    "lastname": "Doe",
    "email": "admin@estate.com",
    "phone": "1234567890",
    "usertype": "admin",
    "estate_id": 1,
    "estate": {
      "id": 1,
      "name": "Sunset Estate",
      "code": "SUNSET-001",
      "address": "123 Main Street, City, State",
      "phone": "0987654321",
      "email": "info@sunsetestate.com",
      "description": "A beautiful residential estate",
      "is_active": true
    },
    "created_at": "2025-11-06T10:00:00.000000Z",
    "updated_at": "2025-11-06T10:00:00.000000Z"
  }
}
```

---

## Important Notes

1. **Admin Registration:**
   - Can create a new estate by providing `estate` object
   - OR join existing estate by providing `estate_id`
   - Cannot provide both `estate` and `estate_id`

2. **Regular User Registration:**
   - **Must** provide `estate_id` (existing estate)
   - Cannot create estates

3. **Estate Isolation:**
   - All data (visitor codes, complaints, activities, announcements) is automatically scoped to the user's estate
   - Users can only see/modify data from their own estate
   - Admin A from Estate A cannot access Estate B's data

4. **Estate Code:**
   - If not provided during creation, auto-generated as: `{slugified-name}-{6-random-chars}`
   - Must be unique across all estates

5. **Validation:**
   - `estate_id` must exist in the `estates` table
   - `estate.code` must be unique if provided
   - Email must be unique across all users

