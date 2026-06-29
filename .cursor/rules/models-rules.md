# Dokumentasi Model

Dokumen ini memberikan penjelasan mengenai setiap Model yang digunakan dalam aplikasi, fungsionalitasnya, dan metode-metode penting yang tersedia.

---

### `UserModel.php`

Model ini bertanggung jawab untuk mengelola semua operasi terkait data pengguna, seperti otentikasi, pendaftaran, dan pengelolaan profil.

- **Tabel Terkait:** `users`
- **Fungsi Utama:**
  - Mengelola data pengguna (CRUD).
  - Melakukan hash pada password sebelum disimpan ke database menggunakan _callback_ `beforeInsert` dan `beforeUpdate`.
- **Metode Penting:**
  - `hashPassword(array $data)`: (Protected) Callback yang secara otomatis mengenkripsi password.
  - `findUserByEmailOrUsername($login)`: Mencari pengguna berdasarkan `email` atau `username`. Berguna untuk proses login.
  - `isAdmin($userId)`: Memeriksa apakah seorang pengguna memiliki peran 'admin'.

---

### `CategoryModel.php`

Model ini mengelola data yang berkaitan dengan kategori kursus.

- **Tabel Terkait:** `categories`
- **Fungsi Utama:**
  - Mengelola data kategori (CRUD).
- **Metode Penting:**
  - `getCategoriesForCourse($courseId)`: Mengambil semua kategori yang terkait dengan satu `courseId` tertentu melalui tabel pivot `course_categories`.

---

### `CourseModel.php`

Model ini adalah pusat pengelolaan data kursus. Berinteraksi dengan beberapa model lain untuk menyajikan data yang komprehensif.

- **Tabel Terkait:** `courses`
- **Fungsi Utama:**
  - Mengelola data kursus (CRUD).
- **Metode Penting:**
  - `getPublishedCourses($limit, $offset)`: Mengambil daftar kursus yang berstatus 'published'.
  - `getCoursesByCategory($categoryId, ...)`: Mendapatkan kursus berdasarkan ID kategori.
  - `getFeaturedCourses($limit)`: Mengambil kursus-kursus yang ditandai sebagai 'featured'.
  - `searchCourses($keyword, ...)`: Mencari kursus berdasarkan kata kunci pada judul atau deskripsi.
  - `getFilteredCourses($filters, ...)`: Mengambil kursus dengan filter dinamis (kategori, level, keyword).
  - `getCourseWithContent($courseId, $userId)`: Mengambil detail lengkap sebuah kursus, termasuk modul dan materi di dalamnya, beserta progres pengguna. Memanggil `ModuleModel`.

---

### `ModuleModel.php`

Model ini mengelola data modul, yang merupakan bagian dari sebuah kursus.

- **Tabel Terkait:** `modules`
- **Fungsi Utama:**
  - Mengelola data modul (CRUD) yang terikat pada sebuah kursus.
- **Metode Penting:**
  - `getModulesByCourse($courseId)`: Mengambil semua modul untuk satu `courseId`.
  - `getModulesWithLessons($courseId, $userId)`: Mengambil modul beserta seluruh materi di dalamnya untuk satu kursus. Memanggil `LessonModel`.
  - `reorderModules($courseId, $moduleOrder)`: Mengatur ulang urutan modul dalam sebuah kursus.

---

### `LessonModel.php`

Model ini mengelola data materi pembelajaran (lesson) yang merupakan bagian dari sebuah modul.

- **Tabel Terkait:** `lessons`
- **Fungsi Utama:**
  - Mengelola data materi pembelajaran (CRUD).
- **Metode Penting:**
  - `getLessonsByModule($moduleId, $userId)`: Mengambil semua materi untuk satu `moduleId`, dan jika `userId` diberikan, akan di-join dengan `lesson_progress` untuk mendapatkan status kemajuan pengguna.
  - `getNextLesson($currentLessonId)`: Mendapatkan materi selanjutnya dalam urutan, baik di dalam modul yang sama maupun di modul berikutnya.
  - `getPreviousLesson($currentLessonId)`: Mendapatkan materi sebelumnya dalam urutan.

---

### `EnrollmentModel.php`

Model ini mengelola data pendaftaran (enrollment) pengguna pada kursus.

- **Tabel Terkait:** `enrollments`
- **Fungsi Utama:**
  - Mencatat dan mengelola pendaftaran pengguna ke kursus.
  - Menghitung dan memperbarui progress keseluruhan kursus.
- **Metode Penting:**
  - `isEnrolled($userId, $courseId)`: Memeriksa apakah pengguna sudah terdaftar di sebuah kursus.
  - `getUserEnrollments($userId, ...)`: Mengambil semua kursus yang diikuti oleh seorang pengguna.
  - `updateProgress($userId, $courseId)`: Menghitung ulang dan memperbarui `progress_percentage` pada sebuah pendaftaran berdasarkan jumlah materi yang telah diselesaikan.
  - `enrollUser($userId, $courseId)`: Mendaftarkan pengguna ke kursus baru.

---

### `LessonProgressModel.php`

Model ini secara spesifik melacak kemajuan belajar pengguna untuk setiap materi pembelajaran.

- **Tabel Terkait:** `lesson_progress`
- **Fungsi Utama:**
  - Membuat dan memperbarui catatan kemajuan per materi.
- **Metode Penting:**
  - `getLessonProgress($userId, $lessonId)`: Mengambil data kemajuan spesifik satu pengguna pada satu materi.
  - `getLessonProgressForCourse($userId, $courseId)`: Mengambil semua data kemajuan seorang pengguna untuk seluruh materi dalam satu kursus.
  - `markAsStarted($userId, $lessonId)`: Menandai materi sebagai 'in_progress'.
  - `markAsCompleted($userId, $lessonId)`: Menandai materi sebagai 'completed' dan memicu pembaruan progress di `EnrollmentModel`.

---

### `CourseReviewModel.php`

Model ini mengelola ulasan dan rating yang diberikan oleh pengguna terhadap kursus.

- **Tabel Terkait:** `course_reviews`
- **Fungsi Utama:**
  - Mengelola data ulasan (CRUD).
- **Metode Penting:**
  - `getCourseReviews($courseId)`: Mengambil semua ulasan untuk sebuah kursus, beserta data pengguna yang memberikan ulasan.
  - `getAverageRating($courseId)`: Menghitung rata-rata rating untuk sebuah kursus.
  - `getRatingDistribution($courseId)`: Menghitung distribusi rating (jumlah bintang 5, 4, 3, 2, 1).
  - `hasUserReviewed($courseId, $userId)`: Memeriksa apakah seorang pengguna sudah pernah memberikan ulasan untuk kursus tersebut.
  - `getUserReview($courseId, $userId)`: Mengambil ulasan spesifik dari seorang pengguna untuk satu kursus.
