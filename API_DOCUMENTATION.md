# Human-HR API Documentation

## Base URL
```
http://127.0.0.1:8000/api
```

---

## Authentication

### Token-Based Authentication
Endpoints yang memerlukan autentikasi menggunakan Bearer Token. Token didapatkan dari endpoint `/login`.

Format header:
```
Authorization: Bearer {token}
```

---

## Endpoints

### 1. Login (Tanpa Token)
**POST** `/login`

Endpoint untuk login dan mendapatkan Bearer token.

#### Request
```json
{
  "email": "admin@local.test",
  "password": "password123"
}
```

#### Response (200 OK)
```json
{
  "message": "Login berhasil.",
  "token_type": "Bearer",
  "token": "random80charactertoken...",
  "user": {
    "id": "uuid-user",
    "name": "Super Admin",
    "email": "admin@local.test",
    "role": "superadmin",
    "email_verified_at": "2026-04-13T10:00:00.000000Z",
    "created_at": "2026-04-13T10:00:00.000000Z",
    "updated_at": "2026-04-13T10:00:00.000000Z"
  }
}
```

#### Response Error (422 Unprocessable Entity)
```json
{
  "message": "Email atau password salah."
}
```

#### cURL
```bash
curl -X POST "http://127.0.0.1:8000/api/login" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@local.test","password":"password123"}'
```

#### PowerShell
```powershell
curl.exe -X POST "http://127.0.0.1:8000/api/login" `
  -H "Accept: application/json" `
  -H "Content-Type: application/json" `
  -d '{"email":"admin@local.test","password":"password123"}'
```

---

### 2. Get Current User (Dengan Token)
**GET** `/me`

Mendapatkan data user yang currently authenticated berdasarkan token.

#### Headers
```
Authorization: Bearer {token}
```

#### Response (200 OK)
```json
{
  "user": {
    "id": "uuid-user",
    "name": "Super Admin",
    "email": "admin@local.test",
    "role": "superadmin",
    "email_verified_at": "2026-04-13T10:00:00.000000Z",
    "created_at": "2026-04-13T10:00:00.000000Z",
    "updated_at": "2026-04-13T10:00:00.000000Z"
  }
}
```

#### Response Error (401 Unauthorized)
```json
{
  "message": "Token tidak ditemukan."
}
```
atau
```json
{
  "message": "Token tidak valid."
}
```

#### cURL
```bash
curl "http://127.0.0.1:8000/api/me" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer TOKEN_DARI_LOGIN"
```

#### PowerShell
```powershell
curl.exe "http://127.0.0.1:8000/api/me" `
  -H "Accept: application/json" `
  -H "Authorization: Bearer TOKEN_DARI_LOGIN"
```

---

### 3. Get All Users (Dengan Token)
**GET** `/users`

Mendapatkan daftar semua user yang tersedia (memerlukan token).

#### Headers
```
Authorization: Bearer {token}
```

#### Response (200 OK)
```json
{
  "users": [
    {
      "id": "uuid-1",
      "name": "Super Admin",
      "email": "admin@local.test",
      "role": "superadmin",
      "email_verified_at": "2026-04-13T10:00:00.000000Z",
      "created_at": "2026-04-13T10:00:00.000000Z",
      "updated_at": "2026-04-13T10:00:00.000000Z"
    },
    {
      "id": "uuid-2",
      "name": "HR User",
      "email": "hr@demo.test",
      "role": "hr",
      "email_verified_at": "2026-04-13T10:00:00.000000Z",
      "created_at": "2026-04-13T10:00:00.000000Z",
      "updated_at": "2026-04-13T10:00:00.000000Z"
    }
  ]
}
```

#### Response Error (401 Unauthorized)
```json
{
  "message": "Token tidak ditemukan."
}
```

#### cURL
```bash
curl "http://127.0.0.1:8000/api/users" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer TOKEN_DARI_LOGIN"
```

#### PowerShell
```powershell
curl.exe "http://127.0.0.1:8000/api/users" `
  -H "Accept: application/json" `
  -H "Authorization: Bearer TOKEN_DARI_LOGIN"
