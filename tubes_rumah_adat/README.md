# Search Engine Semantik & Knowledge Graph Rumah Adat Sumatera Utara

Aplikasi web berbasis **Semantic Web** untuk pencarian *multi-hop*, penelusuran keterkaitan budaya, dan visualisasi *Knowledge Graph* **Rumah Adat Sumatera Utara** yang dikembangkan oleh **Kelompok 07** untuk **Tugas Besar Mata Kuliah Web Semantik**.

---

## 1. Deskripsi Proyek & Arsitektur Sistem

Sistem ini memisahkan lapisan penyimpanan pengetahuan semantik (**RDF/OWL Triplestore**) dari lapisan presentasi web (**Laravel**) agar seluruh pencarian, filter relasi, detail entitas, dan graf keterkaitan berasal langsung dari dataset ontologi melalui protokol HTTP **SPARQL 1.1**.

```text
+-----------------------------------------------------------------------+
|                       Browser / Antarmuka Pengguna                    |
|  - Beranda & Live Search Dropdown (home.blade.php)                    |
|  - Pencarian Multi-Hop, Typo & Akronim + Filter (search.blade.php)    |
|  - Detail Entitas & Relasi Budaya (detail.blade.php)                  |
|  - Visualisasi Knowledge Graph Vis-Network (graph.blade.php + app.js) |
+-----------------------------------+-----------------------------------+
                                    | HTTP / JSON (/api/search/live, /api/graph)
                                    v
+-----------------------------------------------------------------------+
|                       Backend Laravel (^13.17 / PHP ^8.3)             |
|  - Route Web & API (routes/web.php)                                   |
|  - RumahAdatController                                                |
|  - App\Services\SparqlService (Load .rq, Fuzzy/Abbreviation, Cache)   |
+-----------------------------------+-----------------------------------+
                                    | HTTP POST (Accept: application/sparql-results+json)
                                    v
+-----------------------------------------------------------------------+
|              Apache Jena Fuseki Triplestore (:3030)                   |
|  - Dataset Standar   : /rumah_adat/query (ontology/rumah_adat.ttl)    |
|  - Dataset Reasoner  : /rumah_adat_reasoned/query                     |
|                        (ontology/fuseki-reasoner.ttl + OWL Reasoner)  |
+-----------------------------------------------------------------------+
```

### Teknologi Utama
- **Backend:** PHP `^8.3`, Laravel `^13.17`, Laravel Pint & PHPUnit `^12.5`
- **Triplestore & Ontologi:** Apache Jena Fuseki, RDF/Turtle, OWL/RDFS (`OWLFBRuleReasoner`)
- **Frontend & Visualisasi:** Blade, Tailwind CSS `^4.0`, Vite `^8.0`, `vis-network` (`^10.1.5`)
- **Validator Lokal (Opsional):** Python 3 + `rdflib` (`7.6.0`) & `owlrl`

---

## 2. Struktur Ontologi (`ontology/rumah_adat.ttl`)

Dataset dan skema ontologi resmi tersimpan pada `ontology/rumah_adat.ttl` (**3.188 triples *asserted***).

### Vocabulary & Namespace yang Digunakan
- **Vocabulary Standar Inti:** `rdf:`, `rdfs:`, `owl:`, `xsd:`
- **Vocabulary Standar Eksternal (Reuse):**
  - `schema:` (`https://schema.org/`) — pemetaan kelas & properti (`schema:LandmarksOrHistoricalBuildings`, `schema:AdministrativeArea`, `schema:Language`, `schema:Event`, `schema:VisualArtwork`, `schema:name`, `schema:containedInPlace`)
  - `dct:` (`http://purl.org/dc/terms/`) — metadata ontologi (`dct:title`, `dct:description`, `dct:creator`, `dct:created`) dan pemetaan `ra:sejarah` (`dct:description`)
  - `skos:` (`http://www.w3.org/2004/02/skos/core#`) — pemetaan label alternatif `ra:namaAlternatif` (`skos:altLabel`)
- **Namespace Domain:** `ra:` (`http://example.org/ontology/rumah-adat#`)

### Ringkasan Statistik Ontologi
- **Total Triples (*asserted*):** `3.188 triples`
- **Total Kelas (`owl:Class`):** `7` kelas inti
- **Total Instance:** `268` entitas unik
- **Total `owl:ObjectProperty`:** `12` properti (`11` properti relasi utama + `ra:memilikiRumahAdat` sebagai invers)
- **Total `owl:DatatypeProperty`:** `9` properti (`ra:nama`, `ra:namaAlternatif`, `ra:sejarah`, `ra:fungsi`, `ra:ciriArsitektur`, `ra:bentukAtap`, `ra:bahanBangunan`, `ra:maknaFilosofis`, `ra:referensi`)

