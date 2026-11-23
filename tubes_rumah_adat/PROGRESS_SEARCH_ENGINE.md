# Analisis Progress Fitur Search Engine — Rumah Adat Sumatera Utara

> **Tanggal Audit:** 23 November 2026  
> **Lokasi Proyek:** `tubes_rumah_adat/` (Repository `web-semantik-kelompok07`)  
> **Branch / Commit Terakhir:** `main` (`1f8bc44` — *perbaiki turtle*)

---

## 1. Ringkasan Eksekutif

Berdasarkan pemeriksaan langsung terhadap struktur proyek `tubes_rumah_adat/` hingga commit `1f8bc44`, pengembangan **Search Engine Rumah Adat Sumatera Utara berbasis Semantic Web** telah menyelesaikan **lapisan fondasi aplikasi Laravel, UI awal beranda, serta seluruh lapisan Ontologi & Dataset RDF (`ontology/rumah_adat.ttl`)**.

Secara ringkas:
- **Yang sudah selesai:**
  1. Fondasi proyek **Laravel 13 + Tailwind CSS v4 + Vite** dan migrasi database bawaan SQLite.
  2. Antarmuka halaman **Beranda (`/`)**, layout utama, *search bar* (`GET /search?q=...`), dan 3 *quick search chips*.
  3. **Ontologi & Knowledge Graph RDF lengkap (`ontology/rumah_adat.ttl`)** berisi **3.168 triples**, **7 kelas inti**, **11 ObjectProperty** (termasuk `owl:TransitiveProperty` dan `owl:SymmetricProperty`), **9 DatatypeProperty**, serta **267 entitas** yang seluruhnya valid sintaks (`0` error di `dataset/cek_ttl.py`).
- **Yang belum dikerjakan:**
  1. Setup **Apache Jena Fuseki** dan kumpulan file **query SPARQL (`.rq`)** (pencarian *multi-hop*, filter kategori, *match path* alasan keterkaitan, detail, statistik, dan graf).
  2. **Integrasi Backend Laravel** (konfigurasi endpoint Fuseki, `SparqlService`, Controller, serta route `/search`, `/rumah-adat/{slug}`, `/graph`, `/api/graph`). Saat ini mengakses `/search` masih menghasilkan **404 Not Found**.
  3. **Halaman UI Hasil Pencarian, Detail, & Knowledge Graph** (statistik dinamis & *quick explore* wilayah di Beranda, badge alasan keterkaitan di `/search`, panel relasi semantik di `/detail`, visualisasi `Vis.js`/`Cytoscape.js` di `/graph`, foto lokal rumah adat, serta pengujian QA/Feature Test).

---

## 2. Peta Tahapan Progress Fitur Search Engine

| Tahap | Nama Tahapan | Status | Kondisi Saat Ini di Codebase |
| :--- | :--- | :---: | :--- |
| **Tahap 1** | Setup Proyek & Toolchain | **Selesai** | Laravel `13.43.0` (`^13.17`), PHP `8.4.12`, Vite `^8.0.0`, Tailwind CSS `^4.0.0`, SQLite + 3 migrasi bawaan, serta Python `3.13.0` + `rdflib 7.6.0` (`dataset/cek_ttl.py`) sudah siap. |
| **Tahap 2** | UI Entry Point (Beranda & Search Form) | **Selesai (Parsial di Scope UI)** | Layout `resources/views/layouts/app.blade.php` dan `resources/views/pages/home.blade.php` sudah memiliki *search bar* (`name="q"`), tautan `/search`, 3 *quick keywords* (`Batak Toba`, `Karo`, `Simalungun`), dan 3 kartu info. Statistik dinamis & *quick explore* wilayah belum ditambahkan. |
| **Tahap 3** | Perancangan Ontologi & Dataset RDF/OWL | **Selesai** | `ontology/rumah_adat.ttl` sudah final & tervalidasi (`3.168 triples`, `42 RumahAdat`, `15 Suku`, `107 Marga`, `13 BahasaDaerah`, `61 Wilayah`, `17 Tradisi`, `13 Ornamen`, `0` error domain/range/sintaks). |
| **Tahap 4** | Setup Triplestore Fuseki & Query SPARQL (`.rq`) | **Belum Dimulai** | Belum ada file `.rq` maupun konfigurasi Fuseki di repo. **Siap langsung dikerjakan** menggunakan `ontology/rumah_adat.ttl`. |
| **Tahap 5** | Backend Laravel Integration (Service, Controller, Route) | **Belum Dimulai** | `routes/web.php` baru punya `/`. `app/Http/Controllers/` baru berisi `Controller.php`, belum ada `app/Services/` atau konfigurasi Fuseki di `.env.example` & `config/services.php`. |
| **Tahap 6** | UI Hasil, Detail, Knowledge Graph, Aset, & QA/Tests | **Belum Dimulai** | Belum ada `search.blade.php`, `detail.blade.php`, `graph.blade.php`, script di `resources/js/app.js`, foto koleksi rumah adat di `public/images/rumah-adat/`, serta automated test/QA checklist. |

