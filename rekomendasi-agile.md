 metode **Waterfall** diubah menjadi **Agile**, bagian yang perlu diubah terutama ada pada **alur tahapan, alasan pemilihan metode, penjelasan proses kerja, testing, deployment, dan timeline**.

## 1. Bagian “Metode Pengembangan Sistem” harus diubah

Saat ini isinya menjelaskan bahwa pengembangan dilakukan **secara berurutan** dari analisis, desain, implementasi, testing, lalu deployment. Itu cocok untuk Waterfall.

Kalau diganti Agile, penjelasannya harus menjadi bahwa pengembangan dilakukan **secara iteratif dan bertahap melalui sprint**. Jadi sistem tidak langsung dibuat penuh dari awal sampai akhir, tetapi dikembangkan sedikit demi sedikit berdasarkan prioritas kebutuhan.

Contoh arah ubahannya:

> Metode pengembangan sistem yang digunakan dalam penelitian ini adalah metode Agile. Metode Agile merupakan pendekatan pengembangan perangkat lunak yang dilakukan secara iteratif dan inkremental, di mana proses pengembangan dibagi ke dalam beberapa siklus pendek atau sprint. Setiap sprint menghasilkan bagian sistem yang dapat diuji dan dievaluasi, sehingga perubahan kebutuhan pengguna dapat diakomodasi selama proses pengembangan berlangsung.

## 2. Alasan pemilihan metode juga perlu diubah

Di teks sekarang tertulis Waterfall dipilih karena kebutuhan sistem sudah jelas sejak awal. Itu kurang cocok untuk Agile.

Untuk Agile, alasannya harus diarahkan ke:

sistem e-learning berbasis video memungkinkan adanya perubahan fitur, evaluasi dari pengguna, dan perbaikan bertahap.

Contoh:

> Metode Agile dipilih karena pengembangan sistem e-learning berbasis video membutuhkan fleksibilitas dalam menyesuaikan kebutuhan pengguna. Fitur seperti pengelolaan kursus, materi video, evaluasi pembelajaran, dan pengelolaan pengguna dapat dikembangkan secara bertahap melalui sprint. Dengan pendekatan ini, setiap fitur dapat diuji, dievaluasi, dan diperbaiki berdasarkan umpan balik pengguna sebelum sistem diterapkan secara keseluruhan.

## 3. Judul “Tahapan Metodologi Waterfall” harus diganti

Ubah menjadi:

**2. Tahapan Metodologi Agile**

Lalu isi paragraf pembukanya juga perlu diganti. Jangan lagi menyebut lima tahap Waterfall seperti:

> Requirement Analysis, System Design, Implementation, Testing, dan Deployment

Untuk Agile, tahapannya bisa dibuat seperti ini:

1. Product Backlog
2. Sprint Planning
3. Sprint Development
4. Sprint Review dan Testing
5. Sprint Retrospective
6. Deployment / Release

## 4. Subbab “Requirement Analysis” perlu diganti menjadi “Product Backlog”

Pada Agile, kebutuhan sistem tidak hanya dianalisis di awal, tetapi dikumpulkan dalam bentuk **product backlog**.

Bagian kebutuhan fungsional dan nonfungsional masih bisa dipakai, tetapi kalimatnya perlu disesuaikan.

Misalnya:

> Pada tahap Product Backlog, peneliti mengidentifikasi kebutuhan sistem berdasarkan fitur-fitur utama yang akan dikembangkan. Kebutuhan tersebut disusun ke dalam daftar prioritas pengembangan, seperti registrasi pengguna, login, pengelolaan kursus, pengelolaan materi pembelajaran berbasis video, akses video pembelajaran, evaluasi pembelajaran, dan penyimpanan data. Product backlog digunakan sebagai dasar dalam menentukan fitur yang akan dikerjakan pada setiap sprint.

## 5. Subbab “System Design” perlu diubah menjadi “Sprint Planning dan Perancangan Sistem”

Desain sistem tetap boleh ada, tetapi jangan dibuat seolah-olah seluruh rancangan harus selesai sebelum implementasi. Dalam Agile, desain bisa dibuat bertahap sesuai fitur yang dikerjakan.

Bagian MVC, database, Use Case Diagram, Activity Diagram, dan ERD masih bisa dipakai.

Namun kalimatnya perlu diubah menjadi:

> Pada tahap Sprint Planning, peneliti menentukan fitur yang akan dikembangkan pada sprint tertentu berdasarkan prioritas pada product backlog. Selain itu, dilakukan perancangan teknis untuk mendukung pengembangan fitur, seperti perancangan arsitektur sistem, desain basis data, rancangan antarmuka pengguna, serta pemodelan sistem menggunakan Use Case Diagram, Activity Diagram, dan Entity Relationship Diagram.

## 6. Subbab “Implementation” perlu diubah menjadi “Sprint Development”

