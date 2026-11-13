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
- Output demo_d.py: `Sebelum: 0`, `Sesudah: 3`, `Ada Rina: 3`
- Mengapa Ada Rina bernilai 3: Karena `CONSTRUCT` hanya melakukan materialisasi satu kali sebelum data `ex:rina a ex:DosenTetap` ditambahkan ke dalam graf `g`. Ketika fakta baru tentang `ex:rina` diparsing setelahnya, aturan materialisasi `CONSTRUCT { ?x a ?super } WHERE { ?x a/rdfs:subClassOf* ?super }` tidak dijalankan ulang, dan graf lokal RDFLib tanpa *reasoner* tidak secara otomatis menurunkan triple `ex:rina a ex:Dosen` maupun `ex:rina a foaf:Person`. Akibatnya, query `Q_ORANG` yang hanya mencari pola eksplisit `?x a foaf:Person` tetap hanya menemukan 3 individu lama (`ex:budi`, `ex:sari`, dan `ex:andi`).
- Output materialisasi_ulang.py: `Putaran 1: 4 triple baru | Person: 3`, `Rina masuk | Person: 3`, `Putaran 2 | Person: 4`, `Triple turunan disimpan terpisah: 6`
- Tiga triple yang bukan baru: Tiga triple yang sudah ada sejak awal di dalam `kampus.ttl` adalah `ex:budi a ex:DosenTetap`, `ex:sari a ex:Dosen`, dan `ex:andi a ex:Mahasiswa`. Ketiganya tetap tercetak oleh query `ATURAN` karena bagian `*` (*zero or more steps*) pada jalur properti `a/rdfs:subClassOf*` menyertakan jalur sepanjang nol langkah (`rdfs:subClassOf` nol kali), sehingga setiap individu juga dipasangkan kembali dengan kelas asalnya sendiri (`?super` terikat ke kelas langsung milik `?x`).
- Kegunaan kampus_turunan.ttl dan langkah manual yang tersisa: Menyimpan triple hasil inferensi secara terpisah di `kampus_turunan.ttl` memudahkan audit asal-usul data (*provenance*) serta memungkinkan kita menghapus atau membangun ulang seluruh triple turunan secara bersih tanpa merusak fakta dasar di `kampus.ttl` ketika aturan taksonomi berubah. Jika suatu hari `ex:Dosen` tidak lagi menjadi subkelas `foaf:Person`, langkah manual yang tetap harus dilakukan adalah menghapus triple `ex:Dosen rdfs:subClassOf foaf:Person` dari ontologi/graf utama, membuang berkas `kampus_turunan.ttl` yang lama, lalu menjalankan ulang proses materialisasi dari awal pada graf fakta dasar (`kampus.ttl` + data baru seperti Rina) agar kesimpulan usang seperti `ex:budi a foaf:Person`, `ex:sari a foaf:Person`, dan `ex:rina a foaf:Person` tidak tertinggal.

## Bagian D - Diagnosis

| Query | Hasil Rusak | Gejala | Penyebab (Baris Salah) | Hasil Setelah Perbaikan |
| :--- | :--- | :--- | :--- | :--- |
| `rusak_a.rq` | `0 triple` | Graf kosong, tanpa error | Pada baris `CONSTRUCT { ?film ex:judul ?j . }`, nama variabel subjek ditulis `?film` sedangkan di klausa `WHERE` menggunakan `?f`, sehingga `?film` tidak pernah terikat (*unbound*) dan seluruh triple di templat dilewati. | `3 triple` (`dbr:Following`, `dbr:Insomnia`, `dbr:Memento`) |
| `rusak_b.rq` | `4 triple` | Dua nilai yang tampak sama | Pada baris `?o foaf:name|rdfs:label|schema:name ?n .`, `dbr:Christopher_Nolan` memiliki dua literal berbeda tipe/tag bahasa (`"Christopher Nolan"` tanpa tag bahasa pada `foaf:name` dan `"Christopher Nolan"@en` bertag bahasa pada `rdfs:label`), sehingga RDFLib menganggap keduanya dua node RDF berbeda dan tidak menggabungkannya. | `3 triple` (`dbr:Christopher_Nolan`, `dbr:Joko_Anwar`, `dbr:Mira_Lesmana`) |
| `rusak_c.rq` | `0 triple` | Superkelas tidak ditemukan | Pada baris `?x a ex:Permukiman .` di dalam `WHERE`, query hanya mencari instans langsung `ex:Permukiman` tanpa jalur properti `a/rdfs:subClassOf*`, padahal individu di `kota.ttl` bertipe subkelasnya (`ex:KotaMetropolitan`, `ex:KotaDiIndonesia`, `ex:Desa`) dan graf belum dimaterialisasi. | `3 triple` (`ex:Medan`, `ex:Surabaya`, `ex:Tomok`) |


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