# Dokumentasi API Tugas (curl)

Dokumen ini menjelaskan endpoint tugas yang sudah diimplementasikan pada project ini berdasarkan schema `app/Database/tugas.sql` dan referensi `app/Database/vigenesia80/`.

## Base URL

Sesuaikan host dan port dengan server lokal Anda.

```bash
http://localhost:8080
```

## Endpoint yang Tersedia

### Endpoint informasi API

- `GET /`

### Endpoint utama

- `GET /api/users`
- `POST /api/users`
- `DELETE /api/users`
- `GET /api/motivasi`
- `POST /api/motivasi`
- `PUT /api/motivasi`
- `DELETE /api/motivasi`

### Endpoint kompatibilitas tugas

- `GET /user_get`
- `POST /user_post`
- `DELETE /user_delete`
- `GET /motivasi_get`
- `POST /motivasi_post`
- `PUT /motivasi_update`
- `DELETE /motivasi_delete`

> Endpoint `/api/...` dan endpoint kompatibilitas di atas memakai logic yang sama. Pilih salah satu gaya pemanggilan saja agar konsisten.

---

## 0) Cek status API dan daftar endpoint

```bash
curl -X GET "http://localhost:8080/"
```

### Contoh response

```json
{
  "status": "success",
  "message": "API tugas vigenesia aktif",
  "endpoints": [
    "GET /api/users",
    "POST /api/users",
    "DELETE /api/users",
    "GET /api/motivasi",
    "POST /api/motivasi",
    "PUT /api/motivasi",
    "DELETE /api/motivasi",
    "GET /user_get",
    "POST /user_post",
    "DELETE /user_delete",
    "GET /motivasi_get",
    "POST /motivasi_post",
    "PUT /motivasi_update",
    "DELETE /motivasi_delete"
  ]
}
```

---

## 1) Ambil semua user

```bash
curl -X GET "http://localhost:8080/api/users"
```

### Query parameter yang didukung

- `nama` → filter LIKE nama user
- `profesi` → filter LIKE profesi user
- `email` → filter LIKE email
- `role_id` → filter exact match role
- `is_active` → filter exact match status aktif
- `limit` → jumlah data per request
- `offset` → offset pagination

### Contoh query user dengan filter

```bash
curl -X GET "http://localhost:8080/api/users?nama=affan&limit=10&offset=0"
```

```bash
curl -X GET "http://localhost:8080/api/users?profesi=mahasiswa&is_active=1"
```

```bash
curl -X GET "http://localhost:8080/user_get?email=bsi.ac.id&role_id=2"
```

### Contoh response sukses

```json
{
  "status": "success",
  "message": "Data user berhasil diambil",
  "data": {
    "users": [
      {
        "iduser": 1,
        "nama": "sriyadi",
        "profesi": "dosen",
        "email": "sriyadi.sry@bsi.ac.id",
        "role_id": 2,
        "is_active": 1,
        "tanggal_input": "2021-07-16",
        "modified": "2021-07-16"
      }
    ],
    "pagination": {
      "total_records": 1,
      "current_page": 1,
      "total_pages": 1,
      "limit": 10,
      "offset": 0
    },
    "filters_applied": {
      "nama": "affan",
      "profesi": null,
      "email": null,
      "role_id": null,
      "is_active": null
    }
  }
}
```

---

## 2) Tambah user baru

### Request JSON

```bash
curl -X POST "http://localhost:8080/api/users" \
  -H "Content-Type: application/json" \
  -d '{
    "nama": "Budi Santoso",
    "profesi": "mahasiswa",
    "email": "budi@example.com",
    "password": "rahasia123",
    "role_id": 2,
    "is_active": 1
  }'
```

### Versi endpoint tugas

```bash
curl -X POST "http://localhost:8080/user_post" \
  -H "Content-Type: application/json" \
  -d '{
    "nama": "Budi Santoso",
    "profesi": "mahasiswa",
    "email": "budi@example.com",
    "password": "rahasia123",
    "role_id": 2,
    "is_active": 1
  }'
```

### Field wajib

