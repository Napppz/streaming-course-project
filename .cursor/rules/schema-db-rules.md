# Dokumentasi Skema Database

Dokumen ini menjelaskan skema database aplikasi, termasuk tabel, kolom, tipe data, dan relasi antar tabel.

---

### Tabel: `users`

Tabel ini menyimpan data pengguna aplikasi.

| Kolom             | Tipe Data             | Keterangan                          |
| ----------------- | --------------------- | ----------------------------------- |
| `id`              | INT(11) UNSIGNED      | **Primary Key**, auto-increment.    |
| `username`        | VARCHAR(50)           | Nama pengguna untuk login, unik.    |
| `email`           | VARCHAR(100)          | Alamat email pengguna, unik.        |
| `password`        | VARCHAR(255)          | Kata sandi yang sudah di-hash.      |
| `full_name`       | VARCHAR(100)          | Nama lengkap pengguna.              |
| `role`            | ENUM('admin', 'user') | Peran pengguna dalam sistem.        |
| `profile_picture` | VARCHAR(255)          | URL gambar profil.                  |
| `bio`             | TEXT                  | Deskripsi singkat tentang pengguna. |
| `last_login`      | DATETIME              | Waktu terakhir pengguna login.      |
| `created_at`      | DATETIME              | Waktu pembuatan data.               |
| `updated_at`      | DATETIME              | Waktu terakhir data diperbarui.     |

**Relasi:**

- Tabel ini menjadi referensi utama untuk data pengguna di seluruh aplikasi.

---

### Tabel: `categories`

Tabel ini menyimpan kategori-kategori untuk kursus.

| Kolom         | Tipe Data        | Keterangan                             |
| ------------- | ---------------- | -------------------------------------- |
| `id`          | INT(11) UNSIGNED | **Primary Key**, auto-increment.       |
| `name`        | VARCHAR(100)     | Nama kategori.                         |
| `slug`        | VARCHAR(100)     | URL-friendly dari nama kategori, unik. |
| `description` | TEXT             | Deskripsi singkat kategori.            |

---

### Tabel: `courses`

Tabel ini menyimpan data utama mengenai kursus yang tersedia.

| Kolom               | Tipe Data                                    | Keterangan                                |
| ------------------- | -------------------------------------------- | ----------------------------------------- |
| `id`                | INT(11) UNSIGNED                             | **Primary Key**, auto-increment.          |
| `title`             | VARCHAR(255)                                 | Judul kursus.                             |
| `slug`              | VARCHAR(255)                                 | URL-friendly dari judul kursus, unik.     |
| `description`       | TEXT                                         | Deskripsi lengkap kursus.                 |
| `short_description` | VARCHAR(255)                                 | Deskripsi singkat kursus.                 |
| `thumbnail`         | VARCHAR(255)                                 | URL gambar thumbnail kursus.              |
| `status`            | ENUM('draft', 'published', 'private')        | Status publikasi kursus.                  |
| `created_by`        | INT(11) UNSIGNED                             | **Foreign Key** ke `users(id)`.           |
| `published_at`      | DATETIME                                     | Tanggal kursus dipublikasikan.            |
| `duration`          | INT(11)                                      | Total durasi kursus (misal: dalam menit). |
| `level`             | ENUM('beginner', 'intermediate', 'advanced') | Tingkat kesulitan kursus.                 |
| `is_featured`       | TINYINT(1)                                   | Menandakan apakah kursus ini unggulan.    |
| `created_at`        | DATETIME                                     | Waktu pembuatan data.                     |
| `updated_at`        | DATETIME                                     | Waktu terakhir data diperbarui.           |

**Relasi:**

- `created_by` berelasi ke `users(id)` (One-to-Many: User bisa membuat banyak Course).

---

### Tabel: `course_categories`

Tabel pivot (junction table) yang menghubungkan antara kursus dan kategori.

| Kolom         | Tipe Data        | Keterangan                                                      |
| ------------- | ---------------- | --------------------------------------------------------------- |
| `course_id`   | INT(11) UNSIGNED | **Composite Primary Key**, **Foreign Key** ke `courses(id)`.    |
| `category_id` | INT(11) UNSIGNED | **Composite Primary Key**, **Foreign Key** ke `categories(id)`. |

**Relasi:**

- Many-to-Many antara `courses` dan `categories`.

---

### Tabel: `modules`

Tabel ini menyimpan data modul-modul yang merupakan bagian dari sebuah kursus.

| Kolom         | Tipe Data        | Keterangan                        |
| ------------- | ---------------- | --------------------------------- |
| `id`          | INT(11) UNSIGNED | **Primary Key**, auto-increment.  |
| `course_id`   | INT(11) UNSIGNED | **Foreign Key** ke `courses(id)`. |
| `title`       | VARCHAR(255)     | Judul modul.                      |
| `description` | TEXT             | Deskripsi singkat modul.          |
| `order_index` | INT(11)          | Urutan modul dalam satu kursus.   |
| `created_at`  | DATETIME         | Waktu pembuatan data.             |
| `updated_at`  | DATETIME         | Waktu terakhir data diperbarui.   |

**Relasi:**

- `course_id` berelasi ke `courses(id)` (One-to-Many: Course memiliki banyak Module).

---

### Tabel: `lessons`

