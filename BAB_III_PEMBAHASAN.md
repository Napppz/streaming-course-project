# BAB III

# PEMBAHASAN

## 3.1 Analisis Kebutuhan Sistem

Pada bab ini diuraikan analisis kebutuhan sistem untuk aplikasi e-learning berbasis video yang dikembangkan. Analisis dilakukan dengan melihat kondisi pembelajaran yang umum digunakan masyarakat, mengidentifikasi kelemahan pada sistem yang sedang berjalan, kemudian menerjemahkannya menjadi kebutuhan fungsional yang harus dipenuhi oleh sistem.

Pengembangan aplikasi ini tidak hanya berfokus pada penyediaan materi belajar secara daring, tetapi juga pada penyusunan alur belajar yang lebih terstruktur, pelacakan progres pengguna, pengelolaan kursus oleh admin, serta penambahan fitur **kursus premium** yang memungkinkan monetisasi materi belajar tertentu. Dengan adanya fitur tersebut, sistem yang dirancang menjadi lebih lengkap karena mendukung baik kursus gratis maupun kursus berbayar dalam satu platform.

Secara umum, kebutuhan sistem yang dianalisis dalam penelitian ini meliputi:

1. kebutuhan akses belajar yang fleksibel tanpa terikat tempat dan waktu;
2. kebutuhan penyediaan materi belajar yang terstruktur dan mudah diikuti;
3. kebutuhan pelacakan progres pembelajaran secara otomatis;
4. kebutuhan interaksi pengguna terhadap kualitas kursus melalui ulasan dan rating;
5. kebutuhan pengelolaan konten dan pengguna oleh admin;
6. kebutuhan mekanisme pembelian kursus premium yang aman, terintegrasi, dan terdokumentasi.

Dengan demikian, analisis kebutuhan sistem ini menjadi dasar penting dalam merancang solusi aplikasi e-learning yang lebih efektif, terstruktur, dan sesuai dengan perkembangan kebutuhan pembelajaran digital saat ini.

## 3.2 Analisis Sistem Berjalan

Analisis sistem berjalan bertujuan untuk memahami metode dan proses pembelajaran programming yang saat ini banyak diakses oleh masyarakat umum. Pemahaman ini diperlukan agar solusi yang dirancang benar-benar menjawab kekurangan dari metode yang sudah ada. Secara umum, terdapat dua model utama yang banyak digunakan, yaitu pembelajaran konvensional (tatap muka) dan pembelajaran daring tidak terstruktur.

### 3.2.1 Sistem Pembelajaran Konvensional (Tatap Muka)

Metode ini merupakan cara tradisional dalam menimba ilmu, seperti kursus, seminar, atau workshop yang mengharuskan kehadiran fisik peserta. Proses belajar berlangsung melalui interaksi langsung antara instruktur dan peserta pada lokasi serta waktu yang telah ditentukan.

**Alur proses sistem pembelajaran konvensional:**

1. **Pendaftaran dan pembayaran**
  Calon peserta mencari informasi kursus, melakukan pendaftaran, kemudian membayar biaya kursus yang umumnya relatif tinggi.
2. **Persiapan materi**
  Instruktur menyiapkan bahan ajar dalam bentuk slide presentasi, dokumen cetak, atau file digital yang akan dibagikan kepada peserta.
3. **Sesi pembelajaran**
  Proses belajar dilakukan secara sinkron di kelas. Instruktur menjelaskan materi secara langsung, sementara peserta menyimak, mencatat, dan bertanya di tempat.
4. **Distribusi materi**
  Materi diberikan secara fisik di kelas atau dikirim melalui email setelah sesi selesai.
5. **Evaluasi dan pelacakan progres**
  Kemajuan belajar biasanya dicatat secara manual oleh instruktur atau melalui penilaian tugas yang dikumpulkan secara offline. Peserta tidak memiliki dasbor digital untuk memantau progres belajar secara mandiri.

### 3.2.2 Sistem Pembelajaran Daring Tidak Terstruktur

Perkembangan internet mendorong masyarakat untuk belajar melalui sumber daya daring gratis seperti YouTube, blog, forum, dan media sosial. Meskipun lebih fleksibel dibanding pembelajaran tatap muka, pendekatan ini memiliki banyak keterbatasan dari sisi struktur dan konsistensi materi.

**Alur proses sistem pembelajaran daring tidak terstruktur:**