| Kelas (`owl:Class`) | Pemetaan `rdfs:subClassOf` | Jumlah Instance | Deskripsi Singkat |
|---|---|---:|---|
| `ra:RumahAdat` | `schema:LandmarksOrHistoricalBuildings` | 42 | Bangunan rumah adat tradisional Sumatera Utara |
| `ra:Suku` | - | 15 | Suku/sub-etnis pemilik rumah adat |
| `ra:Marga` | - | 107 | Marga kekerabatan yang terhubung ke rumah adat/suku |
| `ra:Wilayah` | `schema:AdministrativeArea` | 61 | Wilayah administratif bertingkat (Desa/Kecamatan → Kab/Kota → Provinsi) |
| `ra:Tradisi` | `schema:Event` | 17 | Upacara adat, ritual, dan praktik budaya |
| `ra:Ornamen` | `schema:VisualArtwork` | 13 | Ukiran dan motif arsitektur khas |
| `ra:BahasaDaerah` | `schema:Language` | 13 | Bahasa daerah pada komunitas rumah adat |

### Aksioma OWL & RDFS Utama
- `owl:TransitiveProperty`: `ra:bagianDariWilayah` (hierarki wilayah desa/kecamatan → kabupaten/kota → `ra:wil_sumatera_utara`)
- `owl:SymmetricProperty`: `ra:berkerabatDenganMarga`, `ra:berkerabatDenganSuku`
- `owl:inverseOf`: `ra:memilikiRumahAdat owl:inverseOf ra:dimilikiOlehSuku`
- `rdfs:subClassOf` & `rdfs:subPropertyOf`: menghubungkan kelas dan properti `ra:` ke `schema:`, `dct:`, `skos:`, dan `rdfs:label`
- File konfigurasi assembler reasoner Fuseki tersedia di `ontology/fuseki-reasoner.ttl`.

---

## 3. Daftar Lengkap Query SPARQL (`resources/sparql/*.rq`)

| File Query | Pemanggil di `SparqlService` | Fungsi Utama |
|---|---|---|
| `resources/sparql/stats.rq` | `getStats()` | Menghitung jumlah entitas pada ke-7 kelas domain (`RumahAdat`, `Suku`, `Marga`, `Wilayah`, `Tradisi`, `Ornamen`, `BahasaDaerah`) untuk statistik Beranda. |
| `resources/sparql/filters.rq` | `getFilterOptions()` | Mengambil daftar opsi filter dinamis untuk sidebar kategori `suku`, `wilayah`, `tradisi`, `ornamen`, `bahasa`, dan `marga`. |
| `resources/sparql/search.rq` | `search()` | Pencarian semantik *multi-hop* 11 cabang `UNION` (skor 55–100) mencakup nama rumah, marga langsung & kerabat (`ra:berkerabatDenganMarga`), nama alternatif, suku langsung & kerabat (`ra:berkerabatDenganSuku`), wilayah transitif (`ra:bagianDariWilayah*`), bahasa, tradisi, ornamen, serta atribut teks + label `?matchPath`. |
| `resources/sparql/detail.rq` | `getDetail()` | Mengambil seluruh atribut historis/arsitektur (`9` datatype property) dan `6` relasi `GROUP_CONCAT` untuk satu entitas rumah adat. |
| `resources/sparql/graph.rq` | `getGraphData()` | Mengambil triple relasi antarentitas 7 kelas domain (`LIMIT 600`, bebas noise TBox) untuk dirender di *Knowledge Graph* `vis-network`. |
| `resources/sparql/reasoning_demo.rq` | *(Uji/Demo Bab 5)* | Query pembuktian *Before vs After Reasoning* tanpa *property path* `*`/`+` untuk menguji inferensi transitif `ra:bagianDariWilayah` (17 → 42 rumah adat), invers `ra:memilikiRumahAdat`, `rdfs:subClassOf schema:LandmarksOrHistoricalBuildings`, dan simetri `ra:berkerabatDenganMarga`. |

*(Catatan: `SparqlService` juga menjalankan beberapa query inline tambahan untuk wilayah unggulan Beranda `getFeaturedRegions()`, pembangunan kamus Cache `fetchOntologyDictionary()`, mode jelajah tanpa kata kunci `buildBrowseQuery()`, serta subgraf fokus `buildFocusedGraphQuery()`.)*

---

## 4. Panduan Instalasi & Menjalankan Aplikasi

### Prasyarat
- **PHP** `^8.3` (pastikan ekstensi `pdo_sqlite`, `sqlite3`, `mbstring`, `curl`, `openssl` aktif)
- **Composer**
- **Node.js & npm**
- **Apache Jena Fuseki** (berjalan di port default `3030`)
- **Python 3** dengan `rdflib` & `owlrl` *(opsional, untuk validasi TTL & uji reasoning offline)*

### Langkah 1 — Menyalakan Apache Jena Fuseki & Memuat Ontologi

**Opsi A: Dataset Standar (`/rumah_adat` — untuk aplikasi web utama)**
1. Jalankan server Apache Jena Fuseki:
   ```bash
   fuseki-server
   ```
