## 3.7 Software Architecture

### 3.7.1 Gambaran Umum Arsitektur Sistem
	Arsitektur perangkat lunak pada platform e-learning / streaming course ini dibangun menggunakan framework CodeIgniter 4 dengan pendekatan Model-View-Controller (MVC). Pendekatan ini dipilih karena mampu memisahkan tanggung jawab antara pengelolaan tampilan, logika aplikasi, dan akses data sehingga sistem lebih terstruktur, mudah dipelihara, serta lebih fleksibel untuk dikembangkan.

	Secara umum, alur kerja sistem dimulai dari permintaan pengguna melalui browser, kemudian URL dipetakan oleh file routing ke controller yang sesuai. Controller akan memproses logika bisnis, berinteraksi dengan model untuk mengambil atau menyimpan data ke database, lalu mengembalikan hasil ke view agar dapat ditampilkan kepada pengguna. Pola ini digunakan pada seluruh area aplikasi, mulai dari halaman publik, area pengguna, hingga panel admin.

### 3.7.2 Pola Model-View-Controller (MVC)
	Pola MVC menjadi dasar utama dalam implementasi aplikasi ini. Pada lapisan model, sistem menangani akses data seperti kursus, kategori, modul, lesson, enrollment, progres belajar, review, dan transaksi pembayaran. Pada lapisan controller, sistem mengatur alur request, validasi data, serta proses bisnis sesuai kebutuhan halaman yang diakses. Sementara itu, lapisan view digunakan untuk menampilkan antarmuka pengguna seperti halaman login, katalog kursus, dashboard pengguna, halaman belajar, dan panel admin.

	Pemisahan tersebut membuat kode program lebih mudah dipahami karena setiap komponen memiliki tanggung jawab yang jelas. Selain itu, arsitektur MVC juga memudahkan pengembangan fitur baru tanpa harus mengubah seluruh struktur aplikasi.

### 3.7.3 Pembagian Modul Berdasarkan Akses Pengguna
	Aplikasi ini memiliki pembagian modul yang jelas berdasarkan hak akses pengguna. Pengunjung dapat mengakses halaman publik, seperti landing page, daftar kursus, dan halaman detail kursus. Pengguna terdaftar dapat mengakses fitur pembelajaran, enrollment, progress tracking, pembayaran kursus premium, ulasan, sertifikat, dan pengelolaan profil. Sementara itu, admin memiliki akses ke modul pengelolaan kursus, modul, lesson, kategori, pengguna, enrollment, review, serta transaksi pembayaran.

	Pembagian modul ini membuat setiap aktor hanya berinteraksi dengan fitur yang relevan dengan perannya. Dengan demikian, sistem menjadi lebih aman, rapi, dan mudah dikelola.

### 3.7.4 Arsitektur Routing dan Kontrol Akses
	File `app/Config/Routes.php` berperan sebagai pusat pemetaan URL ke controller dan method yang sesuai. Pada proyek ini, route dibagi menjadi beberapa kelompok utama, yaitu route publik, route untuk pengguna yang sudah login, route untuk panel admin, route API internal, dan route callback pembayaran.

	Pengamanan akses dilakukan menggunakan filter. Filter `user` digunakan untuk membatasi halaman yang hanya boleh diakses oleh pengguna yang sudah autentikasi, sedangkan filter `admin` digunakan untuk membatasi halaman panel admin. Struktur ini mendukung penerapan role-based access control (RBAC) sederhana dengan role `user`, `admin`, dan `super_admin`.

### 3.7.5 Arsitektur Data dan Relasi Entitas
	Struktur data pada aplikasi dirancang agar mendukung proses pembelajaran secara terorganisasi. Tabel utama yang digunakan meliputi `users`, `courses`, `categories`, `course_categories`, `modules`, `lessons`, `enrollments`, `lesson_progress`, `course_reviews`, dan `course_payment_transactions`. Relasi antar tabel tersebut membentuk alur data dari pembuatan kursus, pengelompokan kategori, penyusunan modul dan lesson, hingga pencatatan progres belajar pengguna.

	Selain itu, pada kursus premium, transaksi pembayaran juga dicatat secara terpisah agar status pembelian dapat dimonitor dengan lebih jelas. Desain ini membantu sistem membedakan antara kursus gratis dan kursus berbayar tanpa mengganggu alur pembelajaran utama.

### 3.7.6 Arsitektur Pembelajaran dan Progres Pengguna
	Arsitektur pembelajaran dirancang agar pengguna dapat mengikuti kursus secara bertahap sesuai struktur materi. Setelah melakukan enrollment, pengguna dapat membuka modul dan lesson yang tersedia, menonton video pembelajaran, membaca materi, lalu menandai lesson sebagai selesai. Sistem kemudian menyimpan progres belajar secara otomatis sehingga pengguna dapat melihat sejauh mana penyelesaian kursus yang sedang diikuti.

	Pendekatan ini tidak hanya mendukung pembelajaran mandiri, tetapi juga membantu pengguna melanjutkan materi dari titik terakhir yang sudah dipelajari. Dengan demikian, pengalaman belajar menjadi lebih terarah dan nyaman.

### 3.7.7 Arsitektur Pembayaran Kursus Premium
	Salah satu komponen penting pada aplikasi ini adalah dukungan terhadap kursus premium. Saat pengguna memilih kursus premium, sistem membuat transaksi pembayaran dan mengarahkan pengguna ke payment link yang disediakan oleh layanan pembayaran eksternal. Setelah pembayaran tervalidasi melalui callback atau webhook, status transaksi diperbarui dan akses kursus diberikan melalui enrollment yang sah.

	Arsitektur ini memastikan bahwa akses ke materi premium hanya diberikan setelah pembayaran benar-benar berhasil. Dengan begitu, sistem mampu menjaga keamanan transaksi sekaligus menyediakan pengalaman pembelian yang terintegrasi.

### 3.7.8 Arsitektur Antarmuka Pengguna
	Lapisan antarmuka pada aplikasi disusun untuk mendukung kebutuhan tiap aktor. View untuk halaman publik digunakan untuk menampilkan informasi kursus secara umum, sedangkan view pengguna menampilkan dashboard, daftar kursus yang diikuti, halaman belajar, riwayat pembayaran, dan sertifikat. Pada sisi admin, view difokuskan pada halaman pengelolaan data dan monitoring aktivitas sistem.

	Pemisahan folder view berdasarkan peran pengguna membuat tampilan lebih mudah dipelihara dan konsisten dengan alur penggunaan sistem.

### 3.7.9 Ringkasan Arsitektur
	Secara keseluruhan, arsitektur perangkat lunak pada platform e-learning / streaming course ini dirancang untuk mendukung pengelolaan konten pembelajaran, kontrol akses berbasis role, pencatatan progres belajar, interaksi pengguna melalui review, serta transaksi kursus premium. Dengan penerapan CodeIgniter 4 dan pola MVC, sistem memiliki struktur yang lebih modular, terorganisasi, dan siap untuk dikembangkan lebih lanjut sesuai kebutuhan akademik maupun operasional.
