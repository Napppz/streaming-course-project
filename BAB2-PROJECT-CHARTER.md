BAB II
PROJECT REPORT

2.1 Metode Pengembangan Perangkat Lunak
	Metode pengembangan perangkat lunak yang digunakan dalam penelitian ini adalah Agile Scrum. Agile Scrum merupakan pendekatan pengembangan perangkat lunak yang bersifat iteratif, inkremental, dan adaptif, di mana sistem dikembangkan melalui beberapa sprint dengan fokus pekerjaan yang jelas pada setiap periode pengerjaan. Dengan metode ini, setiap fitur dapat dikembangkan, diuji, dan dievaluasi secara bertahap sehingga hasil akhir sistem lebih sesuai dengan kebutuhan pengguna.

	Penerapan metode Agile Scrum pada platform e-learning / streaming course berbasis website dipilih karena kebutuhan sistem dapat berkembang selama proses pengembangan berlangsung. Pada aplikasi ini, fitur seperti pendaftaran kursus, konten pembelajaran, dashboard pengguna, panel admin, pembayaran, serta role-based access control (RBAC) dapat diprioritaskan secara bertahap berdasarkan kebutuhan pengguna. Selain itu, Agile Scrum memungkinkan adanya umpan balik pada setiap akhir sprint sehingga pengembangan fitur dapat disesuaikan dengan hasil evaluasi dan kebutuhan operasional aplikasi.

2.2 Tahapan Pengembangan Perangkat Lunak
	Berikut adalah tahapan pengembangan platform e-learning / streaming course menggunakan metode Agile Scrum.

2.2.1 Product Backlog
	Tahap product backlog dilakukan dengan mengidentifikasi seluruh kebutuhan sistem berdasarkan analisis kebutuhan pengguna dan proses bisnis aplikasi. Pada tahap ini, kebutuhan sistem disusun ke dalam daftar prioritas pengembangan yang memuat fitur-fitur utama seperti registrasi dan login pengguna, manajemen kursus, pengelolaan materi pembelajaran berbasis video, akses konten belajar, pembayaran, pengelolaan peran pengguna, dan dashboard admin. Product backlog menjadi dasar utama dalam menentukan fitur yang akan dikerjakan pada setiap sprint.

2.2.2 Sprint Planning dan Perancangan Sistem
	Tahap sprint planning dilakukan untuk menentukan fitur yang akan dikembangkan pada sprint tertentu berdasarkan prioritas product backlog. Pada tahap ini juga dilakukan perancangan sistem secara bertahap agar proses pengembangan berjalan lebih terarah. Perancangan yang dilakukan meliputi desain antarmuka pengguna, alur navigasi sistem, struktur basis data, serta pemodelan sistem menggunakan Use Case Diagram, Activity Diagram, dan Entity Relationship Diagram (ERD). Proses perancangan disusun secara fleksibel agar dapat disesuaikan dengan hasil evaluasi dari sprint sebelumnya.

2.2.3 Sprint Development
	Tahap sprint development merupakan proses pengembangan fitur sistem berdasarkan hasil sprint planning. Pada tahap ini, aplikasi dikembangkan secara bertahap menggunakan bahasa pemrograman PHP 8 dan framework CodeIgniter 4 sebagai teknologi utama. PostgreSQL digunakan sebagai basis data untuk menyimpan data pengguna, kursus, materi, transaksi, dan hak akses. Setiap sprint difokuskan pada pengembangan fitur tertentu, seperti autentikasi pengguna, manajemen kursus, pengelolaan materi video, akses pembelajaran, pembayaran, serta pengaturan role-based access control.

2.2.4 Sprint Review dan Testing
	Tahap sprint review dan testing dilakukan pada setiap akhir sprint untuk memastikan fitur yang telah dikembangkan berjalan sesuai kebutuhan pengguna. Pengujian dilakukan menggunakan metode Black Box Testing, yaitu pengujian yang berfokus pada fungsi sistem berdasarkan masukan dan keluaran tanpa melihat struktur kode program. Pengujian dilakukan pada fitur registrasi, login, manajemen kursus, akses materi video, pembayaran, dashboard pengguna, dan panel admin. Hasil pengujian digunakan sebagai bahan evaluasi untuk memperbaiki fitur pada sprint berikutnya.

2.2.5 Sprint Retrospective
	Tahap sprint retrospective dilakukan untuk mengevaluasi proses pengembangan yang telah berjalan. Evaluasi ini mencakup kendala yang ditemukan selama sprint, efektivitas pembagian tugas, serta kualitas hasil pengembangan. Tahap ini penting untuk meningkatkan kinerja tim dan memastikan pengembangan fitur berikutnya dapat berjalan lebih baik, terstruktur, dan sesuai dengan tujuan sistem.

2.2.6 Release / Deployment
	Tahap release atau deployment dilakukan setelah fitur utama sistem selesai dikembangkan dan dinyatakan layak berdasarkan hasil pengujian pada setiap sprint. Pada tahap ini, sistem diimplementasikan pada server agar dapat diakses secara online oleh pengguna. Sebelum sistem digunakan secara penuh, dilakukan konfigurasi basis data, pengaturan akses pengguna, dan pengujian akhir untuk memastikan aplikasi berjalan dengan baik pada lingkungan operasional.

2.3 Timeline dan Milestone Pengembangan
	Pengembangan sistem dilakukan secara bertahap berdasarkan sprint agar setiap fitur dapat diselesaikan, diuji, dan dievaluasi sebelum masuk ke tahap berikutnya. Timeline pengembangan pada penelitian ini dirancang sebagai berikut:

	1. Minggu ke-1: Identifikasi kebutuhan sistem dan penyusunan product backlog
	2. Minggu ke-2: Sprint planning dan perancangan awal sistem
	3. Minggu ke-3 sampai ke-4: Sprint 1, pengembangan fitur registrasi, login, dan manajemen pengguna
	4. Minggu ke-5 sampai ke-6: Sprint 2, pengembangan fitur manajemen kursus dan materi pembelajaran
	5. Minggu ke-7 sampai ke-8: Sprint 3, pengembangan fitur video pembelajaran, akses konten, dan pembayaran
	6. Minggu ke-9: Sprint 4, pengembangan fitur dashboard admin, RBAC, dan penyempurnaan sistem
	7. Minggu ke-10: Final testing, release, dan deployment sistem

	Dengan pembagian waktu tersebut, proses pengembangan menjadi lebih terukur karena setiap sprint menghasilkan bagian sistem yang dapat diuji dan dievaluasi sebelum dilanjutkan ke sprint berikutnya.
