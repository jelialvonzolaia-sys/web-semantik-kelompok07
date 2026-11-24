# Analisis Progress Fitur Search Engine — Rumah Adat Sumatera Utara

> **Tanggal Audit:** 24 November 2026 (Malam)  
> **Lokasi Proyek:** `tubes_rumah_adat/` (Repository `web-semantik-kelompok07`)  
> **Branch / Commit Terakhir:** `main` (`9d53235` — *Menghubungkan Laravel dengan Fuseki (Integrasi Backend)* + pembaruan UI data asli & PHPUnit)

---

## 1. Ringkasan Eksekutif

Berdasarkan pemeriksaan langsung terhadap struktur proyek `tubes_rumah_adat/` setelah integrasi backend Jeli dan penyambungan UI data asli Jona:

- **Yang sudah selesai / tersedia di repo:**
  1. **Fondasi Aplikasi & Validator:** Laravel `13.43.0` + PHP `8.4.12` + Tailwind CSS v4 + Vite 8 + validator Python `rdflib` (`dataset/cek_ttl.py`) + Laravel Boost / Pint.
  2. **Ontologi & Knowledge Graph RDF (`ontology/rumah_adat.ttl`):** Final dan valid (`3.168 triples`, `42 RumahAdat`, `15 Suku`, `107 Marga`, `13 BahasaDaerah`, `61 Wilayah`, `17 Tradisi`, `13 Ornamen`, `owl:TransitiveProperty`, `owl:SymmetricProperty`, 100% `rdfs:label` & `ra:nama`, `0` error).
  3. **5 File Query SPARQL di `resources/sparql/` (PR #53 + Commit `6f54bab` — Vascha):** `stats.rq`, `filters.rq`, `detail.rq`, `search.rq`, dan `graph.rq` seluruhnya lulus verifikasi terhadap `ontology/rumah_adat.ttl` (`3.168 triples`).
  4. **Integrasi Backend Laravel ↔ Fuseki (Jeli — Selesai):** `.env`, `.env.example`, dan `config/services.php` (`services.fuseki`), `app/Services/SparqlService.php`, `RumahAdatController` (`index`, `search`, `detail`, `graph`, `apiGraph` tanpa array dummy `getHouses()`), 5 route aktif di `routes/web.php` (termasuk `/graph` placeholder & `/api/graph` JSON), serta `tests/Feature/RumahAdatSparqlTest.php` (`9` test / `39` assertions lulus).
  5. **UI Beranda, Search, & Detail Data Asli (Jona — Selesai):** `home.blade.php` (statistik dinamis + `6` *Quick Explore Wilayah* asli dari Fuseki + discovery keyword nyata), `search.blade.php` (sidebar filter dinamis `Marga`, `Suku`, `Wilayah`, `Tradisi`, `Ornamen`, `Bahasa Daerah`, badge skor + *Alasan kecocokan (`matchPaths`)*, tanpa label `DATA AWAL`), dan `detail.blade.php` (seluruh atribut & relasi klikable ontologi, tanpa teks *"data demonstrasi"*, plus dukungan foto lokal `public/images/rumah-adat/{slug}.*`).
- **Yang belum dikerjakan:**
  1. **Visualisasi Knowledge Graph Interaktif & QA (Patricia Putri — Sengaja Belum Disentuh):** Membangun tampilan graf interaktif Vis.js (`vis-network`) / Cytoscape.js di `resources/views/pages/graph.blade.php` (saat ini masih *placeholder* netral), menambahkan tautan **Knowledge Graph** (`/graph`) di `layouts/app.blade.php`, membedakan 7 warna kelas + label relasi + interaktivitas + filter fokus rumah/wilayah, serta menyusun `QA_CHECKLIST.md`.
  2. **Uji Live dengan Server Apache Jena Fuseki & Koleksi Foto:** Menyalakan Fuseki lokal (`http://localhost:3030`) dengan dataset `/rumah_adat` berisi `ontology/rumah_adat.ttl` (`3.168 triples`), serta melengkapi foto per rumah adat di `public/images/rumah-adat/{slug}.jpg` (jika ingin menampilkan foto unik per kartu).

---

## 2. Peta Tahapan Progress Fitur Search Engine

| Tahap | Nama Tahapan | Status | Kondisi Saat Ini di Codebase |
| :--- | :--- | :---: | :--- |
| **Tahap 1** | Setup Proyek & Toolchain | **Selesai** | Laravel `13.43.0`, PHP `8.4.12`, Vite `^8.0.0`, Tailwind CSS `^4.0.0`, SQLite + migrasi bawaan, Laravel Boost/Pint, Python `3.13.0` + `rdflib 7.6.0`. |
| **Tahap 2** | Ontologi & Dataset RDF/OWL | **Selesai** | `ontology/rumah_adat.ttl` final & valid (`3.168 triples`, 7 kelas, 267 entitas, `0` error sintaks/domain/range). |
| **Tahap 3** | UI Prototype Beranda, Search, & Detail | **Selesai** | Layout heritage `home.blade.php`, `search.blade.php`, dan `detail.blade.php` sudah siap dan terhubung ke data asli. |
| **Tahap 4** | Query SPARQL (`.rq`) & Kesiapan Fuseki | **Selesai (5 `.rq` Lulus Uji)** | `stats.rq`, `filters.rq`, `detail.rq`, `search.rq`, dan `graph.rq` lulus verifikasi `rdflib` terhadap `ontology/rumah_adat.ttl` (`3.168 triples`). |
| **Tahap 5** | Integrasi Backend Laravel ↔ Fuseki + UI Data Asli | **Selesai** | `SparqlService.php`, `config/services.php` (`fuseki`), `RumahAdatController.php`, `routes/web.php`, `home.blade.php`, `search.blade.php`, `detail.blade.php`, dan `RumahAdatSparqlTest.php` (`9/9` lulus) sudah siap. |
| **Tahap 6** | Visualisasi Knowledge Graph Interaktif & QA | **Siap Dikerjakan (Bagian Patricia)** | Endpoint `GET /api/graph` (`{ nodes, edges, meta }`) & `GET /graph` (*placeholder* netral) tersedia; UI Vis.js/Cytoscape.js di `graph.blade.php`, menu navbar `layouts/app.blade.php`, dan `QA_CHECKLIST.md` menunggu dikerjakan Patricia. |

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

## 4. Audit Komponen Lain & Status Terkini

- **Routing & Controller (`routes/web.php`, `RumahAdatController.php`, `SparqlService.php`):** Seluruh 5 route (`/`, `/search`, `/detail/{slug}`, `/graph`, `/api/graph`) sudah aktif memakai `SparqlService` dengan *graceful fallback* jika Fuseki offline. Dummy `getHouses()` sudah dihapus total.
- **Frontend (`home.blade.php`, `search.blade.php`, `detail.blade.php`, `graph.blade.php`):** Halaman Beranda, Pencarian, dan Detail sudah menampilkan data dinamis ontologi tanpa label dummy/demonstrasi. Sementara `layouts/app.blade.php` tetap seperti semula dan `resources/views/pages/graph.blade.php` berupa *placeholder* netral khusus agar bagian **Patricia Putri** tetap utuh.
- **Pengujian Otomatis (`tests/Feature/RumahAdatSparqlTest.php`):** `9` test (`39` assertions) lolos lewat PHPUnit (menggunakan `Http::fake`), ditambah pengujian query dinamis `SparqlService` langsung ke `ontology/rumah_adat.ttl` via Python `rdflib`.
- **Catatan Kebersihan Environment:**
  1. Masih ada file lama `dataset/rumah_adat.ttl` (`2.739 triples`) di samping file resmi `ontology/rumah_adat.ttl` (`3.168 triples`) — pastikan yang di-upload ke Fuseki adalah **`ontology/rumah_adat.ttl`**.
  2. File `public/hot` sudah tidak ada (`False`), dan aset `public/build/` tersedia (`php artisan serve` siap dijalankan langsung).

---

## 5. Langkah Selanjutnya (Prioritas Saat Ini)

1. **Uji Lokal End-to-End di Browser (Jeli / Vascha):**
   - Jalankan **Apache Jena Fuseki** lokal (`http://localhost:3030`), buat dataset `/rumah_adat` (*Persistent*), dan upload `ontology/rumah_adat.ttl` (`3.168 triples`).
   - Jalankan `php artisan serve` lalu uji alur lengkap `/`, `/search`, `/detail/{slug}`, dan `/api/graph`.
2. **Patricia Putri (Knowledge Graph Interaktif & QA):**
   - Bangun `resources/views/pages/graph.blade.php` menggunakan **Vis.js (`vis-network`)** atau **Cytoscape.js** dari `/api/graph` (7 warna kelas, label relasi, klik node, zoom/pan, filter fokus rumah/wilayah) dan tambahkan tautan menu **Knowledge Graph** (`/graph`) di `layouts/app.blade.php`.
   - Jalankan pengujian QA end-to-end (`QA_CHECKLIST.md`) untuk seluruh skenario *multi-hop* (`marga`, `kerabat marga`, `suku`, `wilayah bertingkat`, `tradisi Maena/Hombo Batu`, `ornamen`, `bahasa`).
3. **Koleksi Foto Rumah Adat (Opsional — Jona):**
   - Tambahkan foto lokal `public/images/rumah-adat/{slug}.jpg` (atau `.jpeg`/`.png`/`.webp`); backend `SparqlService` sudah otomatis mendeteksinya untuk kartu pencarian dan halaman detail.