1. **Pencarian mandiri**
  Masyarakat mencari sendiri topik programming tertentu melalui mesin pencari atau platform video.
2. **Konsumsi konten**
  Pengguna menonton video tutorial atau membaca artikel dari berbagai sumber yang berbeda.
3. **Tidak ada kurikulum yang jelas**
  Materi yang diakses sering kali tidak berurutan, tidak saling terhubung, dan tidak mengikuti jalur belajar yang sistematis.
4. **Kualitas dan kredibilitas bervariasi**
  Sumber belajar berasal dari banyak pembuat konten dengan kualitas berbeda-beda, sehingga tidak selalu akurat atau mengikuti best practice.
5. **Pelacakan progres manual**
  Pengguna harus mengingat sendiri materi yang telah dipelajari, biasanya melalui catatan pribadi atau bookmark browser, tanpa dukungan sistem yang mampu menampilkan progres secara otomatis.
6. **Akses materi premium terpisah**
  Dalam praktiknya, sebagian materi berkualitas tinggi tersedia dalam bentuk kursus berbayar di platform tertentu. Namun, pembelian, akses konten, dan pelacakan progres sering berada pada sistem yang terpisah, sehingga pengalaman pengguna kurang terintegrasi.

## 3.3 Identifikasi Masalah pada Sistem Berjalan

Berdasarkan dua model pembelajaran di atas, dapat diidentifikasi beberapa masalah mendasar yang menghambat proses belajar masyarakat secara efektif dan efisien.

1. **Ketergantungan pada lokasi dan waktu**
  Sistem konvensional tidak fleksibel karena mewajibkan kehadiran fisik di tempat dan waktu tertentu.
2. **Biaya pembelajaran relatif tinggi**
  Kursus tatap muka sering menuntut biaya pendaftaran yang besar, ditambah biaya transportasi dan akomodasi.
3. **Kurangnya struktur pada sumber belajar daring**
  Banyak materi daring tersedia secara acak dan tidak membentuk jalur belajar yang sistematis bagi pemula.
4. **Distribusi materi tidak efisien**
  Materi cetak sulit diperbarui, sedangkan materi digital yang tersebar di banyak platform sulit dikelola versinya.
5. **Format konten terbatas**
  Sebagian besar materi masih berbasis teks atau video yang terpisah dari sistem pembelajaran, sehingga pengalaman belajar kurang terpadu.
6. **Tidak ada pelacakan progres terintegrasi**
  Pengguna tidak dapat melihat sejauh mana progres belajar mereka secara otomatis dalam satu sistem.
7. **Interaksi dan umpan balik terbatas**
  Pada sistem daring tidak terstruktur, pengguna sulit bertanya atau memberikan umpan balik terhadap materi.
8. **Belum adanya mekanisme monetisasi yang terintegrasi**
  Materi premium biasanya tersebar di platform lain, sehingga pembelian, validasi pembayaran, dan akses kursus tidak berada dalam satu alur yang konsisten.

## 3.4 Analisis Permasalahan

Berdasarkan analisis sistem berjalan, diidentifikasi sejumlah permasalahan spesifik yang perlu diselesaikan oleh aplikasi e-learning yang diusulkan. Permasalahan tersebut dikelompokkan ke dalam empat area utama.

### 3.4.1 Permasalahan Keterbatasan Akses dan Fleksibilitas

Pembelajaran konvensional masih sangat bergantung pada kehadiran fisik, sehingga menimbulkan beberapa kendala:

1. **Hambatan geografis dan mobilitas**
  Sulit diakses oleh masyarakat yang tinggal jauh dari lokasi kursus atau memiliki keterbatasan mobilitas.
2. **Biaya tambahan**
  Pengguna harus mengeluarkan biaya transportasi dan mengorbankan waktu perjalanan.
3. **Konflik dengan kewajiban lain**
  Jadwal kelas dapat berbenturan dengan pekerjaan, aktivitas akademik, atau kewajiban keluarga.
4. **Perbedaan kecepatan belajar**
  Sistem tatap muka cenderung tidak mampu menyesuaikan kecepatan belajar masing-masing individu.
5. **Kesulitan mengulang materi**
  Peserta tidak dapat mengulang materi secara instan kapan saja sesuai kebutuhan.

### 3.4.2 Permasalahan Pengelolaan, Distribusi, dan Kualitas Konten

Sumber belajar daring yang tersebar memiliki beberapa kelemahan mendasar, yaitu:

1. **Tidak ada alur belajar yang jelas**
  Pengguna kesulitan menentukan materi awal, urutan topik, dan langkah belajar berikutnya.
2. **Kualitas materi tidak seragam**
  Validitas, kedalaman, dan akurasi materi sangat bergantung pada pembuat konten.
3. **Pembaruan materi tidak terpusat**
  Pengguna harus mencari sendiri pembaruan teknologi atau materi terbaru.
4. **Penyajian materi kurang terpadu**
  Materi video, teks, dan informasi kursus sering berada di tempat yang terpisah.
5. **Keterbatasan akses ke materi premium berkualitas**
  Banyak materi yang lebih lengkap tersedia dalam bentuk berbayar, namun pengguna tidak memiliki satu platform yang mengintegrasikan pembelian, akses, dan pembelajaran.

### 3.4.3 Permasalahan Interaksi dan Pelacakan Kemajuan

Dalam pembelajaran mandiri, pengguna juga menghadapi masalah pada aspek pemantauan kemajuan belajar, antara lain:

1. **Tidak ada tempat bertanya secara langsung**
  Pengguna kesulitan memperoleh bantuan saat mengalami error atau kebingungan pada materi.
2. **Tidak ada mekanisme umpan balik yang sistematis**
  Pengguna tidak dapat menilai kualitas kursus secara terstruktur sehingga penyedia konten sulit mengevaluasi materi.
3. **Progres belajar tidak terukur**
  Pengguna tidak dapat melihat persentase penyelesaian materi secara otomatis.
4. **Kehilangan jejak belajar**
  Pengguna mudah lupa pelajaran terakhir yang dipelajari, terutama jika mengikuti beberapa kursus sekaligus.
5. **Tidak ada rasa pencapaian**
  Ketiadaan indikator progres mengurangi motivasi pengguna untuk menyelesaikan kursus.

### 3.4.4 Permasalahan Transaksi dan Akses Kursus Premium

Dengan adanya pengembangan kursus premium, muncul pula kebutuhan dan permasalahan baru yang harus diselesaikan oleh sistem, yaitu:

1. **Pemisahan kursus gratis dan premium**
  Sistem harus mampu membedakan kursus yang dapat langsung diikuti dengan kursus yang harus dibeli terlebih dahulu.
2. **Validasi pembayaran sebelum pemberian akses**
  Pengguna tidak boleh langsung memperoleh akses belajar hanya karena membuka halaman checkout atau redirect pembayaran.
3. **Pencatatan histori transaksi**
  Sistem memerlukan penyimpanan data transaksi agar admin dapat memantau status pembayaran dan pengguna dapat melihat riwayat pembeliannya.
4. **Pencegahan transaksi ganda**
  Sistem harus menghindari pembuatan transaksi pending yang berulang untuk kursus yang sama pada pengguna yang sama.
5. **Kontrol akses yang aman**
  Akses ke kursus premium hanya boleh diberikan jika pembayaran telah tervalidasi dan enrollment telah dibuat secara sah.

## 3.5 Analisis Kebutuhan Fungsional

Analisis kebutuhan fungsional mendefinisikan fitur dan kemampuan yang harus dimiliki oleh sistem untuk menyelesaikan permasalahan yang telah diidentifikasi. Dalam sistem ini kebutuhan fungsional dibagi menurut aktor: **Admin**, **Pengguna terdaftar (Masyarakat)**, dan **Pengunjung yang belum atau tidak sedang terautentikasi** (akses publik tanpa sesi login).

### 3.5.1 Kebutuhan Fungsional untuk Admin

Admin adalah pengelola utama sistem yang bertanggung jawab atas data kursus, materi pembelajaran, pengguna, ulasan, dan transaksi pembayaran. Pada versi pengembangan terbaru, sistem juga mendukung peran **super admin** untuk pengelolaan role yang lebih tinggi.