2. Buka Fuseki Web UI di `http://localhost:3030`.
3. Pilih **Manage datasets** → **Add new dataset**:
   - **Dataset name:** `rumah_adat`
   - **Dataset type:** *Persistent (TDB2)* atau *In-memory*
4. Buka tab **add data** pada dataset `/rumah_adat`, lalu unggah file `ontology/rumah_adat.ttl` (`3.188 triples`).

**Opsi B: Dataset Ber-Reasoner (`/rumah_adat_reasoned` — untuk demo/uji inferensi OWL Bab 5)**
Jalankan Fuseki dengan file assembler `ontology/fuseki-reasoner.ttl` dari dalam folder `tubes_rumah_adat/`:
```bash
cd tubes_rumah_adat
fuseki-server --config=ontology/fuseki-reasoner.ttl
```
Endpoint reasoning akan aktif di `http://localhost:3030/rumah_adat_reasoned/query`. Untuk mengetesnya, tempelkan isi `resources/sparql/reasoning_demo.rq` pada tab query di `/rumah_adat` (tanpa reasoner) lalu bandingkan hasilnya dengan `/rumah_adat_reasoned` (dengan `OWLFBRuleReasoner`).

### Langkah 2 — Instalasi Dependency & Konfigurasi Laravel

Masuk ke direktori aplikasi `tubes_rumah_adat/`:
```bash
cd tubes_rumah_adat
composer install
```

Salin file konfigurasi environment dan buat kunci aplikasi:
```bash
# Windows PowerShell / CMD
copy .env.example .env

# macOS / Linux
cp .env.example .env

php artisan key:generate
```

Pastikan konfigurasi Fuseki di `.env` sesuai dengan server Fuseki Anda:
```env
FUSEKI_BASE_URL=http://localhost:3030
FUSEKI_DATASET=rumah_adat
FUSEKI_SPARQL_ENDPOINT=http://localhost:3030/rumah_adat/query
```
*(Bila menggunakan Windows SQLite bawaan `.env`, siapkan file `database/database.sqlite` bila belum ada dan jalankan migrasi)*:
```bash
php artisan migrate
```

Pasang dependency frontend (`vis-network` & Tailwind CSS v4) dan build aset Vite:
```bash
npm install
npm run build
```

### Langkah 3 — Menjalankan Server Aplikasi

Jalankan server pengembangan Laravel:
```bash
php artisan serve
```
Akses aplikasi melalui browser di `http://127.0.0.1:8000`.
*(Jika sedang mengembangkan frontend dan menjalankan `npm run dev` secara bersamaan, pastikan `npm install` sudah selesai dijalankan dan hentikan proses dev dengan `Ctrl + C` bila ingin kembali memakai build produksi di `public/build/`.)*

---

## 5. Daftar Endpoint Web & API + Pengujian

### Daftar Endpoint (`routes/web.php`)

| Method | URI | Nama Route | Controller Method | Deskripsi |
|---|---|---|---|---|
| `GET` | `/` | `home` | `RumahAdatController@index` | Halaman beranda: statistik real-time Fuseki, live search dropdown, & 6 wilayah utama |
| `GET` | `/search` | `search` | `RumahAdatController@search` | Halaman pencarian semantik, filter multi-kategori, koreksi typo/singkatan, & *match paths* |
| `GET` | `/api/search/live` | `api.search.live` | `RumahAdatController@liveSearch` | Endpoint JSON untuk live instant search (query `?q=` & parameter filter aktif) |
| `GET` | `/detail/{slug}` | `detail` | `RumahAdatController@detail` | Halaman profil detail rumah adat & panel keterkaitan budaya dari ontologi |
| `GET` | `/graph` | `graph` | `RumahAdatController@graph` | Halaman visualisasi interaktif *Knowledge Graph* (7 kelas warna, filter fokus, & inspektor node) |
| `GET` | `/api/graph` | `api.graph` | `RumahAdatController@apiGraph` | Endpoint JSON `{ nodes, edges, meta }` untuk `vis-network` (mendukung `?focus=` & `?q=`) |

### Menjalankan Pengujian Otomatis

1. **Suite Feature Test Laravel (PHPUnit dengan `Http::fake`):**
   ```bash
   php artisan test
   ```
   *(atau langsung: `php vendor/phpunit/phpunit/phpunit`)*
2. **Validasi Sintaks & Kelengkapan Tipe Ontologi RDF:**
   ```bash
   python dataset/cek_ttl.py
   ```
3. **Pengecekan Gaya Kode PHP (Laravel Pint):**
   ```bash
   php vendor/bin/pint --test
   ```

---

## Anggota Tim — Kelompok 07
- **Jeli Alvonzo Laia** (251402101)
- **Patricia Putri Josephine Situmeang** (251402119)
- **Vascha U. Lumbantoruan** (251402125)
- **Chyntia R.A Hutabarat** (251402002)
- **Jona Vebrian Gultom** (251402084)