```

---

### 4. Get User by ID (Dengan Token)
**GET** `/users/{user}`

Mendapatkan data user tertentu berdasarkan UUID (memerlukan token).

#### Parameters
- `user` (path, required): UUID user

#### Headers
```
Authorization: Bearer {token}
```

#### Response (200 OK)
```json
{
  "user": {
    "id": "uuid-user",
    "name": "Andi Pelamar",
    "email": "andi@demo.test",
    "role": "pelamar",
    "email_verified_at": "2026-04-13T10:00:00.000000Z",
    "created_at": "2026-04-13T10:00:00.000000Z",
    "updated_at": "2026-04-13T10:00:00.000000Z"
  }
}
```

#### Response Error (401 Unauthorized)
```json
{
  "message": "Token tidak ditemukan."
}
```

#### Response Error (404 Not Found)
```json
{
  "message": "Not Found"
}
```

#### cURL
```bash
curl "http://127.0.0.1:8000/api/users/UUID_USER" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer TOKEN_DARI_LOGIN"
```

#### PowerShell
```powershell
curl.exe "http://127.0.0.1:8000/api/users/UUID_USER" `
  -H "Accept: application/json" `
  -H "Authorization: Bearer TOKEN_DARI_LOGIN"
```

---

### 5. Get All Users (Tanpa Token - Public)
**GET** `/public/users`

Mendapatkan daftar semua user secara publik (tanpa memerlukan token).

#### Response (200 OK)
```json
{
  "users": [
    {
      "id": "uuid-1",
      "name": "Super Admin",
      "email": "admin@local.test",
      "role": "superadmin",
      "email_verified_at": "2026-04-13T10:00:00.000000Z",
      "created_at": "2026-04-13T10:00:00.000000Z",
      "updated_at": "2026-04-13T10:00:00.000000Z"
    },
    {
      "id": "uuid-2",
      "name": "HR User",
      "email": "hr@demo.test",
      "role": "hr",
      "email_verified_at": "2026-04-13T10:00:00.000000Z",
      "created_at": "2026-04-13T10:00:00.000000Z",
      "updated_at": "2026-04-13T10:00:00.000000Z"
    }
  ]
}
```

#### cURL
```bash
curl "http://127.0.0.1:8000/api/public/users" \
  -H "Accept: application/json"
```

#### PowerShell
```powershell
curl.exe "http://127.0.0.1:8000/api/public/users" `
  -H "Accept: application/json"
```

---

### 6. Get User by ID (Tanpa Token - Public)
**GET** `/public/users/{user}`

Mendapatkan data user tertentu berdasarkan UUID secara publik (tanpa memerlukan token).

#### Parameters
- `user` (path, required): UUID user

#### Response (200 OK)
```json
{
  "user": {
    "id": "uuid-user",
    "name": "Andi Pelamar",
    "email": "andi@demo.test",
    "role": "pelamar",
    "email_verified_at": "2026-04-13T10:00:00.000000Z",
    "created_at": "2026-04-13T10:00:00.000000Z",
    "updated_at": "2026-04-13T10:00:00.000000Z"
  }
}
```

#### Response Error (404 Not Found)
```json
{
  "message": "Not Found"
}
```

#### cURL
```bash
curl "http://127.0.0.1:8000/api/public/users/UUID_USER" \
  -H "Accept: application/json"
```

#### PowerShell
```powershell
curl.exe "http://127.0.0.1:8000/api/public/users/UUID_USER" `
  -H "Accept: application/json"
```

---

## Summary

| Endpoint | Method | Auth | Deskripsi |
|----------|--------|------|-----------|
| `/login` | POST | ❌ | Login dan dapatkan token |
| `/me` | GET | ✅ | Lihat profile user aktif |
| `/users` | GET | ✅ | Lihat semua user (autentikasi) |
| `/users/{user}` | GET | ✅ | Lihat user spesifik (autentikasi) |
| `/public/users` | GET | ❌ | Lihat semua user (publik) |
| `/public/users/{user}` | GET | ❌ | Lihat user spesifik (publik) |

---

## Status Codes

- **200 OK**: Request berhasil
- **201 Created**: Resource berhasil dibuat
- **400 Bad Request**: Request validation error
- **401 Unauthorized**: Token tidak valid atau tidak ada
- **404 Not Found**: Resource tidak ditemukan
- **422 Unprocessable Entity**: Validasi gagal (contoh: login dengan email/password salah)
- **500 Internal Server Error**: Server error

---

## Error Handling

Semua error response menggunakan format JSON dengan struktur:
```json
{
  "message": "Pesan error yang deskriptif"
}
```

---

## Testing

Untuk menjalankan feature tests API:
```bash
php vendor/bin/pest tests/Feature/Api/AuthApiTest.php
```

---

## Implementation Notes

- **Token Storage**: Token disimpan di kolom `api_token` di tabel `users`
- **Token Format**: Random 80-character string
- **Token Expiration**: Tidak ada expiration (infinite), bisa diimplementasikan sesuai kebutuhan
- **Security**: Token bersifat sensitif, jangan ekspos di URL atau log
- **Hidden Fields**: Password dan api_token tidak ditampilkan di response
