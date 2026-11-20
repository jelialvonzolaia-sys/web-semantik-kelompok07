# Analisis Progress Fitur Search Engine — Rumah Adat Sumatera Utara

> **Tanggal Audit:** 20 November 2026  
> **Lokasi Proyek:** `tubes_rumah_adat/` (Repository `web-semantik-kelompok07`)  
> **Branch / Commit Terakhir:** `main` (`ddd2235` — *Merge pull request #47 from jelialvonzolaia-sys/feat/frontend*)

---

## 1. Ringkasan Eksekutif

Berdasarkan pemeriksaan langsung terhadap seluruh struktur proyek `tubes_rumah_adat` beserta riwayat *commit* di repository, fitur **Search Engine Rumah Adat Sumatera Utara berbasis Semantic Web** saat ini berada pada **Tahap Awal (Frontend Landing Page & Entry Point UI)**.

Secara ringkas:
- **Yang sudah berjalan:** Pondasi proyek Laravel 13 + Tailwind CSS v4 + Vite sudah siap, serta antarmuka halaman beranda (`/`), navigasi, *search bar* (`GET /search?q=...`), dan *popular search chips* sudah terimplementasi dengan desain *heritage*.
- **Yang belum ada:** Rute `/search`, *controller*, halaman hasil pencarian, halaman detail, dataset/ontologi RDF rumah adat (`.ttl`/`.owl`), serta mekanisme *query* SPARQL / koneksi *triplestore* belum dibuat. Jika pengguna menekan tombol **"Jelajahi Sekarang"**, menu **"Jelajahi"**, atau tautan kata kunci populer saat ini, aplikasi akan menghasilkan **404 Not Found**.

---

## 2. Peta Tahapan Progress Fitur Search Engine

| Tahap | Nama Tahapan | Status | Kondisi Saat Ini di Codebase |
| :--- | :--- | :---: | :--- |
| **Tahap 1** | Setup Proyek & Toolchain | **Selesai** | Laravel `13.43.0` (`^13.17`), PHP `8.4.12`, Vite `^8.0.0`, Tailwind CSS `^4.0.0`, SQLite + 3 migrasi bawaan sudah terpasang dan asset frontend sudah pernah di-*build* (`public/build/manifest.json`). |
| **Tahap 2** | UI Entry Point (Beranda & Search Form) | **Selesai (Parsial di Scope UI)** | Layout `resources/views/layouts/app.blade.php` dan halaman `resources/views/pages/home.blade.php` sudah memiliki *search bar* (`name="q"`), tautan `/search`, *quick keywords* (`Batak Toba`, `Karo`, `Simalungun`), dan 3 kartu informasi fitur. Halaman hasil & detail belum ada. |
| **Tahap 3** | Perancangan Ontologi & Dataset RDF/OWL | **Belum Dimulai** | Belum ada file `.ttl`, `.owl`, `.rdf`, atau `.rq` bertema rumah adat Sumatera Utara di dalam `tubes_rumah_adat/` (file RDF/SPARQL yang ada di `pertemuan-04` s.d. `pertemuan-12` masih berupa latihan materi kuliah). |
| **Tahap 4** | Integrasi Triplestore / SPARQL Engine & Backend Service | **Belum Dimulai** | `composer.json` belum memiliki *library* RDF/SPARQL (seperti `easyrdf/easyrdf`), `config/services.php` & `.env.example` belum memiliki konfigurasi *endpoint* SPARQL (mis. Apache Jena Fuseki / GraphDB / RDFLib), dan `app/Services/` belum ada. |
| **Tahap 5** | Routing, Controller, Halaman Hasil (`/search`), & Detail | **Belum Dimulai** | `routes/web.php` baru memiliki route `/` (`home`). `app/Http/Controllers/` baru berisi `Controller.php` kosong. Belum ada `SearchController` dan view hasil pencarian maupun detail rumah adat. |
| **Tahap 6** | Interaktivitas Lanjutan, Aset Konten, & Testing | **Belum Dimulai** | `resources/js/app.js` masih kosong (`//`), gambar di `public/images/rumah-adat/` baru berisi 1 foto (`hero-rumah-adat.jpeg`), dan folder `tests/` baru berisi `ExampleTest.php` bawaan Laravel. |

---

## 3. Audit Mendalam Komponen yang Sudah Ada

### 3.1. Riwayat Git Pengembangan `tubes_rumah_adat`
Dari histori git terkini, pengembangan `tubes_rumah_adat` baru melewati fase inisialisasi proyek dan *pull request* UI beranda pertama:
- `ada6e1d` — `set folder tubes`: inisialisasi *skeleton* aplikasi Laravel 13.
- `e83a744` — `folder tubes`: penamaan ulang direktori menjadi `tubes_rumah_adat`.
- `b283d12` — `feat: implement homepage UI with immersive heritage design`: pembuatan layout utama, halaman beranda, gambar hero, dan konfigurasi route `/` pada `routes/web.php`.
- `e1ad3d5` & `ddd2235` — sinkronisasi dan *merge* PR #47 (`feat/frontend`) ke `main`.

### 3.2. Infrastruktur & Dependensi
- **Backend (`composer.json`):** `php` `^8.3` (terpasang `8.4.12`), `laravel/framework` `^13.17` (`13.43.0`), `laravel/tinker` `^3.0`. Belum ada *package* pihak ketiga untuk parsing RDF maupun komunikasi SPARQL.
- **Frontend (`package.json` & `vite.config.js`):** Menggunakan Vite `^8.0.0`, Tailwind CSS `^4.0.0` (`@tailwindcss/vite`), `laravel-vite-plugin` `^3.1`, dan konfigurasi font `Instrument Sans` (bobot 400, 500, 600).
- **Database (`config/database.php` & `.env.example`):** Menggunakan `sqlite` (`database/database.sqlite`). Hasil `php artisan migrate:status` menunjukkan 3 tabel bawaan Laravel (`users`, `cache`, `jobs`) sudah berstatus `[1] Ran`.

### 3.3. Frontend & Entry Point Pencarian
1. **Layout Utama (`resources/views/layouts/app.blade.php`):**
   - Menggunakan Bahasa Indonesia (`<html lang="id">`) dan palet warna bertema budaya (`#FFF9F5`, `#192235`, `#FFA586`, `#B51A28`, `#561A2E`).
   - *Navbar* menyediakan tautan **Beranda** (`/`) dan **Jelajahi** (`/search`).
   - *Footer* mencantumkan identitas *"Rumah Adat Sumatera Utara. Eksplorasi Warisan Budaya Berbasis Semantic Web."*
2. **Halaman Beranda (`resources/views/pages/home.blade.php`):**
   - **Hero Section:** Menampilkan latar `public/images/rumah-adat/hero-rumah-adat.jpeg` dengan judul *"Jelajahi Warisan Rumah Adat Sumatera Utara"*.
   - **Form Search Engine (baris 48–92):** Sudah tersedia `<form action="/search" method="GET" role="search">` dengan `<input type="search" name="q" placeholder="Cari rumah adat, suku, atau wilayah...">` dan tombol submit **"Jelajahi Sekarang"**.
   - **Pencarian Populer (baris 95–110):** Menyediakan 3 *chip* tautan cepat (`Batak Toba`, `Karo`, `Simalungun`) yang mengarah ke `/search?q=...`.
   - **Kartu Informasi (baris 131–154):** Menampilkan 3 pilar fitur yang direncanakan (*Arsitektur Tradisional*, *Warisan Budaya*, dan *Pencarian Semantik*).
3. **Routing (`routes/web.php`):**
   - Baru mendefinisikan `GET /` dengan nama `home` yang mengembalikan `view('pages.home')`.
   - Hasil verifikasi `php artisan route:list` hanya menampilkan 4 route: `/`, `storage/{path}` (`GET|HEAD` dan `PUT`), serta `/up`.

---

## 4. Analisis Celah & Bagian yang Belum Dikerjakan

### 4.1. Alur Pencarian Terputus di `/search` (Blocker Fungsional)
- Mengakses `/search` baik lewat menu navbar **Jelajahi**, submit form pencarian, maupun klik tag pencarian populer akan berakhir **404 Not Found** karena route `/search` belum didaftarkan di `routes/web.php`.
- Belum ada *controller* (`app/Http/Controllers/` hanya berisi `Controller.php`), *form validation*, maupun Blade view untuk halaman hasil pencarian dan halaman detail entitas.

### 4.2. Belum Ada Layer Data & Mesin Semantic Web
Karena tujuan aplikasi ini adalah **pencarian berbasis semantik**, komponen inti berikut masih kosong seluruhnya di `tubes_rumah_adat`:
- **Ontologi & Dataset RDF:** Belum ada skema ontologi (kelas, *object property*, *data property*, hierarki `rdfs:subClassOf` / aksioma OWL) maupun data individu rumah adat Sumatera Utara (misalnya *Ruma Bolon*, *Siwaluh Jabu*, *Omo Hada* / *Omo Sebua*, *Bagas Godang*, *Rumah Bolon Simalungun*, dll.).
- **Triplestore / Query Engine:** Belum ada keputusan arsitektur maupun implementasi koneksi ke *triplestore* (mis. Apache Jena Fuseki, GraphDB, Oxigraph) atau pemrosesan graf lokal (mis. *service* Python `rdflib` / *package* PHP `easyrdf/easyrdf`).
- **Logika Query Semantik:** Belum ada query SPARQL (`SELECT`, `FILTER`, `OPTIONAL`, *property path*, maupun dukungan hasil inferensi/materialisasi RDFS/OWL) untuk menjawab pencarian lintas relasi seperti:
  - Pencarian berdasarkan nama rumah adat, suku/etnis, kabupaten/wilayah asal;
  - Pencarian berdasarkan komponen arsitektur, bahan bangunan, fungsi adat, atau nilai filosofis;
  - Pencarian terhubung (*related entities* / relasi semantik antar suku, wilayah, dan gaya arsitektur).

### 4.3. Aset Visual, Interaktivitas JS, & Testing
- **JavaScript (`resources/js/app.js`):** Masih kosong (`//`). Fitur seperti *autocomplete* saran kata kunci, filter dinamis, atau visualisasi graf relasi pengetahuan (*knowledge graph*) belum dibuat.
- **Aset Gambar (`public/images/rumah-adat/`):** Baru tersedia 1 gambar (`hero-rumah-adat.jpeg`). Belum ada gambar untuk masing-masing rumah adat di hasil pencarian dan halaman detail.
- **Automated Tests (`tests/`):** Baru berisi `ExampleTest.php` bawaan Laravel.

### 4.4. Catatan Teknis Kondisi Environment Lokal Saat Audit
Selama pemeriksaan workspace ditemukan beberapa hal teknis yang perlu diperhatikan tim:
1. **`public/hot` masih tertinggal:** File `public/hot` (di-ignore git) berisi `http://[::1]:5173`. Bila server Laravel dijalankan tanpa `npm run dev` aktif, Laravel akan mencoba memuat CSS/JS dari `http://[::1]:5173` alih-alih aset hasil *build* di `public/build/`. Hapus `public/hot` saat menjalankan aplikasi memakai hasil `npm run build`.
2. **Perubahan lokal pada `package-lock.json`:** `git status` mencatat `package-lock.json` berubah di baris `"name": "tubes-rumah-adat"` menjadi `"tubes_rumah_adat"`. Sebaiknya dirapikan/di-commit atau di-restore agar tidak mengganggu PR berikutnya.
3. **Eksekusi PHPUnit (`php artisan test`):** Saat diuji pada environment saat ini, PHPUnit gagal membuka `vendor/autoload.php` (`Cannot open bootstrap script ".../vendor/autoload.php"`, dengan `is_readable('vendor/autoload.php') === false`). Tim perlu memastikan izin baca folder `vendor/` atau menjalankan ulang `composer install` pada lingkungan pengembangan masing-masing sebelum menulis dan menjalankan *test suite*.
4. **File bawaan `resources/views/welcome.blade.php`:** Masih tersisa di folder views meskipun sudah digantikan oleh `pages/home.blade.php`.

---

## 5. Roadmap Langkah Selanjutnya (Prioritas Pengerjaan)

Agar fitur *search engine* dapat segera berjalan end-to-end sesuai konsep Web Semantik, berikut urutan pengerjaan yang disarankan:

### Fase A — Ontologi & Knowledge Graph (Fondasi Semantik)
1. **Rancang Ontologi Domain Rumah Adat Sumatera Utara (`.ttl` / `.owl`):**
   - Definisikan *Class* utama (misal: `RumahAdat`, `Suku`, `Wilayah`, `KomponenArsitektur`, `Material`, `FilosofiBudaya`, `FungsiSosial`).
   - Definisikan *Object Properties* dan *Data Properties* (misal: `berasalDariSuku`, `terletakDi`, `memilikiKomponen`, `menggunakanMaterial`, `memilikiFilosofi`, `namaRumah`, `deskripsi`, `gambarUrl`).
   - Terapkan relasi hierarki (`rdfs:subClassOf` / OWL RL) serta pisahkan graf *asserted* dan *inferred* (sejalan dengan praktik materi `pertemuan-11` dan `pertemuan-12`).
2. **Isi Dataset Awal (RDF Turtle):**
   - Kumpulkan data terverifikasi untuk rumah adat utama di Sumatera Utara (Batak Toba, Karo, Simalungun, Pakpak, Angkola/Mandailing, Nias, Melayu Deli, dll.).
3. **Siapkan Eksekutor SPARQL:**
   - Pilih salah satu mekanisme yang paling stabil untuk tim:
     - **Opsi 1 (Direkomendasikan untuk Web Semantik):** Jalankan **Apache Jena Fuseki** (atau GraphDB) lokal/Docker lalu akses endpoint SPARQL-nya dari Laravel menggunakan `Illuminate\Support\Facades\Http` atau `easyrdf/easyrdf`.
     - **Opsi 2 (Ringan tanpa server Java terpisah):** Gunakan CLI/service Python `rdflib` (yang sudah familier digunakan tim di `pertemuan-11` & `pertemuan-12`) atau *library* PHP RDF yang memuat file `.ttl` ter-materialisasi.

### Fase B — Backend Search Engine (Laravel)
4. **Konfigurasi & Service Layer:**
   - Tambahkan konfigurasi *endpoint* SPARQL di `.env.example` dan `config/services.php`.
   - Buat `app/Services/SparqlClientService.php` dan `app/Services/RumahAdatSearchService.php` untuk membangun query SPARQL secara aman (sanitasi input `q` agar terhindar dari *SPARQL injection*), mendukung pencocokan kata kunci (`REGEX`/`CONTAINS` case-insensitive pada `rdfs:label`, deskripsi, suku, wilayah, arsitektur) serta filter faset.
5. **Routing & Controller:**
   - Daftarkan `GET /search` (dan `GET /rumah-adat/{slug}` untuk detail) pada `routes/web.php`.
   - Buat `SearchController` dengan validasi query parameter (`q`, `suku`, `wilayah`, `kategori`, dll.).

### Fase C — Frontend Hasil Pencarian & Detail
6. **Halaman Hasil Pencarian (`resources/views/pages/search.blade.php`):**
   - Pertahankan *search bar* aktif dengan nilai `request('q')`.
   - Tambahkan sidebar/bar filter semantik (filter berdasarkan Suku, Wilayah, Elemen Arsitektur), jumlah hasil, *card* hasil pencarian, serta tampilan *empty state* jika tidak ada entitas yang cocok.
7. **Halaman Detail Rumah Adat (`resources/views/pages/detail.blade.php`):**
   - Tampilkan informasi lengkap rumah adat beserta **keterkaitan semantiknya** (relasi dengan suku, wilayah, material, filosofi, serta rekomendasi rumah adat lain yang memiliki relasi serupa di *knowledge graph*).
   - Lengkapi foto tiap rumah adat di `public/images/rumah-adat/`.

### Fase D — Nilai Tambah & Pengujian
8. **Fitur Interaktif & Verifikasi:**
   - Tambahkan *autocomplete / live suggestion* atau visualisasi mini *knowledge graph* pada `resources/js/app.js`.
   - Buat *Feature Tests* di `tests/Feature/` untuk memastikan route `/`, `/search?q=...`, filter semantik, dan halaman detail berjalan dengan status HTTP `200` serta mengembalikan data yang tepat.