| Nama Kebutuhan                | Deskripsi                                                                                                                                                           |
| ----------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Autentikasi Admin             | Admin harus memiliki halaman login yang aman untuk masuk ke sistem. Admin dan super admin diarahkan ke dasbor admin setelah berhasil login.                         |
| Dasbor Admin                  | Admin dapat melihat ringkasan statistik aplikasi, seperti jumlah pengguna, jumlah kursus, dan data transaksi pembayaran premium.                                    |
| Manajemen Kursus (CRUD)       | Admin dapat membuat, melihat, memperbarui, dan menghapus data kursus. Data yang dikelola mencakup judul, deskripsi, thumbnail, kategori, status, level, dan durasi. |
| Pengaturan Kursus Premium     | Admin dapat menandai suatu kursus sebagai premium, menentukan harga, mata uang, serta status apakah kursus tersebut dapat dibeli atau tidak.                        |
| Manajemen Modul (CRUD)        | Admin dapat menambah, melihat, mengedit, dan menghapus modul pada setiap kursus.                                                                                    |
| Manajemen Pelajaran (CRUD)    | Admin dapat membuat, mengedit, dan menghapus pelajaran pada setiap modul, termasuk video pembelajaran dan deskripsi materi.                                         |
| Manajemen Kategori (CRUD)     | Admin dapat menambah, mengedit, dan menghapus kategori untuk mengelompokkan kursus.                                                                                 |
| Manajemen Pengguna            | Admin dapat melihat daftar pengguna dan detail pengguna. Pada implementasi terbaru, pengelolaan role admin atau super admin dibatasi khusus untuk super admin.      |
| Manajemen Role Pengguna       | Super admin dapat membuat atau mengubah pengguna menjadi admin atau super admin, sedangkan admin biasa hanya dapat mengelola pengguna umum.                         |
| Manajemen Ulasan              | Admin dapat melihat dan menghapus ulasan kursus yang tidak sesuai.                                                                                                  |
| Monitoring Enrollment         | Admin dapat melihat data enrollment pengguna pada kursus yang tersedia.                                                                                             |
| Monitoring Pembayaran Premium | Admin dapat melihat daftar transaksi pembayaran premium beserta statusnya, detail pengguna, detail kursus, nominal pembayaran, dan histori payload pembayaran.      |


**Tabel Kebutuhan Fungsional untuk Admin**

### 3.5.2 Kebutuhan Fungsional untuk Pengguna (Masyarakat)

Pengguna adalah pihak yang **telah terdaftar dan terautentikasi**, yang menggunakan platform untuk mencari, membeli, dan mempelajari kursus yang tersedia. Kebutuhan pada tabel ini mengasumsikan pengguna telah login, kecuali jika teks deskripsi menyatakan lain.


| Nama Kebutuhan                      | Deskripsi                                                                                                                              |
| ----------------------------------- | -------------------------------------------------------------------------------------------------------------------------------------- |
| Pendaftaran Akun                    | Masyarakat dapat membuat akun baru dengan mengisi data dasar seperti username, email, dan password.                                    |
| Autentikasi Pengguna                | Pengguna terdaftar dapat login dan logout untuk mengakses fitur pembelajaran secara personal.                                          |
| Pengelolaan Profil                  | Pengguna dapat melihat dan memperbarui data profil, seperti nama lengkap, bio, foto profil, email, dan password.                       |
| Menjelajahi Kursus                  | Pengguna dapat melihat daftar kursus yang tersedia pada platform.                                                                      |
| Filter Kursus                       | Pengguna dapat menelusuri kursus berdasarkan kategori dan level agar pencarian materi lebih mudah.                                     |
| Melihat Detail Kursus               | Pengguna dapat membuka halaman detail kursus untuk melihat deskripsi, daftar modul, daftar lesson, rating, serta ulasan pengguna lain. |
| Mendaftar Kursus Gratis             | Untuk kursus gratis, pengguna dapat langsung melakukan enrollment dan mulai belajar.                                                   |
| Melakukan Checkout Kursus Premium   | Untuk kursus premium, pengguna dapat membuka halaman checkout internal sebelum diarahkan ke payment link.                              |
| Melakukan Pembayaran Kursus Premium | Pengguna dapat membayar kursus premium melalui payment link yang telah dibuat oleh sistem.                                             |
| Melihat Status Pembelian            | Pengguna dapat mengetahui apakah transaksi berada pada status pending, paid, failed, expired, atau cancelled.                          |
| Melihat Riwayat Pembayaran          | Pengguna dapat melihat histori transaksi pembayaran premium pada dashboard pengguna.                                                   |
| Melihat Invoice Pembayaran          | Pengguna dapat membuka invoice pembayaran premium dalam format dokumen yang disediakan sistem.                                         |
| Mengakses Halaman Belajar           | Setelah memiliki akses melalui enrollment, pengguna dapat membuka halaman belajar kursus dan melihat modul serta lesson yang tersedia. |
| Kontrol Akses Kursus Premium        | Pengguna hanya dapat mengakses kursus premium jika pembayaran telah tervalidasi dan enrollment telah diberikan oleh sistem.            |
| Memutar Video Pelajaran             | Pengguna dapat membuka lesson dan memutar video pembelajaran sebagai materi utama.                                                     |
| Menandai Pelajaran Selesai          | Pengguna dapat menandai lesson sebagai selesai sehingga sistem memperbarui progres belajar secara otomatis.                            |
| Melihat Progres Belajar             | Pengguna dapat melihat persentase progres penyelesaian kursus yang diikuti.                                                            |
| Melanjutkan Pembelajaran Terakhir   | Sistem membantu pengguna kembali ke lesson terakhir atau alur pembelajaran yang relevan.                                               |
| Memberikan Ulasan dan Peringkat     | Pengguna dapat memberikan rating dan ulasan terhadap kursus yang diikuti sebagai bentuk umpan balik.                                   |