Tabel ini menyimpan data materi pembelajaran (lesson) dalam setiap modul.

| Kolom            | Tipe Data        | Keterangan                         |
| ---------------- | ---------------- | ---------------------------------- |
| `id`             | INT(11) UNSIGNED | **Primary Key**, auto-increment.   |
| `module_id`      | INT(11) UNSIGNED | **Foreign Key** ke `modules(id)`.  |
| `title`          | VARCHAR(255)     | Judul materi pembelajaran.         |
| `description`    | TEXT             | Deskripsi materi pembelajaran.     |
| `content`        | TEXT             | Konten teks dari materi.           |
| `video_url`      | VARCHAR(255)     | URL video pembelajaran.            |
| `video_duration` | INT(11)          | Durasi video (misal: dalam detik). |
| `order_index`    | INT(11) UNSIGNED | Urutan materi dalam satu modul.    |
| `created_at`     | DATETIME         | Waktu pembuatan data.              |
| `updated_at`     | DATETIME         | Waktu terakhir data diperbarui.    |

**Relasi:**

- `module_id` berelasi ke `modules(id)` (One-to-Many: Module memiliki banyak Lesson).

---

### Tabel: `enrollments`

Tabel ini mencatat pengguna mana saja yang mendaftar di kursus tertentu.

| Kolom                 | Tipe Data        | Keterangan                           |
| --------------------- | ---------------- | ------------------------------------ |
| `id`                  | INT(11) UNSIGNED | **Primary Key**, auto-increment.     |
| `user_id`             | INT(11) UNSIGNED | **Foreign Key** ke `users(id)`.      |
| `course_id`           | INT(11) UNSIGNED | **Foreign Key** ke `courses(id)`.    |
| `enrolled_at`         | DATETIME         | Waktu pengguna mendaftar kursus.     |
| `completed_at`        | DATETIME         | Waktu pengguna menyelesaikan kursus. |
| `progress_percentage` | DECIMAL(5,2)     | Persentase kemajuan kursus.          |
| `is_active`           | TINYINT(1)       | Status keaktifan pendaftaran.        |

**Relasi:**

- `user_id` dan `course_id` membentuk **unique key** untuk memastikan pengguna hanya bisa mendaftar sekali di satu kursus.
- Many-to-Many antara `users` dan `courses` melalui tabel ini.

---

### Tabel: `lesson_progress`

Tabel ini melacak kemajuan setiap pengguna pada setiap materi pembelajaran.

| Kolom                 | Tipe Data                                       | Keterangan                             |
| --------------------- | ----------------------------------------------- | -------------------------------------- |
| `id`                  | INT(11) UNSIGNED                                | **Primary Key**, auto-increment.       |
| `user_id`             | INT(11) UNSIGNED                                | **Foreign Key** ke `users(id)`.        |
| `lesson_id`           | INT(11) UNSIGNED                                | **Foreign Key** ke `lessons(id)`.      |
| `status`              | ENUM('not_started', 'in_progress', 'completed') | Status kemajuan materi.                |
| `progress_percentage` | DECIMAL(5,2)                                    | Persentase kemajuan materi (jika ada). |
| `started_at`          | DATETIME                                        | Waktu materi mulai dipelajari.         |
| `completed_at`        | DATETIME                                        | Waktu materi selesai dipelajari.       |

**Relasi:**

- `user_id` dan `lesson_id` membentuk **unique key**.
- Many-to-Many antara `users` dan `lessons` melalui tabel ini, yang menunjukkan progress.

---

### Tabel: `course_reviews`

Tabel ini menyimpan ulasan dan rating yang diberikan oleh pengguna untuk sebuah kursus.

| Kolom        | Tipe Data        | Keterangan                          |
| ------------ | ---------------- | ----------------------------------- |
| `id`         | INT(11) UNSIGNED | **Primary Key**, auto-increment.    |
| `course_id`  | INT(11) UNSIGNED | **Foreign Key** ke `courses(id)`.   |
| `user_id`    | INT(11) UNSIGNED | **Foreign Key** ke `users(id)`.     |
| `rating`     | INT(1) UNSIGNED  | Rating yang diberikan (misal: 1-5). |
| `review`     | TEXT             | Teks ulasan dari pengguna.          |
| `created_at` | DATETIME         | Waktu ulasan dibuat.                |
| `updated_at` | DATETIME         | Waktu ulasan diperbarui.            |

**Relasi:**

- `course_id` dan `user_id` memiliki _key_ untuk melacak ulasan per pengguna per kursus.

---

### Tabel: `settings`

Tabel ini menyimpan konfigurasi umum aplikasi.

| Kolom           | Tipe Data        | Keterangan                                 |
| --------------- | ---------------- | ------------------------------------------ |
| `id`            | INT(11) UNSIGNED | **Primary Key**, auto-increment.           |
| `setting_key`   | VARCHAR(100)     | Kunci unik untuk setiap pengaturan.        |
| `setting_value` | TEXT             | Nilai dari pengaturan.                     |
| `setting_group` | VARCHAR(100)     | Grup pengaturan (misal: 'general').        |
| `is_public`     | TINYINT(1)       | Apakah pengaturan ini bisa diakses publik. |
| `updated_at`    | DATETIME         | Waktu terakhir pengaturan diperbarui.      |