- `nama`
- `profesi`
- `email`
- `password`
- `role_id`

### Field opsional

- `is_active` → default `1`

### Contoh response sukses

```json
{
  "status": "success",
  "message": "User baru berhasil ditambahkan",
  "data": {
    "iduser": 4,
    "nama": "Budi Santoso",
    "profesi": "mahasiswa",
    "email": "budi@example.com",
    "role_id": 2,
    "is_active": 1,
    "tanggal_input": "2026-05-12",
    "modified": "2026-05-12"
  }
}
```

### Contoh error email sudah dipakai

```json
{
  "status": "error",
  "message": "Email sudah terdaftar, gunakan email yang berbeda"
}
```

### Contoh error data tidak lengkap

```json
{
  "status": "error",
  "message": "Data tidak lengkap. Semua field wajib diisi: nama, profesi, email, password, role_id"
}
```

---

## 3) Hapus user

Request ini memakai body berisi `iduser`.

```bash
curl -X DELETE "http://localhost:8080/api/users" \
  -H "Content-Type: application/json" \
  -d '{
    "iduser": 4
  }'
```

### Versi endpoint tugas

```bash
curl -X DELETE "http://localhost:8080/user_delete" \
  -H "Content-Type: application/json" \
  -d '{
    "iduser": 4
  }'
```

### Contoh response sukses

```json
{
  "status": "success",
  "message": "User berhasil dihapus",
  "data": {
    "iduser": 4,
    "nama": "Budi Santoso",
    "profesi": "mahasiswa",
    "email": "budi@example.com",
    "role_id": 2,
    "is_active": 1,
    "total_motivasi_dihapus": 0,
    "deleted_at": "2026-05-12 23:10:00"
  }
}
```

---

## 4) Ambil semua motivasi

```bash
curl -X GET "http://localhost:8080/api/motivasi"
```

### Query parameter yang didukung

- `iduser` → filter berdasarkan pemilik motivasi
- `search` → cari teks pada `isi_motivasi`
- `limit` → jumlah data per request
- `offset` → offset pagination

### Contoh query motivasi

```bash
curl -X GET "http://localhost:8080/api/motivasi?iduser=1"
```

```bash
curl -X GET "http://localhost:8080/api/motivasi?search=bangun&limit=5&offset=0"
```

```bash
curl -X GET "http://localhost:8080/motivasi_get?iduser=2&search=dc"
```

### Contoh response sukses

```json
{
  "status": "success",
  "message": "Data motivasi berhasil diambil",
  "data": {
    "motivasi": [
      {
        "id": 12,
        "isi_motivasi": "bangun",
        "iduser": 1,
        "nama_user": "sriyadi",
        "profesi_user": "dosen",
        "tanggal_input": "2021-07-10",
        "tanggal_update": "2021-07-11"
      }
    ],
    "pagination": {
      "total_records": 1,
      "current_page": 1,
      "total_pages": 1,
      "limit": 5,
      "offset": 0
    },
    "filters_applied": {
      "iduser": "1",
      "search": "bangun"
    }
  }
}
```

---

## 5) Tambah motivasi

```bash
curl -X POST "http://localhost:8080/api/motivasi" \
  -H "Content-Type: application/json" \
  -d '{
    "isi_motivasi": "Tetap semangat belajar setiap hari",
    "iduser": 1
  }'
```

### Versi endpoint tugas

```bash
curl -X POST "http://localhost:8080/motivasi_post" \
  -H "Content-Type: application/json" \
  -d '{
    "isi_motivasi": "Tetap semangat belajar setiap hari",
    "iduser": 1
  }'
```

### Field wajib

- `isi_motivasi`
- `iduser`

### Contoh response sukses

```json
{
  "status": "success",
  "message": "Motivasi baru berhasil ditambahkan",
  "data": {
    "id": 24,
    "isi_motivasi": "Tetap semangat belajar setiap hari",
    "iduser": 1,
    "nama_user": "sriyadi",
    "tanggal_input": "2026-05-12",
    "tanggal_update": "2026-05-12"
  }
}
```

