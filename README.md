# 🌐 Web Semantik - Kelompok 07

Repository ini dibuat untuk mendokumentasikan seluruh rangkaian tugas, latihan, praktikum (`pertemuan-01` s.d. `pertemuan-12`), dan **Project Tugas Besar** mata kuliah **Web Semantik** pada folder [`tubes_rumah_adat/`](tubes_rumah_adat/README.md).

---

## 👥 Anggota Kelompok

| No. | Nama | NIM | Peran di Tugas Besar |
|---:|---|---|---|
| 1 | Jeli Alvonzo Laia | 251402101 | Project Lead & Backend Integration (`SparqlService`, Controller, Test) |
| 2 | Patricia Putri Josephine Situmeang | 251402119 | Visualisasi Knowledge Graph (`vis-network`) & QA |
| 3 | Vascha U. Lumbantoruan | 251402125 | Query SPARQL (`resources/sparql/*.rq`) & Apache Jena Fuseki |
| 4 | Chyntia R.A Hutabarat | 251402002 | Ontologi & Dataset RDF/OWL (`ontology/rumah_adat.ttl`) |
| 5 | Jona Vebrian Gultom | 251402084 | UI/UX & Frontend Blade/Tailwind CSS |

---

## 🏛️ Proyek Tugas Besar: Search Engine & Knowledge Graph Rumah Adat Sumatera Utara

Aplikasi web pencarian semantik dan eksplorasi graf pengetahuan untuk warisan arsitektur dan budaya **Rumah Adat Sumatera Utara**, dibangun dengan **Laravel 13**, **Apache Jena Fuseki (SPARQL + OWL Reasoner)**, **Tailwind CSS v4**, dan **Vis-Network**.

### Struktur Utama Repositori

- `pertemuan-01/` s.d. `pertemuan-12/` — Latihan dan praktikum mingguan Web Semantik (Turtle, SPARQL, SHACL/validasi, RDFS/OWL Reasoning).
- `tubes_rumah_adat/` — Aplikasi utama Tugas Besar:
  - `ontology/rumah_adat.ttl` — Ontologi OWL/RDFS & dataset RDF (`3.188 triples`, `7` kelas inti, `268` entitas).
  - `ontology/fuseki-reasoner.ttl` — Konfigurasi assembler Fuseki dengan `OWLFBRuleReasoner` (`/rumah_adat_reasoned/query`).
  - `resources/sparql/*.rq` — Koleksi query SPARQL (`stats`, `filters`, `search`, `detail`, `graph`, dan `reasoning_demo`).
  - `app/Services/SparqlService.php` — Layanan integrasi Laravel ↔ Fuseki, pencarian *multi-hop*, koreksi typo (*fuzzy matching*), dan ekspansi singkatan daerah/budaya.

### Menjalankan Proyek Tugas Besar

```bash
cd tubes_rumah_adat
composer install
copy .env.example .env
php artisan key:generate
php artisan migrate
npm install
npm run build
```

1. Jalankan **Apache Jena Fuseki** di `http://localhost:3030` dengan dataset `/rumah_adat` dan unggah `tubes_rumah_adat/ontology/rumah_adat.ttl` (atau gunakan `ontology/fuseki-reasoner.ttl` untuk endpoint ber-reasoner `/rumah_adat_reasoned`).
2. Jalankan server Laravel: `php artisan serve` lalu buka `http://127.0.0.1:8000`.
3. Jalankan pengujian otomatis: `php artisan test`.

Untuk dokumentasi lengkap mengenai arsitektur, struktur ontologi, query SPARQL, endpoint API, dan skenario uji reasoning, lihat **[`tubes_rumah_adat/README.md`](tubes_rumah_adat/README.md)**.