**Tabel Kebutuhan Fungsional untuk Pengguna**

### 3.5.3 Kebutuhan Fungsional untuk Pengunjung (Belum / Tidak Terautentikasi)

Pengunjung adalah siapa saja yang membuka platform **tanpa** sesi login. Kebutuhan berikut memisahkan **aksi publik** dari fitur yang baru dapat digunakan setelah autentikasi (lihat bagian 3.5.2).


| Nama Kebutuhan     | Deskripsi                                                                                                                                                                                                 |
| ------------------ | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Halaman publik     | Pengunjung dapat membuka halaman utama (landing), navigasi umum, dan informasi pengantar platform tanpa harus login.                                                                                      |
| Kursus unggulan    | Pengunjung dapat melihat kursus yang ditampilkan sebagai unggulan (featured) pada halaman publik sebagai cuplikan penawaran konten.                                                                     |
| Katalog kursus     | Pengunjung dapat melihat daftar kursus yang statusnya tersedia untuk ditampilkan ke publik (misalnya dipublikasikan), tanpa memiliki akun.                                                              |
| Filter katalog     | Pengunjung dapat menyaring katalog berdasarkan kategori dan level (jika disediakan di antarmuka publik) agar pencarian materi tetap relevan sebelum login.                                                |
| Detail kursus      | Pengunjung dapat membuka halaman detail kursus untuk membaca deskripsi, struktur modul dan daftar lesson, serta informasi ringkas lain yang ditampilkan untuk umum (termasuk indikasi gratis/premium bila ada). |
| Ulasan & rating    | Pengunjung dapat melihat ringkasan rating dan daftar ulasan yang dipublikasikan untuk umum pada halaman detail kursus, tanpa dapat mengubah data pribadi atau mengirim ulasan.                             |
| Login & daftar     | Pengunjung dapat membuka halaman login dan registrasi untuk menjadi pengguna terdaftar.                                                                                                                  |
| Akses terbatas     | Untuk fitur yang membutuhkan identitas pengguna (misalnya enrollment, checkout/pembayaran premium, halaman belajar, progres, profil, atau pemberian ulasan), sistem tidak mengizinkan eksekusi lengkap; pengunjung diberi informasi jelas dan/atau dialihkan ke halaman login atau registrasi sesuai alur aplikasi. |


**Tabel Kebutuhan Fungsional untuk Pengunjung (Non-Autentikasi)**

## 3.6 Ringkasan Pembahasan

Berdasarkan hasil analisis, dapat disimpulkan bahwa sistem e-learning yang dikembangkan dirancang untuk menjawab kelemahan pembelajaran konvensional dan pembelajaran daring tidak terstruktur. Sistem ini menyediakan jalur belajar yang lebih terorganisasi, akses yang fleksibel, pelacakan progres otomatis, serta mekanisme ulasan untuk meningkatkan kualitas materi.

Pada pengembangan terbaru, sistem juga mendukung **kursus premium**, sehingga platform tidak hanya berfungsi sebagai media pembelajaran gratis, tetapi juga sebagai sistem pembelajaran berbayar yang memiliki mekanisme checkout, pencatatan transaksi, monitoring pembayaran, dan kontrol akses yang lebih ketat. Dengan demikian, fitur-fitur yang dirancang pada aplikasi ini telah menyesuaikan kebutuhan pengguna modern sekaligus membuka peluang pengelolaan konten pembelajaran yang lebih profesional.