### Contoh error user tidak ditemukan

```json
{
  "status": "error",
  "message": "User dengan ID 999 tidak ditemukan"
}
```

---

## 6) Update motivasi

Hanya `isi_motivasi` yang diubah. Request membutuhkan `id` dan isi baru.

```bash
curl -X PUT "http://localhost:8080/api/motivasi" \
  -H "Content-Type: application/json" \
  -d '{
    "id": 24,
    "isi_motivasi": "Jangan menyerah, belajar pelan-pelan tapi konsisten"
  }'
```

### Versi endpoint tugas

```bash
curl -X PUT "http://localhost:8080/motivasi_update" \
  -H "Content-Type: application/json" \
  -d '{
    "id": 24,
    "isi_motivasi": "Jangan menyerah, belajar pelan-pelan tapi konsisten"
  }'
```

### Contoh response sukses

```json
{
  "status": "success",
  "message": "Motivasi berhasil diupdate",
  "data": {
    "before_update": {
      "id": 24,
      "isi_motivasi": "Tetap semangat belajar setiap hari",
      "iduser": 1,
      "nama_user": "sriyadi",
      "tanggal_input": "2026-05-12"
    },
    "after_update": {
      "id": 24,
      "isi_motivasi": "Jangan menyerah, belajar pelan-pelan tapi konsisten",
      "iduser": 1,
      "nama_user": "sriyadi",
      "tanggal_input": "2026-05-12",
      "tanggal_update": "2026-05-12"
    },
    "updated_at": "2026-05-12 23:15:00"
  }
}
```

### Contoh error tidak ada perubahan

```json
{
  "status": "error",
  "message": "Tidak ada perubahan data"
}
```

---

## 7) Hapus motivasi

```bash
curl -X DELETE "http://localhost:8080/api/motivasi" \
  -H "Content-Type: application/json" \
  -d '{
    "id": 24
  }'
```

### Versi endpoint tugas

```bash
curl -X DELETE "http://localhost:8080/motivasi_delete" \
  -H "Content-Type: application/json" \
  -d '{
    "id": 24
  }'
```

### Contoh response sukses

```json
{
  "status": "success",
  "message": "Motivasi berhasil dihapus",
  "data": {
    "id": 24,
    "isi_motivasi": "Jangan menyerah, belajar pelan-pelan tapi konsisten",
    "iduser": 1,
    "nama_user": "sriyadi",
    "deleted_at": "2026-05-12 23:20:00"
  }
}
```

---

## Contoh Alur Pengujian Cepat

### A. Ambil user seed

```bash
curl -X GET "http://localhost:8080/api/users"
```

### B. Tambah user baru

```bash
curl -X POST "http://localhost:8080/api/users" \
  -H "Content-Type: application/json" \
  -d '{
    "nama": "Testing User",
    "profesi": "tester",
    "email": "testing.user@example.com",
    "password": "test12345",
    "role_id": 2
  }'
```

### C. Tambah motivasi untuk user id 1

```bash
curl -X POST "http://localhost:8080/api/motivasi" \
  -H "Content-Type: application/json" \
  -d '{
    "isi_motivasi": "Belajar API dengan curl",
    "iduser": 1
  }'
```

### D. Cari motivasi berdasarkan keyword

```bash
curl -X GET "http://localhost:8080/api/motivasi?search=Belajar"
```

---

## Catatan Penting

1. Method `DELETE` dan `PUT` menggunakan body JSON.
2. Data user yang dikembalikan tidak menyertakan field password.
3. Saat membuat user, password akan disimpan dalam bentuk hash oleh aplikasi.
4. Jika Anda memakai endpoint kompatibilitas tugas, response tetap sama dengan endpoint `/api/...`.
5. Bila server Anda berjalan di port lain, ganti semua `localhost:8080` sesuai kondisi lokal.
6. Selain JSON, body request juga bisa dikirim sebagai `application/x-www-form-urlencoded` bila diperlukan.
7. Nilai `limit` dibatasi maksimal `1000`, dan bila dikirim kurang dari `1` maka sistem akan memakai default `10`.