---

## 3. Audit Komponen yang Sudah Selesai

### 3.1. Riwayat Git Pengembangan `tubes_rumah_adat`
- `ada6e1d` & `e83a744` — Inisialisasi skeleton Laravel 13 di `tubes_rumah_adat/`.
- `b283d12` / `ddd2235` (PR #47) — Implementasi UI beranda awal & layout heritage.
- `fb0d720` — Pembuatan dokumentasi progress awal (`PROGRESS_SEARCH_ENGINE.md`).
- `bc5497b` (PR #48), `713e8ef`, `5406c3b`, `c3d4edf` / `a8ba3a5` (PR #49) — Penyusunan ontologi, dataset RDF rumah adat, skrip validasi `dataset/cek_ttl.py`, dan panduan `URUTAN_PENGERJAAN.md`.
- `1f8bc44` — Penyempurnaan penuh `ontology/rumah_adat.ttl` (penghapusan `ra:linkFoto` dan entri placeholder *"Status Inventarisasi"*, penambahan 6 rumah adat wilayah Timur/Selatan + tradisi baru termasuk `Maena`, penguatan aksioma OWL, penyeragaman `rdfs:label`, serta pembersihan komentar).

### 3.2. Infrastruktur & Dependensi
- **Backend (`composer.json`):** `php` `^8.3` (terpasang `8.4.12`), `laravel/framework` `^13.17` (`13.43.0`), `laravel/tinker` `^3.0`. Komunikasi dengan Apache Jena Fuseki nantinya dapat menggunakan HTTP Client bawaan Laravel (`Illuminate\Support\Facades\Http`) tanpa perlu library eksternal tambahan, atau menambah paket RDF jika diperlukan.
- **Frontend (`package.json` & `vite.config.js`):** Vite `^8.0.0`, Tailwind CSS `^4.0.0` (`@tailwindcss/vite`), `laravel-vite-plugin` `^3.1`, font `Instrument Sans`, dan asset build tersedia di `public/build/`.
- **Validator Python (`dataset/cek_ttl.py`):** Menggunakan Python `3.13.0` + `rdflib 7.6.0` untuk mengecek sintaks Turtle, jumlah entitas di 7 kelas, serta memastikan seluruh target relasi `ObjectProperty` memiliki deklarasi `rdf:type`.

### 3.3. Frontend & Entry Point Pencarian
1. **Layout Utama (`resources/views/layouts/app.blade.php`):**
   - Navbar menyediakan tautan **Beranda** (`/`) dan **Jelajahi** (`/search`), dengan palet warna budaya (`#FFF9F5`, `#192235`, `#FFA586`, `#B51A28`, `#561A2E`).
2. **Halaman Beranda (`resources/views/pages/home.blade.php`):**
   - Menampilkan hero image `public/images/rumah-adat/hero-rumah-adat.jpeg`, form pencarian `<form action="/search" method="GET" role="search">` (`name="q"`), 3 *chip* pencarian populer (`Batak Toba`, `Karo`, `Simalungun`), dan 3 kartu penjelasan fitur.
3. **Routing (`routes/web.php`):**
   - Baru terdaftar `GET /` (`home`), `storage/{path}`, dan `/up`.

### 3.4. Ontologi & Knowledge Graph RDF (`ontology/rumah_adat.ttl`)
File utama `ontology/rumah_adat.ttl` (`@prefix ra: <http://example.org/ontology/rumah-adat#> .`) sudah tervalidasi dengan hasil:
- **Total Triples:** `3.168 triples`, `0` error sintaks, `0` pelanggaran `rdfs:domain`/`rdfs:range`.
- **7 Kelas Inti (`owl:Class`) & Jumlah Entitas (100% memiliki `ra:nama` dan `rdfs:label`):**
  - `ra:RumahAdat`: **42** entitas (seluruhnya bangunan/kompleks nyata, tidak ada lagi placeholder *"Status Inventarisasi"*).
  - `ra:Suku`: **15** entitas (terhubung simetris melalui `ra:berkerabatDenganSuku` = 44 relasi).
  - `ra:Marga`: **107** entitas (terhubung simetris melalui `ra:berkerabatDenganMarga` = 764 relasi, plus `ra:bagianDariSuku` & `ra:berasalDariWilayah`).
  - `ra:BahasaDaerah`: **13** entitas.
  - `ra:Wilayah`: **61** entitas (1 provinsi + 60 kabupaten/kota/desa, 0 wilayah daun yang menganggur).
  - `ra:Tradisi`: **17** entitas (termasuk `Maena`, `Owasa`, `Hombo Batu`, `Merdang Merdem`, `Merkottas`, `Jamu Laut`, `Rondang Bintang`, `Manortor`, `Tepung Tawar`, dll.).
  - `ra:Ornamen`: **13** entitas.
- **11 Object Properties:**
  - `ra:berasalDariWilayah` (`owl:ObjectProperty`, range `ra:Wilayah`)
  - `ra:bagianDariWilayah` (`owl:ObjectProperty, owl:TransitiveProperty`, `Wilayah -> Wilayah`)
  - `ra:dimilikiOlehSuku` (`RumahAdat -> Suku`)
  - `ra:memilikiMarga` (`RumahAdat -> Marga`)
  - `ra:menggunakanBahasa` (`RumahAdat -> BahasaDaerah`)
  - `ra:memilikiTradisi` (`RumahAdat -> Tradisi`)
  - `ra:memilikiOrnamen` (`RumahAdat -> Ornamen`)
  - `ra:menuturkanBahasa` (`Suku -> BahasaDaerah`)
  - `ra:bagianDariSuku` (`Marga -> Suku`)
  - `ra:berkerabatDenganMarga` (`owl:ObjectProperty, owl:SymmetricProperty`, `Marga -> Marga`)
  - `ra:berkerabatDenganSuku` (`owl:ObjectProperty, owl:SymmetricProperty`, `Suku -> Suku`)
- **9 Datatype Properties (`ra:RumahAdat` / `owl:Thing`):**
  `ra:nama`, `ra:namaAlternatif`, `ra:sejarah`, `ra:fungsi`, `ra:ciriArsitektur`, `ra:bentukAtap`, `ra:bahanBangunan`, `ra:maknaFilosofis`, `ra:referensi`.  
  *(Catatan: properti `ra:linkFoto` sengaja dihapus dari TBox & ABox karena tautan sebelumnya berupa halaman HTML; gambar rumah adat untuk tampilan web disiapkan melalui aset lokal Laravel).*

---

## 4. Analisis Celah & Bagian yang Belum Dikerjakan

### 4.1. Layer Query Semantik & Triplestore (Prioritas Berikutnya)
- Belum ada instance/dokumentasi **Apache Jena Fuseki** yang terhubung ke proyek.
- Belum ada file **query SPARQL (`.rq`)** untuk statistik dataset, pencarian kata kunci *multi-hop* (`ra:dimilikiOlehSuku`, `ra:memilikiMarga`, `ra:berkerabatDenganMarga`, `ra:berkerabatDenganSuku`, `ra:bagianDariWilayah+`, `ra:menggunakanBahasa`, `ra:memilikiTradisi`, `ra:memilikiOrnamen`), filter kategori, pelacakan alasan keterkaitan (*match path*), detail entitas, maupun data node/edge graf.

### 4.2. Backend Routing, Service, & Controller Laravel
- Route `/search` masih **404 Not Found** (`routes/web.php` baru berisi `/`).
- Belum ada konfigurasi `FUSEKI_URL` / `FUSEKI_DATASET` di `.env.example` dan `config/services.php`.
- Belum ada `SparqlService` / `RumahAdatSearchService` di `app/Services/` maupun `HomeController`, `SearchController`, `DetailController`, dan `GraphController`.

### 4.3. Halaman Hasil, Detail, Visualisasi Knowledge Graph, Aset Gambar, & Testing
- Belum ada view `resources/views/pages/search.blade.php`, `detail.blade.php`, dan `graph.blade.php`.
- Di `resources/views/pages/home.blade.php`, bagian **Statistik Dataset** dinamis dan **Quick Explore Wilayah** (di luar 3 *chip* suku) belum tersedia.
- `resources/js/app.js` masih kosong (`//`); integrasi **Vis.js / Cytoscape.js** belum dibuat.
- Folder `public/images/rumah-adat/` baru berisi `hero-rumah-adat.jpeg` (foto per rumah adat belum ditambahkan).
- Folder `tests/` baru berisi `ExampleTest.php` bawaan Laravel (belum ada feature test maupun dokumen `QA_CHECKLIST.md`).

### 4.4. Catatan Teknis Kebersihan Repository
1. **Duplikat file TTL lama (`dataset/rumah_adat.ttl`):** Di folder `dataset/` masih terdapat `dataset/rumah_adat.ttl` dari commit `c3d4edf` (`2.739 triples`), sedangkan file terbaru yang sudah disempurnakan dan divalidasi oleh `dataset/cek_ttl.py` adalah **`ontology/rumah_adat.ttl` (`3.168 triples`)**. Sebaiknya `dataset/rumah_adat.ttl` dihapus agar anggota tim tidak keliru meng-upload file lama ke Fuseki.
2. **Eksekusi `php artisan test` di environment lokal:** Pastikan izin akses folder `vendor/` dan konfigurasi `composer install` berjalan normal di masing-masing perangkat sebelum menjalankan test suite Laravel.
3. **File sisa bawaan:** `resources/views/welcome.blade.php` masih ada di `resources/views/` meski tidak dipakai oleh route mana pun.

---

## 5. Roadmap Langkah Selanjutnya

### Fase A — Ontologi & Knowledge Graph (Fondasi Semantik)
1. ~~Rancang Ontologi & Validasi `ontology/rumah_adat.ttl`~~ — **Selesai (`✅`, `3.168 triples`).**
2. **Setup Apache Jena Fuseki & Kumpulan Query SPARQL (`.rq`):**
   - Import **`ontology/rumah_adat.ttl`** ke dataset Fuseki (misal dataset `rumah_adat`).
   - Buat & uji file `.rq` di `resources/sparql/` (statistik, pencarian *multi-hop* + *match path*, filter kategori, detail rumah adat, serta graf node/edge).

### Fase B — Backend Search Engine (Laravel)
3. **Konfigurasi & Service Layer:**
   - Daftarkan `FUSEKI_URL` dan `FUSEKI_DATASET` di `.env.example` dan `config/services.php`.
   - Buat `app/Services/SparqlService.php` & `app/Services/RumahAdatSearchService.php` (pembaca `.rq`, sanitasi input SPARQL, HTTP POST ke Fuseki, parser JSON hasil).
4. **Routing & Controller:**
   - Tambahkan route `/`, `/search`, `/rumah-adat/{slug}`, `/graph`, dan `/api/graph` di `routes/web.php` beserta controllernya.

### Fase C — Frontend Hasil Pencarian, Detail, & Knowledge Graph
5. **Penyempurnaan Beranda, Halaman Hasil (`/search`), & Halaman Detail (`/rumah-adat/{slug}`):**
   - Tampilkan statistik asli (`42` Rumah Adat, `15` Suku, `61` Wilayah, dll.), *quick explore* wilayah, kartu hasil + **badge alasan keterkaitan**, panel relasi semantik yang dapat diklik, serta aset foto lokal di `public/images/rumah-adat/`.
6. **Halaman Visualisasi Knowledge Graph (`/graph`):**
   - Implementasi graf dinamis memakai **Vis.js** atau **Cytoscape.js** dari endpoint `/api/graph`.

### Fase D — Pengujian Menyeluruh & Persiapan Demo
7. **QA Checklist & Feature Tests:**
   - Uji seluruh skenario pencarian tidak langsung (*multi-hop*: via marga, kerabat marga, tradisi seperti `Maena`/`Hombo Batu`, ornamen, bahasa, suku berkerabat, wilayah bertingkat), uji link/gambar, dan buat feature test di `tests/Feature/`.
