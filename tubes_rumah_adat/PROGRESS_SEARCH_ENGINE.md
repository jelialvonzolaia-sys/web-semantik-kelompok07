# Analisis Progress Fitur Search Engine — Rumah Adat Sumatera Utara

> **Tanggal Audit:** 24 November 2026 (Malam)  
> **Lokasi Proyek:** `tubes_rumah_adat/` (Repository `web-semantik-kelompok07`)  
> **Branch / Commit Terakhir:** `main` (`2f3aac2` — *Merge pull request #53*, ditambah penyempurnaan lokal `search.rq` & `graph.rq`)

---

## 1. Ringkasan Eksekutif

Berdasarkan pemeriksaan langsung terhadap struktur proyek `tubes_rumah_adat/` hingga commit `2f3aac2` beserta penyempurnaan query di `resources/sparql/`, komponen inti data, UI prototype, dan query SPARQL sudah siap:

- **Yang sudah selesai / tersedia di repo:**
  1. **Fondasi Aplikasi & Validator:** Laravel `13.43.0` + Tailwind CSS v4 + Vite 8 + validator Python `rdflib` (`dataset/cek_ttl.py`).
  2. **Ontologi & Knowledge Graph RDF (`ontology/rumah_adat.ttl`):** Final dan valid (`3.168 triples`, `42 RumahAdat`, `15 Suku`, `107 Marga`, `13 BahasaDaerah`, `61 Wilayah`, `17 Tradisi`, `13 Ornamen`, `owl:TransitiveProperty`, `owl:SymmetricProperty`, 100% `rdfs:label` & `ra:nama`, `0` error).
  3. **UI Prototype & Routing Awal (PR #50–#52):** `routes/web.php` (`/`, `/search`, `/detail/{slug}`), `RumahAdatController.php` (masih 3 data dummy), `home.blade.php`, `search.blade.php`, dan `detail.blade.php`.
  4. **5 File Query SPARQL di `resources/sparql/` (PR #53 — Vascha + Perbaikan):** `stats.rq`, `filters.rq`, `detail.rq`, `search.rq`, dan `graph.rq` seluruhnya sudah lulus verifikasi terhadap `ontology/rumah_adat.ttl` (`3.168 triples`).
- **Yang belum dikerjakan (Menyambungkan Semua Bagian):**
  1. **Kesiapan Server Fuseki Lokal (Vascha):** Pastikan Apache Jena Fuseki berjalan di `http://localhost:3030` dengan dataset `/rumah_adat` (*Persistent*) berisi `ontology/rumah_adat.ttl` (`3.168 triples`).
  2. **Integrasi Backend Laravel ↔ Fuseki (Jeli):** Belum ada `FusekiSparqlService` / `SparqlService`, belum ada `FUSEKI_URL`/`FUSEKI_DATASET` di `.env.example` & `config/services.php`, `RumahAdatController` masih memakai 3 data dummy, dan belum ada route `/graph` & `/api/graph`.
  3. **UI Data Asli, Visualisasi Knowledge Graph, & QA (Jona & Patricia):** Belum ada badge *match path* di `search.blade.php`, atribut detail ontologi lengkap di `detail.blade.php`, foto koleksi rumah adat di `public/images/rumah-adat/`, halaman `/graph` (`Vis.js`/`Cytoscape.js`), maupun `QA_CHECKLIST.md` & feature test.

---

## 2. Peta Tahapan Progress Fitur Search Engine

| Tahap | Nama Tahapan | Status | Kondisi Saat Ini di Codebase |
| :--- | :--- | :---: | :--- |
| **Tahap 1** | Setup Proyek & Toolchain | **Selesai** | Laravel `13.43.0`, PHP `8.4.12`, Vite `^8.0.0`, Tailwind CSS `^4.0.0`, SQLite + migrasi bawaan, Python `3.13.0` + `rdflib 7.6.0` (`dataset/cek_ttl.py`). |
| **Tahap 2** | Ontologi & Dataset RDF/OWL | **Selesai** | `ontology/rumah_adat.ttl` final & valid (`3.168 triples`, 7 kelas, 267 entitas, `0` error sintaks/domain/range). |
| **Tahap 3** | UI Prototype Beranda, Search, & Detail + Controller Dummy | **Selesai (Prototype)** | `routes/web.php` (`/`, `/search`, `/detail/{slug}`), `RumahAdatController.php` (3 dummy), `home.blade.php`, `search.blade.php`, dan `detail.blade.php` sudah berjalan. |
| **Tahap 4** | Query SPARQL (`.rq`) & Kesiapan Fuseki | **Selesai (5 `.rq` Lulus Uji)** | `stats.rq`, `filters.rq`, `detail.rq`, `search.rq`, dan `graph.rq` seluruhnya lulus verifikasi terhadap `ontology/rumah_adat.ttl` (`3.168 triples`); tinggal memastikan Fuseki lokal memuat dataset `/rumah_adat`. |
| **Tahap 5** | Integrasi Backend Laravel ↔ Fuseki + Penyambungan UI Data Asli | **Belum Dimulai** | Belum ada `SparqlService`, config Fuseki, penggantian dummy di `RumahAdatController`, badge *match path* di UI, maupun foto lokal per rumah adat. |
| **Tahap 6** | Visualisasi Knowledge Graph Interaktif & QA/Tests | **Belum Dimulai** | Belum ada route `/graph`, `/api/graph`, `graph.blade.php` (`Vis.js`/`Cytoscape.js`), maupun `QA_CHECKLIST.md` & feature test. |

---

## 3. Audit Terbaru: Hasil Pengujian 5 File SPARQL (`resources/sparql/`)

Seluruh file `.rq` (`resources/sparql/`) telah diuji langsung terhadap `ontology/rumah_adat.ttl` (`3.168 triples`):

| File | Hasil Uji | Catatan Teknis |
| :--- | :---: | :--- |
| **`stats.rq`** | **Lulus (`7` baris)** | Mengembalikan angka tepat: `BahasaDaerah=13`, `Marga=107`, `Ornamen=13`, `RumahAdat=42`, `Suku=15`, `Tradisi=17`, `Wilayah=61`. |
| **`filters.rq`** | **Lulus (`226` baris)** | Mengembalikan seluruh opsi filter (`suku`, `wilayah`, `tradisi`, `ornamen`, `bahasa`, `marga`) terurut rapi. |
| **`detail.rq`** | **Lulus (`42/42` rumah)** | Diuji pada seluruh 42 `ra:RumahAdat`; setiap entitas mengembalikan tepat `1` baris dengan 9 atribut teks + 6 `GROUP_CONCAT` relasi. *(Opsional: bisa ditambah `wilayahInduk` via `ra:bagianDariWilayah+` untuk breadcrumb wilayah).* |
| **`search.rq`** | **Lulus (`Multi-hop & Teks`)** | Struktur output (`?rumah ?namaRumah ?namaSuku ?namaWilayah ?matchPath ?relevanceScore`) dipertahankan dengan 11 cabang `UNION` (skor 55–100: nama rumah, marga langsung/berkerabat, nama alternatif, suku langsung/berkerabat, wilayah transitif, bahasa, tradisi, ornamen, dan deskripsi & arsitektur). `BIND(LCASE("laia") AS ?ql)` dan `FILTER(?ql != "" && CONTAINS(LCASE(STR(?matchValue)), ?ql))` berada di luar `UNION` agar valid SPARQL 1.1: `"laia"` (`36` baris / `4` rumah), `"maena"` (`7` baris / `4` rumah), `"Sopo Godang"` (`11` baris / `7` rumah), `"Arsitektur"` (`20` baris / `18` rumah), `"Kabupaten Toba"` (`8` baris / `3` rumah), `"Harahap"` (`49` baris / `7` rumah), `"Tengku"` (`64` baris / `15` rumah), dan `""` aman (`0` baris). Catatan integrasi backend: hasil tetap perlu digabung/dikelompokkan per `?rumah` di Laravel. |
| **`graph.rq`** | **Lulus (`600` baris, `0` TBox)** | Mewajibkan `?subject` dan `?object` bertipe 7 kelas domain (`0` baris `"Entity"`/`rdfs:domain`/`rdfs:range`), menambahkan prioritas relasi inti `RumahAdat` (`?priority`), `ORDER BY`, dan `LIMIT 600`. Mencakup seluruh `42` `RumahAdat` (`545` edge dari `RumahAdat`, `49` `Wilayah`, `6` `Suku`; termasuk `42` `dimiliki oleh suku` dan `42` `menggunakan bahasa`). |

---

## 4. Audit Komponen Lain yang Sudah Ada

- **Routing & Controller (`routes/web.php`, `RumahAdatController.php`):** Route `/`, `/search`, `/detail/{slug}` sudah aktif memakai 3 data dummy (`rumah-bolon`, `siwaluh-jabu`, `rumah-bolon-simalungun`).
- **Frontend (`home.blade.php`, `search.blade.php`, `detail.blade.php`):** Desain heritage sudah rapi dan responsif. Di `home.blade.php`, kotak statistik `42/15/61/107` masih array statis dan Quick Explore baru menampilkan 3 wilayah. Di `search.blade.php`, filter masih hardcoded dan belum ada *badge match path*. Di `detail.blade.php`, diagram graf masih *Prototype Visualization* statis 3 node.
- **Catatan Kebersihan Environment:**
  1. Masih ada file lama `dataset/rumah_adat.ttl` (`2.739 triples`) di samping file resmi `ontology/rumah_adat.ttl` (`3.168 triples`) — pastikan yang di-upload ke Fuseki adalah **`ontology/rumah_adat.ttl`**.
  2. File lokal `public/hot` (`http://[::1]:5173`) masih ada; hapus jika menjalankan Laravel tanpa `npm run dev`.
  3. Saat test PHPUnit di sandbox sebelumnya (`vendor/autoload.php` unreadable), pastikan `vendor/` terbaca normal di mesin masing-masing.

---

## 5. Langkah Selanjutnya (Prioritas Saat Ini)

1. **Vascha (Kesiapan Server Apache Jena Fuseki):**
   - Pastikan **Apache Jena Fuseki** berjalan lokal (`http://localhost:3030`) dengan dataset `/rumah_adat` (*Persistent*) berisi `ontology/rumah_adat.ttl` (`3.168 triples`) dan seluruh 5 file `.rq` yang sudah diperbaiki berjalan lancar di tab SPARQL Query.
2. **Jeli (Integrasi Backend Laravel ↔ Fuseki):**
   - Tambah `FUSEKI_URL` & `FUSEKI_DATASET` di `.env.example` dan `config/services.php`.
   - Buat `FusekiSparqlService` (`app/Services/`), sambungkan ke `RumahAdatController` & `home` (ganti 3 data dummy dengan query Fuseki + handle kasus `q` kosong & deduplikasi hasil), serta tambahkan route `GET /graph` & `GET /api/graph`.
3. **Jona (Sambungkan UI ke Data Asli):**
   - Tampilkan statistik asli & perluas *Quick Explore Wilayah* di `home.blade.php`.
   - Tampilkan *badge alasan keterkaitan (`matchPath`)* & filter dinamis di `search.blade.php` (hapus label `DATA AWAL`).
   - Tampilkan seluruh field ontologi (`sejarah`, `fungsi`, `ciriArsitektur`, `bentukAtap`, `bahanBangunan`, `maknaFilosofis`, `referensi`, marga, bahasa, ornamen, tradisi sebagai tautan klikable) di `detail.blade.php`, serta lengkapi foto rumah adat di `public/images/rumah-adat/`.
4. **Patricia Putri (Knowledge Graph Interaktif & QA):**
   - Bangun `resources/views/pages/graph.blade.php` menggunakan **Vis.js (`vis-network`)** atau **Cytoscape.js** dari `/api/graph` dan tambahkan menu navigasinya di `layouts/app.blade.php`.
   - Jalankan pengujian QA end-to-end (`QA_CHECKLIST.md`) untuk seluruh skenario *multi-hop* (`marga`, `kerabat marga`, `suku`, `wilayah bertingkat`, `tradisi Maena/Hombo Batu`, `ornamen`, `bahasa`) serta buat feature test di `tests/Feature/`.
