# Dokumentasi Routes

Dokumen ini menjelaskan semua definisi rute (URL endpoint) yang tersedia dalam aplikasi, termasuk metode HTTP yang didukung, controller yang menangani, dan filter (middleware) yang diterapkan.

---

### Rute Publik & Otentikasi

Rute-rute ini dapat diakses oleh semua pengunjung.

- `GET /`: Halaman utama aplikasi. Ditangani oleh `Home::index`.

  - **Alur:** Permintaan ke rute ini akan dieksekusi oleh fungsi `index` di dalam file controller `app/Controllers/Home.php`.

- `GET /login`: Menampilkan halaman login. Ditangani oleh `AuthController::login`.

  - **Alur:** Rute ini akan dieksekusi oleh fungsi `login` yang ada di dalam file controller `app/Controllers/AuthController.php`.

- `POST /login`: Memproses upaya login pengguna. Ditangani oleh `AuthController::attemptLogin`.

  - **Alur:** Rute ini akan dieksekusi oleh fungsi `attemptLogin` yang ada di dalam file controller `app/Controllers/AuthController.php`.

- `GET /signup`: Menampilkan halaman registrasi. Ditangani oleh `AuthController::signup`.

  - **Alur:** Rute ini akan dieksekusi oleh fungsi `signup` yang ada di dalam file controller `app/Controllers/AuthController.php`.

- `POST /signup`: Memproses upaya registrasi pengguna baru. Ditangani oleh `AuthController::attemptSignup`.

  - **Alur:** Rute ini akan dieksekusi oleh fungsi `attemptSignup` yang ada di dalam file controller `app/Controllers/AuthController.php`.

- `GET /logout`: Mengeluarkan pengguna dari sesi (logout). Ditangani oleh `AuthController::logout`.

  - **Alur:** Rute ini akan dieksekusi oleh fungsi `logout` yang ada di dalam file controller `app/Controllers/AuthController.php`.

- `GET /course`: Menampilkan daftar semua kursus yang tersedia untuk publik. Ditangani oleh `CourseController2::index`.
  - **Alur:** Rute ini akan dieksekusi oleh fungsi `index` yang ada di dalam file controller `app/Controllers/CourseController2.php`.

---

### Grup Rute: `/course` (Memerlukan Login)

Grup rute ini memerlukan pengguna untuk login (`'filter' => 'user'`). Rute ini terkait dengan interaksi pengguna dengan konten kursus.

- `GET course/lesson/(:num)/(:num)`: Menampilkan halaman materi pembelajaran. Ditangani oleh `CourseController2::lesson/$1/$2`.

  - **Alur:** Rute ini akan dieksekusi oleh fungsi `lesson` yang ada di dalam file controller `app/Controllers/CourseController2.php`.

- `POST course/mark-complete`: Menandai sebuah materi sebagai selesai. Ditangani oleh `CourseController2::markComplete`.

  - **Alur:** Rute ini akan dieksekusi oleh fungsi `markComplete` yang ada di dalam file controller `app/Controllers/CourseController2.php`.

- `POST course/update-progress`: Memperbarui kemajuan belajar (misalnya, posisi video). Ditangani oleh `CourseController2::updateProgress`.

  - **Alur:** Rute ini akan dieksekusi oleh fungsi `updateProgress` yang ada di dalam file controller `app/Controllers/CourseController2.php`.

- `GET course/(:num)`: Mengarahkan pengguna ke halaman kursus. Ditangani oleh `CourseController2::redirectCourse/$1`.

  - **Alur:** Rute ini akan dieksekusi oleh fungsi `redirectCourse` yang ada di dalam file controller `app/Controllers/CourseController2.php`.

- `GET course/(:num)/lesson/(:num)`: Menampilkan halaman kursus dengan materi tertentu. Ditangani oleh `CourseController2::courseById/$1/$2`.

  - **Alur:** Rute ini akan dieksekusi oleh fungsi `courseById` yang ada di dalam file controller `app/Controllers/CourseController2.php`.

