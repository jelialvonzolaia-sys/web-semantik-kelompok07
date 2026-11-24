# Analisis Progress Fitur Search Engine — Rumah Adat Sumatera Utara

> **Tanggal Audit:** 24 November 2026  
> **Lokasi Proyek:** `tubes_rumah_adat/` (Repository `web-semantik-kelompok07`)  
> **Branch / Commit Terakhir:** `main` (`63c0ad1` — *Merge pull request #52 from jelialvonzolaia-sys/feat/detail-page-enhancement*)

---

## 1. Ringkasan Eksekutif

Berdasarkan pemeriksaan langsung terhadap codebase `tubes_rumah_adat/` hingga commit `63c0ad1`, progres proyek sudah melangkah jauh ke fase **Prototype End-to-End Frontend + Controller (masih berbasis 3 data dummy) & Ontologi RDF Final (`3.168 triples`)**:

- **Yang sudah selesai / berjalan sekarang:**
  1. **Fondasi Aplikasi:** Laravel `13.43.0` + Tailwind CSS v4 + Vite 8 + validator Python `rdflib` (`dataset/cek_ttl.py`).
  2. **Ontologi & Dataset RDF (`ontology/rumah_adat.ttl`):** Sudah final (`3.168 triples`, `42 RumahAdat`, `15 Suku`, `107 Marga`, `13 BahasaDaerah`, `61 Wilayah`, `17 Tradisi`, `13 Ornamen`, `owl:TransitiveProperty`, `owl:SymmetricProperty`, 100% `rdfs:label` & `ra:nama`, `0` error).
  3. **Routing & Controller Awal (`routes/web.php` & `RumahAdatController.php`):** Alur `Beranda (/)` → `Hasil Pencarian (/search)` → `Detail (/detail/{slug})` sudah **berjalan tanpa 404** menggunakan `RumahAdatController` (`index` & `show`), lengkap dengan filter `q`, `suku[]`, dan `wilayah[]` pada **3 data demonstrasi statis**.
  4. **Antarmuka UI Utama (`home.blade.php`, `search.blade.php`, `detail.blade.php`):** Seluruh halaman utama sudah dibangun dengan tema *Modern Cultural Heritage* (hero search bar, statistik dataset, quick explore wilayah, sidebar filter pencarian, kartu hasil, empty state, profil detail, serta prototype diagram 3 node).
- **Yang belum dikerjakan (Jembatan Semantik & Visualisasi Dinamis):**
  1. **Apache Jena Fuseki & Query SPARQL (`.rq`):** Belum ada instance/dokumentasi Fuseki maupun file query `.rq` di repository.
  2. **Integrasi Backend Laravel ↔ Fuseki:** `RumahAdatController` masih memakai 3 data dummy hardcoded (`getHouses()`), belum terhubung ke Fuseki/SPARQL, belum ada `SparqlService`, belum ada `FUSEKI_URL`/`FUSEKI_DATASET` di `.env.example` dan `config/services.php`, serta belum ada route `/graph` dan `/api/graph`.
  3. **Penyambungan UI ke Data Ontologi Asli + Fitur Semantik:** Belum ada *badge alasan keterkaitan (match path)* dinamis di `search.blade.php`, atribut lengkap ontologi (`sejarah`, `fungsi`, `ciriArsitektur`, `bentukAtap`, `bahanBangunan`, `maknaFilosofis`, `referensi`, marga, bahasa, ornamen, tradisi) di `detail.blade.php`, foto koleksi rumah adat di `public/images/rumah-adat/`, halaman Knowledge Graph interaktif (`Vis.js`/`Cytoscape.js`), serta pengujian QA/Feature Test.

---

## 2. Peta Tahapan Progress Fitur Search Engine

| Tahap | Nama Tahapan | Status | Kondisi Saat Ini di Codebase |
| :--- | :--- | :---: | :--- |
| **Tahap 1** | Setup Proyek & Toolchain | **Selesai** | Laravel `13.43.0`, PHP `8.4.12`, Vite `^8.0.0`, Tailwind CSS `^4.0.0`, SQLite + 3 migrasi bawaan, serta Python `3.13.0` + `rdflib 7.6.0` (`dataset/cek_ttl.py`). |
| **Tahap 2** | Ontologi & Dataset RDF/OWL | **Selesai** | `ontology/rumah_adat.ttl` final & valid (`3.168 triples`, `42 RumahAdat`, `15 Suku`, `107 Marga`, `13 BahasaDaerah`, `61 Wilayah`, `17 Tradisi`, `13 Ornamen`, `0` error sintaks/domain/range). |
| **Tahap 3** | UI/UX Halaman Beranda, Search, & Detail + Controller Prototype | **Selesai (Prototype Dummy)** | `routes/web.php` (`/`, `/search`, `/detail/{slug}`), `RumahAdatController.php`, `home.blade.php`, `search.blade.php`, dan `detail.blade.php` sudah berjalan mulus memakai 3 data dummy (`Rumah Bolon`, `Siwaluh Jabu`, `Rumah Bolon Simalungun`). |
| **Tahap 4** | Setup Triplestore Fuseki & Query SPARQL (`.rq`) | **Belum Dimulai** | Belum ada file `.rq` maupun konfigurasi Fuseki di repo. **Prioritas utama berikutnya.** |
| **Tahap 5** | Integrasi Backend Laravel ↔ Fuseki + Penyambungan UI Data Asli | **Belum Dimulai** | Belum ada `SparqlService`, config Fuseki, mapping 42 data asli, *badge alasan keterkaitan (match path)*, detail relasi lengkap, maupun foto rumah adat per entitas. |
| **Tahap 6** | Visualisasi Knowledge Graph Interaktif (`Vis.js`/`Cytoscape.js`) & QA/Tests | **Belum Dimulai** | Diagram di `detail.blade.php` masih prototype statis 3 node; belum ada halaman `/graph`, `/api/graph`, script di `resources/js/app.js`, maupun `QA_CHECKLIST.md` & feature test. |

---

## 3. Audit Komponen yang Sudah Selesai

### 3.1. Riwayat Git Terbaru (`tubes_rumah_adat`)
- `ada6e1d` & `e83a744` — Inisialisasi skeleton Laravel 13.
- `b283d12` / `ddd2235` (PR #47) — Implementasi hero beranda & layout heritage awal.
- `fb0d720` — Dokumentasi progress awal (`PROGRESS_SEARCH_ENGINE.md`).
- `bc5497b` (PR #48), `713e8ef`, `5406c3b`, `a8ba3a5` (PR #49), `1f8bc44` — Penyusunan, validasi (`dataset/cek_ttl.py`), dan penyempurnaan final `ontology/rumah_adat.ttl` (`3.168 triples`).
- `07816cc` — Pembaruan dokumen `PROGRESS_SEARCH_ENGINE.md` & `URUTAN_PENGERJAAN.md`.
- `54b50f6` / `e9d5186` (PR #50) — Implementasi `RumahAdatController.php`, route `/search` & `/detail/{slug}`, `search.blade.php`, dan `detail.blade.php` awal.
- `d2df116` / `192d79e` (PR #51) — Penyempurnaan `home.blade.php` (Dataset Statistics, Quick Explore Wilayah, Cultural Discovery, CTA).
- `f47b19a` / `63c0ad1` (PR #52) — Penyempurnaan `detail.blade.php` (Cultural Identity, Cultural Overview, Semantic Data sidebar, Prototype Knowledge Graph 3 node).

### 3.2. Routing, Controller, & Alur Web (`routes/web.php`, `RumahAdatController.php`)
- **`routes/web.php`:**
  - `GET /` → `home` (`pages.home`)
  - `GET /search` → `search` (`RumahAdatController@index`)
  - `GET /detail/{slug}` → `detail` (`RumahAdatController@show`)
  - *(Hasil `php artisan route:list` menunjukkan 6 route termasuk `storage/{path}` dan `/up`).*
- **`app/Http/Controllers/RumahAdatController.php` (`119` baris):**
  - `getHouses()` masih mengembalikan **3 data dummy** (`rumah-bolon`, `siwaluh-jabu`, `rumah-bolon-simalungun`) dengan field `slug`, `name`, `tribe`, `region`, `description`, `tag`.
  - `index()` memfilter koleksi menggunakan parameter `q` (pencarian substring pada `name`, `tribe`, `region`, `description`), `suku[]`, dan `wilayah[]`.
  - `show($slug)` mencari item berdasarkan `slug` dan merender `pages.detail` (atau 404 jika tidak ditemukan).

### 3.3. Antarmuka Frontend (`resources/views/`)
1. **Layout (`layouts/app.blade.php`, `54` baris):** Navbar dengan tautan `route('home')` dan `route('search')`. *(Belum ada tautan menu untuk `/graph`).*
2. **Beranda (`pages/home.blade.php`, `386` baris):**
   - **Hero Search Bar:** Form `GET {{ route('search') }}` (`name="q"`) + 3 *popular search chips* (`Batak Toba`, `Karo`, `Simalungun`).
   - **Dataset Statistics:** Menampilkan angka `42+ Rumah Adat`, `15+ Suku`, `61+ Wilayah`, `107+ Marga` *(masih array statis di Blade, namun angkanya sudah selaras dengan `ontology/rumah_adat.ttl`)*.
   - **Quick Explore Wilayah:** 3 kartu wilayah (`Samosir`, `Karo`, `Simalungun`) menuju `route('search', ['wilayah' => [...]])`.
   - **Cultural Discovery & CTA:** 3 kartu topik (`Arsitektur`, `Budaya`, `Rumah Adat`) dan tombol CTA ke `route('search')`.
3. **Hasil Pencarian (`pages/search.blade.php`, `354` baris):**
   - Hero pencarian + 5 *quick search* (`Rumah Bolon`, `Batak Toba`, `Karo`, `Simalungun`, `Nias`).
   - Sidebar filter checkbox **Suku** (`Batak Toba`, `Karo`, `Simalungun`, `Mandailing`, `Nias`) dan **Wilayah** (`Samosir`, `Karo`, `Simalungun`, `Toba`, `Dairi`, `Nias`).
   - Header hasil dengan label status **`DATA AWAL`**, daftar kartu rumah adat dengan chip statis (`Rumah Adat`, `$house['tribe']`, `Wilayah`), tombol `Lihat Detail →`, serta tampilan *empty state*.
4. **Halaman Detail (`pages/detail.blade.php`, `439` baris):**
   - Hero profil budaya dengan breadcrumb dan side card *Heritage Category*.
   - Card `01 — CULTURAL IDENTITY` (`name`, `tribe`, `region`, `tag`) dan `02 — CULTURAL OVERVIEW` (`description`).
   - Sidebar `03 — SEMANTIC DATA` (3 kartu relasi dasar) dan `04 — KNOWLEDGE GRAPH` (**Prototype Visualization** statis 3 node: Rumah Adat → Suku → Wilayah), ditambah `05 — INFORMATION NOTE` dan navigasi bawah.

### 3.4. Ontologi & Dataset RDF (`ontology/rumah_adat.ttl`)
- Tervalidasi `python dataset/cek_ttl.py`: **`3.168 triples`**, `0` syntax error, `0` pelanggaran domain/range.
- **Entitas (100% ber-`ra:nama` dan `rdfs:label`):** `42 RumahAdat`, `15 Suku`, `107 Marga`, `13 BahasaDaerah`, `61 Wilayah`, `17 Tradisi`, `13 Ornamen`.
- **Relasi OWL:** `ra:bagianDariWilayah` (`owl:TransitiveProperty`), `ra:berkerabatDenganMarga` (`owl:SymmetricProperty`), `ra:berkerabatDenganSuku` (`owl:SymmetricProperty`), ditambah relasi wilayah, suku, marga, bahasa, ornamen, dan tradisi.

---

## 4. Analisis Celah yang Tersisa (Menuju Search Engine Semantik Utuh)

1. **Belum Ada Apache Jena Fuseki & File Query SPARQL (`.rq`):**
   - Pencarian saat ini masih `str_contains` di array PHP terhadap 3 data dummy; belum bisa melakukan pencarian *multi-hop* (`marga`, `kerabat marga`, `kerabat suku`, `bahasa`, `tradisi` seperti `Maena`, `ornamen`, atau wilayah bertingkat `bagianDariWilayah+`) serta belum menghasilkan *match path* (alasan keterkaitan).
2. **Belum Ada Service Layer & Konfigurasi Triplestore:**
   - `.env.example` dan `config/services.php` belum menyimpan konfigurasi `FUSEKI_URL` / `FUSEKI_DATASET`.
   - `app/Services/` belum ada (`SparqlService` / `RumahAdatSearchService` belum dibuat untuk menggantikan `getHouses()` di `RumahAdatController`).
3. **Field UI Masih Terbatas pada Skema Dummy:**
   - **Beranda:** Statistik belum diambil dinamis dari Fuseki dan *Quick Explore Wilayah* baru menampilkan 3 dari wilayah utama Sumut (belum ada Nias, Toba, Mandailing/Angkola, Deli/Melayu, Pakpak, dsb.).
   - **Search (`search.blade.php`):** Filter Suku/Wilayah masih hardcoded terbatas; kartu hasil belum menampilkan **Badge Alasan Keterkaitan (match path)**, belum ada filter Tradisi/Ornamen, dan masih bertanda `DATA AWAL`.
   - **Detail (`detail.blade.php`):** Belum menampilkan 8 atribut kaya dari ontologi (`namaAlternatif`, `sejarah`, `fungsi`, `ciriArsitektur`, `bentukAtap`, `bahanBangunan`, `maknaFilosofis`, `referensi`) serta daftar relasi Marga, Bahasa, Ornamen, Tradisi, dan Wilayah induk yang dapat diklik.
4. **Visualisasi Knowledge Graph Interaktif & Aset Gambar Belum Dibuat:**
   - Belum ada route `/graph`, endpoint JSON `/api/graph`, view `graph.blade.php`, maupun library `Vis.js` / `Cytoscape.js` di `package.json` / `resources/js/app.js`.
   - Folder `public/images/rumah-adat/` baru berisi 1 gambar (`hero-rumah-adat.jpeg`).
5. **Pengujian & Kebersihan Environment:**
   - Belum ada feature/unit test untuk rute `/search` & `/detail/{slug}` maupun dokumen `QA_CHECKLIST.md`.
   - **File kembar `dataset/rumah_adat.ttl`:** Masih ada versi lama `2.739 triples` di `dataset/rumah_adat.ttl`; gunakan selalu **`ontology/rumah_adat.ttl` (`3.168 triples`)** dan hapus `dataset/rumah_adat.ttl` agar tidak salah muat ke Fuseki.
   - **File lokal `public/hot`:** Saat audit, `public/hot` kembali ada berisi `http://[::1]:5173`; hapus file ini jika menjalankan server Laravel tanpa `npm run dev` aktif.

---

## 5. Langkah Selanjutnya (Prioritas Saat Ini)

1. **Vascha (Prioritas Utama Sekarang):** Upload **`ontology/rumah_adat.ttl`** (`3.168 triples`) ke **Apache Jena Fuseki** lalu buat & uji query SPARQL `.rq` di `resources/sparql/` (statistik, multi-hop search + match path, filter kategori, detail lengkap 1 rumah adat, dan triplet graf).
2. **Jeli:** Tambahkan konfigurasi Fuseki di `.env.example` & `config/services.php`, bangun `SparqlService` untuk menggantikan array dummy `getHouses()` di `RumahAdatController`, siapkan route `GET /graph` & `GET /api/graph`, serta kirim statistik dinamis ke `home.blade.php`.
3. **Jona:** Sambungkan `search.blade.php` (badge *match path* + filter lengkap + hapus label `DATA AWAL`) dan `detail.blade.php` (tampilkan `sejarah`, `ciriArsitektur`, `bentukAtap`, `bahanBangunan`, `maknaFilosofis`, `referensi`, marga, bahasa, tradisi, ornamen sebagai tautan klikable), lengkapi Quick Explore Wilayah di Beranda, dan siapkan foto rumah adat di `public/images/rumah-adat/`.
4. **Patricia Putri:** Bangun halaman interaktif `/graph` dengan **Vis.js** atau **Cytoscape.js** dari `/api/graph` (tambahkan link-nya di `layouts/app.blade.php` & `detail.blade.php`), lalu jalankan pengujian QA multi-hop menyeluruh dan feature test.
