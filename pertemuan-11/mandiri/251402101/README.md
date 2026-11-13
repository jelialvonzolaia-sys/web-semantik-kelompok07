# Pertemuan 11 - Tugas Mandiri: Dari Endpoint ke Graf Lokal

## Bagian A - LIMIT dan triple
- Triple dengan LIMIT 10: 30
- Triple dengan LIMIT 30: 90
- Baris SELECT LIMIT 10 / kota berbeda: 10 / 10
- Mengapa tidak (atau mengapa tepat) 30 triple: Hasil `CONSTRUCT` dengan `LIMIT 10` menghasilkan tepat 30 triple karena klausa `LIMIT 10` membatasi hingga 10 *solution* (10 baris pada hasil `SELECT`), dan setiap *solution* memiliki 1 nilai `?kota`, 1 `?namaKota` berbahasa Indonesia, serta 1 nilai `?populasi` yang berbeda-beda (terdapat 10 kota unik: Jakarta, Kota Manokwari, Singaraja, Tual, Soreang, Cikarang Pusat, Cibinong, Cianjur, Atambua, dan Sintang). Karena tidak ada kota yang memiliki lebih dari satu nilai populasi dalam 10 *solution* tersebut, seluruh $10 \times 3 = 30$ cetakan triple bersifat unik sehingga tidak ada triple kembar yang digabung.

## Bagian B - DBpedia ke Turtle
- Output demo_c.py: 48 triple, film tanpa tanggal: 16
- Sumber data: DBpedia
- Triple yang tetap tercetak untuk film tanpa tanggal: Triple yang tetap tercetak adalah `?film a schema:Movie`, `?film schema:name ?judul`, dan `?film schema:director dbr:Christopher_Nolan` (3 triple per film, sehingga untuk 16 film tercetak total 48 triple). Triple yang tidak tercetak adalah `?film schema:datePublished ?rilis`. Tidak terjadi pesan error karena pola `OPTIONAL { ?film dbo:releaseDate ?rilis }` membiarkan variabel `?rilis` tidak terikat (*unbound*) ketika data tanggal rilis tidak ada, dan dalam aturan `CONSTRUCT`, pola triple yang mengandung variabel tak terikat akan dilewati secara diam-diam tanpa gagal.
- Query lokal tanpa internet: Menjalankan hanya `Q_TANPA_TANGGAL` pada `film_nolan.ttl` berhasil dan menghasilkan `16` karena berkas `film_nolan.ttl` sudah tersimpan sebagai *cache* lokal di penyimpanan komputer dan diproses sepenuhnya oleh `rdflib` secara luring (*offline*) tanpa mengakses jaringan. Sebaliknya, jika seluruh skrip `demo_c.py` dijalankan ulang tanpa koneksi internet, program akan mengalami error (gagal koneksi / `URLError`) pada baris `sp.queryAndConvert()` karena skrip mencoba menghubungi endpoint publik `https://dbpedia.org/sparql`.
- Mengapa dbo:Film memberi 0 pada film_nolan.ttl: Karena `film_nolan.ttl` adalah graf baru hasil perintah `CONSTRUCT` yang memetakan kosakata sumber DBpedia (`dbo:Film`) ke kosakata target `schema.org` (`schema:Movie`). Triple dengan predikat `rdf:type dbo:Film` hanya dipakai untuk pencocokan pola di klausa `WHERE` pada saat query ke DBpedia, dan tidak ikut dicetak ke dalam templat `CONSTRUCT`, sehingga di dalam berkas `film_nolan.ttl` sama sekali tidak ada triple dengan kelas `dbo:Film`.

## Bagian C - Materialisasi
- Output demo_d.py: [isi]
- Mengapa Ada Rina bernilai 3: [jawaban]
- Output materialisasi_ulang.py: [isi]
- Tiga triple yang bukan baru: [jawaban]
- Kegunaan kampus_turunan.ttl dan langkah manual yang tersisa: [jawaban]

## Bagian D - Diagnosis
| Query | Hasil mesin | Gejala | Penyebab (baris yang salah) | Hasil setelah diperbaiki |
|-------|-------------|--------|-----------------------------|--------------------------|
| rusak_a | [isi] | [isi] | [isi] | [isi] |
| rusak_b | [isi] | [isi] | [isi] | [isi] |
| rusak_c | [isi] | [isi] | [isi] | [isi] |

## Bagian E - Etika dan diskusi
| Aturan | a_kota_limit.rq | demo_c.py |
|--------|-----------------|-----------|
| 1 User-Agent | [isi] | [isi] |
| 2 LIMIT | [isi] | [isi] |
| 3 Simpan, jangan ulangi | [isi] | [isi] |
| 4 Jangkar jalur | [isi] | [isi] |
- Versi yang melanggar aturan 4 (tidak dijalankan) dan alasannya: [isi]

### Diskusi 1 - Taksonomi berubah
[150-250 kata]

### Diskusi 2 - SELECT atau CONSTRUCT
[150-250 kata]

### Cek diri
| Pernyataan | Bukti dari tugas ini |
|------------|----------------------|
| 1 | [isi] |
| 2 | [isi] |
| 3 | [isi] |
| 4 | [isi] |
| 5 | [isi] |