Bagian teknologi seperti PHP 8, CodeIgniter 4, dan PostgreSQL tetap bisa dipakai.

Yang perlu diubah adalah alurnya. Jangan ditulis sebagai satu tahap implementasi besar, tapi sebagai pengembangan per sprint.

Contoh:

> Tahap Sprint Development merupakan proses pengembangan fitur sistem berdasarkan hasil sprint planning. Pada tahap ini, peneliti membangun sistem e-learning berbasis video secara bertahap menggunakan bahasa pemrograman PHP 8, framework CodeIgniter 4, dan basis data PostgreSQL. Setiap sprint berfokus pada pengembangan fitur tertentu, seperti autentikasi pengguna, pengelolaan kursus, pengelolaan modul pembelajaran, pengelolaan video, serta evaluasi pembelajaran.

## 7. Subbab “Testing” perlu diubah menjadi “Sprint Review dan Testing”

Di Waterfall, testing dilakukan setelah implementasi. Dalam Agile, testing dilakukan di setiap sprint.

Jadi bagian Black Box Testing masih bisa dipakai, tetapi posisinya berubah menjadi pengujian setiap hasil sprint.

Contoh:

> Pengujian dilakukan pada setiap akhir sprint untuk memastikan fitur yang dikembangkan telah berjalan sesuai kebutuhan. Metode pengujian yang digunakan adalah Black Box Testing, yaitu pengujian yang berfokus pada fungsi sistem berdasarkan masukan dan keluaran tanpa melihat struktur kode program. Pengujian dilakukan terhadap fitur-fitur seperti registrasi, login, pengelolaan kursus, akses materi pembelajaran, pemutaran video, evaluasi pembelajaran, dan penyimpanan data.

## 8. Subbab “Deployment” perlu diubah menjadi “Release / Deployment”

Deployment tetap ada, tetapi dalam Agile bisa dilakukan setelah beberapa sprint selesai atau setelah fitur utama stabil.

Contoh:

> Tahap Release atau Deployment dilakukan setelah fitur utama sistem selesai dikembangkan dan telah melalui proses pengujian pada setiap sprint. Sistem e-learning berbasis video kemudian dipasang pada server agar dapat diakses secara online oleh pengguna. Sebelum diterapkan, dilakukan konfigurasi basis data, pengaturan akses sistem, serta pengujian akhir pada lingkungan pengguna.

## 9. Timeline dan Milestone harus diubah total

Timeline Waterfall yang sekarang seperti ini:

* Requirement Analysis: Minggu ke-1 dan 2
* System Design: Minggu ke-3 dan 4
* Implementation: Minggu ke-5 sampai ke-8
* Testing: Minggu ke-9
* Deployment: Minggu ke-10

Kalau Agile, sebaiknya dibuat berdasarkan sprint. Misalnya:

**Timeline dan Milestone Agile**

* Minggu ke-1: Identifikasi kebutuhan dan penyusunan product backlog
* Minggu ke-2: Sprint Planning dan perancangan awal sistem
* Minggu ke-3 sampai ke-4: Sprint 1 — pengembangan fitur registrasi, login, dan manajemen pengguna
* Minggu ke-5 sampai ke-6: Sprint 2 — pengembangan fitur pengelolaan kursus dan modul pembelajaran
* Minggu ke-7 sampai ke-8: Sprint 3 — pengembangan fitur video pembelajaran dan akses materi
* Minggu ke-9: Sprint 4 — pengembangan fitur evaluasi pembelajaran dan penyempurnaan sistem
* Minggu ke-10: Final testing, release, dan deployment sistem

## 10. Kata-kata yang perlu diganti

Beberapa istilah Waterfall di naskah perlu diganti supaya konsisten:

| Istilah Waterfall                                           | Ganti menjadi Agile                                              |
| ----------------------------------------------------------- | ---------------------------------------------------------------- |
| Tahapan dilakukan secara berurutan                          | Tahapan dilakukan secara iteratif                                |
| Requirement Analysis                                        | Product Backlog                                                  |
| System Design                                               | Sprint Planning dan Perancangan Sistem                           |
| Implementation                                              | Sprint Development                                               |
| Testing                                                     | Sprint Review dan Testing                                        |
| Deployment                                                  | Release / Deployment                                             |
| Tahap berikutnya dilakukan setelah tahap sebelumnya selesai | Setiap sprint menghasilkan fitur yang dapat diuji dan dievaluasi |
| Kebutuhan sudah jelas sejak awal                            | Kebutuhan dapat disesuaikan berdasarkan umpan balik pengguna     |

Intinya, **struktur bab masih bisa dipakai**, tetapi narasinya harus berubah dari **linear/berurutan** menjadi **iteratif/berulang melalui sprint**. Bagian teknologi, kebutuhan sistem, database, diagram, MVC, dan Black Box Testing masih bisa dipertahankan, hanya cara penjelasannya yang perlu disesuaikan dengan Agile.