- `GET course/(:num)/enroll`: Mendaftarkan pengguna ke dalam kursus. Ditangani oleh `CourseController2::enroll/$1`.
  - **Alur:** Rute ini akan dieksekusi oleh fungsi `enroll` yang ada di dalam file controller `app/Controllers/CourseController2.php`.

---

### Grup Rute: `/api` (Memerlukan Login)

Grup rute ini berfungsi sebagai API internal untuk operasi di sisi klien (client-side) dan memerlukan login pengguna.

- `GET api/mark-complete/(:num)/(:num)`: Menandai seluruh kursus sebagai selesai. Ditangani oleh `CourseController2::markCourseCompleted/$1/$2`.

  - **Alur:** Rute ini akan dieksekusi oleh fungsi `markCourseCompleted` yang ada di dalam file controller `app/Controllers/CourseController2.php`.

- `GET api/lesson-navigation/(:num)/(:num)`: Mendapatkan data untuk navigasi antar materi (sebelumnya/berikutnya). Ditangani oleh `CourseController2::getLessonNavigation/$1/$2`.
  - **Alur:** Rute ini akan dieksekusi oleh fungsi `getLessonNavigation` yang ada di dalam file controller `app/Controllers/CourseController2.php`.

---

### Grup Rute: `/user` (Dasbor Pengguna)

Grup rute ini terkait dengan area dasbor pengguna (`'filter' => 'user'`).

- `GET user/dashboard`: Menampilkan dasbor utama pengguna. Ditangani oleh `Users\DashboardController::index`.

  - **Alur:** Rute ini akan dieksekusi oleh fungsi `index` yang ada di dalam file controller `app/Controllers/Users/DashboardController.php`.

- `GET user/courses`: Menampilkan daftar semua kursus. Ditangani oleh `Users\CourseController::index`.

  - **Alur:** Rute ini akan dieksekusi oleh fungsi `index` yang ada di dalam file controller `app/Controllers/Users/CourseController.php`.

- `GET user/courses/enrolled`: Menampilkan kursus yang sudah diikuti pengguna. Ditangani oleh `Users\CourseController::enrolled`.

  - **Alur:** Rute ini akan dieksekusi oleh fungsi `enrolled` yang ada di dalam file controller `app/Controllers/Users/CourseController.php`.

- `GET user/view-course/(:num)`: Menampilkan halaman detail kursus yang diikuti. Ditangani oleh `Users\CourseController::viewCourse/$1`.

  - **Alur:** Rute ini akan dieksekusi oleh fungsi `viewCourse` yang ada di dalam file controller `app/Controllers/Users/CourseController.php`.

- `GET user/certificate/(:num)`: Menghasilkan sertifikat untuk kursus yang telah selesai. Ditangani oleh `Users\CertificateController::generate/$1`.

  - **Alur:** Rute ini akan dieksekusi oleh fungsi `generate` yang ada di dalam file controller `app/Controllers/Users/CertificateController.php`.

- `GET user/profile`: Menampilkan profil pengguna. Ditangani oleh `UserProfileController::index`.

  - **Alur:** Rute ini akan dieksekusi oleh fungsi `index` yang ada di dalam file controller `app/Controllers/UserProfileController.php`.

- `POST user/profile/update`: Memperbarui profil pengguna. Ditangani oleh `UserProfileController::update`.

  - **Alur:** Rute ini akan dieksekusi oleh fungsi `update` yang ada di dalam file controller `app/Controllers/UserProfileController.php`.

- `GET user/settings`: Menampilkan halaman pengaturan akun. Ditangani oleh `UserSettingsController::index`.

  - **Alur:** Rute ini akan dieksekusi oleh fungsi `index` yang ada di dalam file controller `app/Controllers/UserSettingsController.php`.

- `POST user/settings`: Memperbarui pengaturan akun. Ditangani oleh `UserSettingsController::update`.
  - **Alur:** Rute ini akan dieksekusi oleh fungsi `update` yang ada di dalam file controller `app/Controllers/UserSettingsController.php`.

---

### Grup Rute: `/course` (Ulasan)

Grup rute ini menangani fungsionalitas ulasan (review) untuk kursus.

