
# Pertemuan 12 — Inferensi dan Reasoning

Dokumentasi tugas kelompok Web Semantik Pertemuan 12.

## Langkah 4 — Wikidata

- **Q8 / Q9:** 877 / 1343
- **Penyebab selisih:** Q8 hanya menghitung entitas universitas
  di Indonesia yang secara langsung memiliki tipe university
  (Q3918). Q9 menggunakan property path wdt:P31/wdt:P279*
  sehingga turut menghitung entitas yang bertipe subclass dari
  university. Selisih hasil kedua query adalah 466 entitas.

- **Satu subclass yang menyumbang selisih:** Polytechnic
  (politeknik, Q845392), sebanyak 332 entitas. Berdasarkan
  query analisis, entitas tersebut terhubung ke kelas university
  melalui hierarki subclass, tetapi tidak langsung memiliki
  tipe university (Q3918).

- **WDQS: materialisasi, saat query, atau tidak keduanya?**
  Pada percobaan ini, hierarki subclass ditelusuri saat query
  melalui property path wdt:P279* yang ditulis secara eksplisit.
  Hasil Q9 tidak membuktikan bahwa WDQS otomatis melakukan
  materialisasi seluruh triple turunan atau menjalankan
  penalaran RDFS. Mesin query mengikuti jalur subclass yang
  tercatat di Wikidata.

### Berkas Query

- `queries/q8_univ_tanpa_jalur.rq`
- `queries/q9_univ_dengan_jalur.rq`

### Bukti Screenshot

- `screenshots/output-q8.png` — hasil Q8 (877).
- `screenshots/output-wikidata.png` — hasil Q9 (1343).
- `screenshots/output-subclass.png` — hasil analisis politeknik (332).

**Tanggal pengujian:** 16 November 2026.