- `GET course/(:num)/reviews`: Menampilkan semua ulasan untuk kursus. Ditangani oleh `Users\ReviewController::index`.
  - **Alur:** Rute ini akan dieksekusi oleh fungsi `index` di file `app/Controllers/Users/ReviewController.php`.
- `POST course/(:num)/reviews`: Menyimpan ulasan baru untuk kursus. Ditangani oleh `Users\ReviewController::store`.
  - **Alur:** Rute ini akan dieksekusi oleh fungsi `store` di file `app/Controllers/Users/ReviewController.php`.
- `GET course/(:num)/reviews/create`: Menampilkan form pembuatan ulasan. Ditangani oleh `Users\ReviewController::create`.
  - **Alur:** Rute ini akan dieksekusi oleh fungsi `create` di file `app/Controllers/Users/ReviewController.php`.
- `GET course/(:num)/reviews/edit`: Menampilkan form edit ulasan. Ditangani oleh `Users\ReviewController::edit`.
  - **Alur:** Rute ini akan dieksekusi oleh fungsi `edit` di file `app/Controllers/Users/ReviewController.php`.
- `POST course/(:num)/reviews/update`: Memperbarui ulasan yang sudah ada. Ditangani oleh `Users\ReviewController::update`.
  - **Alur:** Rute ini akan dieksekusi oleh fungsi `update` di file `app/Controllers/Users/ReviewController.php`.
- `GET course/(:num)/reviews/delete`: Menghapus ulasan. Ditangani oleh `Users\ReviewController::delete`.
  - **Alur:** Rute ini akan dieksekusi oleh fungsi `delete` di file `app/Controllers/Users/ReviewController.php`.

---

### Grup Rute: `/admin` (Area Admin)

Grup rute ini hanya bisa diakses oleh pengguna dengan peran 'admin' (`'filter' => 'admin'`).

- **Dasbor:**

  - `GET admin/dashboard`: Menampilkan dasbor admin. Ditangani oleh `Admin\DashboardController::index`.
    - **Alur:** Dijalankan oleh fungsi `index` di `app/Controllers/Admin/DashboardController.php`.

- **Manajemen Ulasan:**

  - `GET admin/course/(:num)/reviews`: Melihat ulasan kursus. Ditangani oleh `Admin\CourseReviewController::index/$1`.
    - **Alur:** Dijalankan oleh fungsi `index` di `app/Controllers/Admin/CourseReviewController.php`.
  - `GET admin/course/(:num)/reviews/(:num)/delete`: Menghapus ulasan. Ditangani oleh `Admin\CourseReviewController::delete/$1/$2`.
    - **Alur:** Dijalankan oleh fungsi `delete` di `app/Controllers/Admin/CourseReviewController.php`.

- **Manajemen Kursus (CRUD):**

  - `GET admin/course`: Ditangani oleh `Admin\CourseController::index`.
    - **Alur:** Dijalankan oleh fungsi `index` di `app/Controllers/Admin/CourseController.php`.
  - `POST admin/course`: Ditangani oleh `Admin\CourseController::store`.
    - **Alur:** Dijalankan oleh fungsi `store` di `app/Controllers/Admin/CourseController.php`.
  - `GET admin/course/create`: Ditangani oleh `Admin\CourseController::create`.
    - **Alur:** Dijalankan oleh fungsi `create` di `app/Controllers/Admin/CourseController.php`.
  - `GET admin/course/(:num)/edit`: Ditangani oleh `Admin\CourseController::edit/$1`.
    - **Alur:** Dijalankan oleh fungsi `edit` di `app/Controllers/Admin/CourseController.php`.
  - `POST admin/course/(:num)`: Ditangani oleh `Admin\CourseController::update/$1`.
    - **Alur:** Dijalankan oleh fungsi `update` di `app/Controllers/Admin/CourseController.php`.
  - `GET admin/course/(:num)/delete`: Ditangani oleh `Admin\CourseController::delete/$1`.
    - **Alur:** Dijalankan oleh fungsi `delete` di `app/Controllers/Admin/CourseController.php`.

- **Manajemen Modul (CRUD):**

  - `GET admin/course/(:num)/modules`: Ditangani oleh `Admin\ModuleController::index/$1`.
    - **Alur:** Dijalankan oleh fungsi `index` di `app/Controllers/Admin/ModuleController.php`.
  - `POST admin/course/(:num)/modules`: Ditangani oleh `Admin\ModuleController::store/$1`.
    - **Alur:** Dijalankan oleh fungsi `store` di `app/Controllers/Admin/ModuleController.php`.
  - `GET admin/course/(:num)/modules/create`: Ditangani oleh `Admin\ModuleController::create/$1`. - **Alur:** Dijalankan oleh fungsi `create` di `app/Controllers/Admin/ModuleController.php`.
  - `GET admin/course/(:num)/modules/(:num)/edit`: Ditangani oleh `Admin\ModuleController::edit/$1/$2`.
    - **Alur:** Dijalankan oleh fungsi `edit` di `app/Controllers/Admin/ModuleController.php`.
  - `POST admin/course/(:num)/modules/(:num)`: Ditangani oleh `Admin\ModuleController::update/$1/$2`.
    - **Alur:** Dijalankan oleh fungsi `update` di `app/Controllers/Admin/ModuleController.php`.
  - `GET admin/course/(:num)/modules/(:num)/delete`: Ditangani oleh `Admin\ModuleController::delete/$1/$2`.
    - **Alur:** Dijalankan oleh fungsi `delete` di `app/Controllers/Admin/ModuleController.php`.

- **Manajemen Materi (CRUD):**

  - `GET admin/course/(:num)/modules/(:num)/lessons`: Ditangani oleh `Admin\LessonController::index/$1/$2`.
    - **Alur:** Dijalankan oleh fungsi `index` di `app/Controllers/Admin/LessonController.php`.
  - `POST admin/course/(:num)/modules/(:num)/lessons`: Ditangani oleh `Admin\LessonController::store/$1/$2`.
    - **Alur:** Dijalankan oleh fungsi `store` di `app/Controllers/Admin/LessonController.php`.
  - `GET admin/course/(:num)/modules/(:num)/lessons/create`: Ditangani oleh `Admin\LessonController::create/$1/$2`.
    - **Alur:** Dijalankan oleh fungsi `create` di `app/Controllers/Admin/LessonController.php`.
  - `GET admin/course/(:num)/modules/(:num)/lessons/(:num)/edit`: Ditangani oleh `Admin\LessonController::edit/$1/$2/$3`.
    - **Alur:** Dijalankan oleh fungsi `edit` di `app/Controllers/Admin/LessonController.php`.
  - `POST admin/course/(:num)/modules/(:num)/lessons/(:num)`: Ditangani oleh `Admin\LessonController::update/$1/$2/$3`.
    - **Alur:** Dijalankan oleh fungsi `update` di `app/Controllers/Admin/LessonController.php`.
  - `GET admin/course/(:num)/modules/(:num)/lessons/(:num)/delete`: Ditangani oleh `Admin\LessonController::delete/$1/$2/$3`.
    - **Alur:** Dijalankan oleh fungsi `delete` di `app/Controllers/Admin/LessonController.php`.

- **Manajemen Kategori (CRUD):**

  - `GET admin/categories`: Ditangani oleh `Admin\CategoryController::index`.
    - **Alur:** Dijalankan oleh fungsi `index` di `app/Controllers/Admin/CategoryController.php`.
  - `POST admin/categories`: Ditangani oleh `Admin\CategoryController::store`.
    - **Alur:** Dijalankan oleh fungsi `store` di `app/Controllers/Admin/CategoryController.php`.
  - `GET admin/categories/create`: Ditangani oleh `Admin\CategoryController::create`.
    - **Alur:** Dijalankan oleh fungsi `create` di `app/Controllers/Admin/CategoryController.php`.
  - `GET admin/categories/(:num)/edit`: Ditangani oleh `Admin\CategoryController::edit/$1`.
    - **Alur:** Dijalankan oleh fungsi `edit` di `app/Controllers/Admin/CategoryController.php`.
  - `POST admin/categories/(:num)`: Ditangani oleh `Admin\CategoryController::update/$1`.
    - **Alur:** Dijalankan oleh fungsi `update` di `app/Controllers/Admin/CategoryController.php`.
  - `GET admin/categories/(:num)/delete`: Ditangani oleh `Admin\CategoryController::delete/$1`.
    - **Alur:** Dijalankan oleh fungsi `delete` di `app/Controllers/Admin/CategoryController.php`.

- **Manajemen Pengguna (CRUD):**

  - `GET admin/users`: Ditangani oleh `Admin\UserController::index`.
    - **Alur:** Dijalankan oleh fungsi `index` di `app/Controllers/Admin/UserController.php`.
  - `POST admin/users`: Ditangani oleh `Admin\UserController::store`.
    - **Alur:** Dijalankan oleh fungsi `store` di `app/Controllers/Admin/UserController.php`.
  - `GET admin/users/create`: Ditangani oleh `Admin\UserController::create`.
    - **Alur:** Dijalankan oleh fungsi `create` di `app/Controllers/Admin/UserController.php`.
  - `GET admin/users/(:num)/edit`: Ditangani oleh `Admin\UserController::edit/$1`. - **Alur:** Dijalankan oleh fungsi `edit` di `app/Controllers/Admin/UserController.php`.
  - `POST admin/users/(:num)`: Ditangani oleh `Admin\UserController::update/$1`.
    - **Alur:** Dijalankan oleh fungsi `update` di `app/Controllers/Admin/UserController.php`.
  - `GET admin/users/(:num)/delete`: Ditangani oleh `Admin\UserController::delete/$1`.
    - **Alur:** Dijalankan oleh fungsi `delete` di `app/Controllers/Admin/UserController.php`.

- **Manajemen Pendaftaran:**
  - `GET admin/enrollments`: Melihat data pendaftaran pengguna. Ditangani oleh `Admin\EnrollmentController::index`.
    - **Alur:** Dijalankan oleh fungsi `index` di `app/Controllers/Admin/EnrollmentController.php`.
  - `GET admin/enrollments/(:num)`: Melihat detail pendaftaran. Ditangani oleh `Admin\EnrollmentController::show/$1`.
    - **Alur:** Dijalankan oleh fungsi `show` di `app/Controllers/Admin/EnrollmentController.php`.

---

### Grup Rute: `/admin/api` (API Admin)

API internal khusus untuk operasi admin yang biasanya dipanggil melalui AJAX.

- `POST admin/api/courses/(:num)/status`: Mengubah status kursus (draft/published). Ditangani oleh `Admin\CourseController::updateStatus/$1`.
  - **Alur:** Dijalankan oleh fungsi `updateStatus` di `app/Controllers/Admin/CourseController.php`.
- `POST admin/api/courses/(:num)/feature`: Mengubah status unggulan (featured) kursus. Ditangani oleh `Admin\CourseController::toggleFeatured/$1`.
  - **Alur:** Dijalankan oleh fungsi `toggleFeatured` di `app/Controllers/Admin/CourseController.php`.
- `POST admin/api/courses/(:num)/modules/reorder`: Mengubah urutan modul. Ditangani oleh `Admin\ModuleController::reorder/$1`.
  - **Alur:** Dijalankan oleh fungsi `reorder` di `app/Controllers/Admin/ModuleController.php`.
- `POST admin/api/courses/(:num)/modules/(:num)/lessons/reorder`: Mengubah urutan materi. Ditangani oleh `Admin\LessonController::reorder/$1/$2`.
  - **Alur:** Dijalankan oleh fungsi `reorder` di `app/Controllers/Admin/LessonController.php`.
- `GET admin/api/stats/overview`: Mengambil data statistik untuk dasbor. Ditangani oleh `Admin\DashboardController::getOverviewStats`.
  - **Alur:** Dijalankan oleh fungsi `getOverviewStats` di `app/Controllers/Admin/DashboardController.php`.
- `GET admin/api/stats/enrollments`: Mengambil data statistik pendaftaran. Ditangani oleh `Admin\DashboardController::getEnrollmentStats`.
  - **Alur:** Dijalankan oleh fungsi `getEnrollmentStats` di `app/Controllers/Admin/DashboardController.php`.
