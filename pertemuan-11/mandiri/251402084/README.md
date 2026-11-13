# Pertemuan 11 — Tugas Mandiri: Dari Endpoint ke Graf Lokal

## Bagian A — LIMIT dan Triple

- **Triple dengan LIMIT 10:** 30 triple.
- **Triple dengan LIMIT 30:** 90 triple.
- **Baris SELECT LIMIT 30 / kota berbeda:** 30 baris / 30 kota berbeda.
- **Mengapa tidak (atau mengapa tepat) 30 triple:** Pada percobaan LIMIT 10, query CONSTRUCT menghasilkan tepat 30 triple karena terdapat 10 solusi yang masing-masing menghasilkan tiga triple, yaitu `rdf:type`, `rdfs:label`, dan `ex:populasi`. Tidak terdapat triple identik yang digabungkan dalam hasil tersebut. LIMIT membatasi jumlah solusi dari WHERE, bukan jumlah triple hasil CONSTRUCT. Oleh karena itu, jumlah triple dapat berbeda dari tiga kali LIMIT jika beberapa solusi menghasilkan triple yang sama.

### Bukti Screenshot

- LIMIT 10: `screenshots/a-limit10.png`
- LIMIT 30: `screenshots/a-limit30.png`
- SELECT LIMIT 30: `screenshots/a-select30.png`

## Bagian B — DBpedia ke Turtle

- **Output demo_c.py:** 48 triple, 16 film tanpa tanggal rilis.
- **Sumber data:** Endpoint DBpedia (`https://dbpedia.org/sparql`) menggunakan query `q_demo_b.rq`.

### Triple yang tetap tercetak untuk film tanpa tanggal

Setiap film tetap menghasilkan tiga triple, yaitu `rdf:type schema:Movie`, `schema:name`, dan `schema:director`. Triple `schema:datePublished` tidak dihasilkan apabila variabel `?rilis` tidak memiliki nilai karena pola `dbo:releaseDate` berada dalam blok OPTIONAL. Hal ini tidak menyebabkan error karena CONSTRUCT melewatkan triple template yang memiliki variabel tidak terikat.

### Query lokal tanpa internet

Query lokal tetap dapat dijalankan tanpa internet karena hasil CONSTRUCT dari DBpedia sudah disimpan ke dalam berkas `film_nolan.ttl`. RDFLib membaca berkas Turtle tersebut dan menjalankan query SPARQL terhadap graf yang tersimpan secara lokal, sehingga tidak perlu menghubungi endpoint DBpedia kembali.

### Mengapa dbo:Film menghasilkan 0 pada film_nolan.ttl?

Graf yang disimpan menggunakan kelas `schema:Movie`, bukan `dbo:Film`. Walaupun keduanya berhubungan dengan konsep film, RDFLib tidak otomatis menganggap kedua kelas tersebut setara. Oleh karena itu, query yang mencari `?film a dbo:Film` tidak menemukan kecocokan pada graf lokal. Query harus menggunakan `schema:Movie` agar sesuai dengan triple yang dibentuk oleh CONSTRUCT.

### Bukti Screenshot

- Output DBpedia: `screenshots/b-dbpedia.png`

## Bagian C — Materialisasi

### Hasil Percobaan

| Pengujian | Hasil |
|---|---|
| Jumlah triple awal | 9 triple |
| Jumlah triple setelah materialisasi pertama | 13 triple |
| Jumlah triple turunan pertama | 4 triple |
| foaf:Person sebelum materialisasi | 0 individu |
| foaf:Person sesudah materialisasi | 3 individu |
| foaf:Person setelah penambahan Rina tanpa materialisasi ulang | 3 individu |
| Jumlah triple setelah Rina ditambahkan | 10 triple |
| Jumlah triple turunan setelah materialisasi ulang | 6 triple |
| Total triple akhir | 16 triple |

### Mengapa Rina Belum Terhitung sebagai foaf:Person?

Rina ditambahkan ke dalam graf sebagai `ex:DosenTetap` setelah proses materialisasi pertama selesai. Walaupun kelas `ex:DosenTetap` memiliki hubungan hierarkis dengan `ex:Dosen` dan `foaf:Person`, RDFLib tidak secara otomatis menerapkan aturan penalaran ketika data baru ditambahkan. Oleh karena itu, jumlah individu bertipe `foaf:Person` tetap 3 sebelum materialisasi ulang dilakukan.

### Hasil Materialisasi Ulang

Materialisasi ulang dilakukan menggunakan query CON

## Bagian D — Diagnosis Query CONSTRUCT

Bagian ini menguji tiga query SPARQL CONSTRUCT yang memiliki masalah berbeda. Pengujian dilakukan menggunakan RDFLib terhadap data lokal `film.ttl` dan `kota.ttl`. Setiap query rusak dibandingkan dengan query perbaikannya untuk mengidentifikasi penyebab dan perubahan hasil.

### Tabel Diagnosis

| Kasus | Gejala | Penyebab | Perbaikan | Hasil Perbaikan |
|---|---|---|---|---|
| A | Query menghasilkan 0 triple | Variabel `?film` pada template CONSTRUCT tidak terikat karena WHERE menggunakan `?f` | Mengganti variabel pada template menjadi `?f` | 3 triple |
| B | Query menghasilkan 4 triple, padahal hanya terdapat 3 orang | Nilai nama yang tampak sama dapat berbeda sebagai literal RDF karena tag bahasa | Menggunakan `BIND(STR(?n) AS ?namaNormal)` untuk menormalisasi nilai nama | 3 triple |
| C | Query menghasilkan 0 triple meskipun terdapat kota dalam data | WHERE hanya mencari tipe `ex:Kota` secara langsung tanpa mengikuti hierarki subclass | Menggunakan property path `rdfs:subClassOf*` | 2 triple |

### Kasus A — Variabel Tidak Terikat

Query `rusak_a.rq` dijalankan terhadap `film.ttl` dan menghasilkan 0 triple. Penyebabnya adalah variabel `?film` pada template CONSTRUCT tidak terikat dalam bagian WHERE, yang menggunakan variabel `?f`.

Pada `perbaikan_a.rq`, variabel tersebut disamakan menjadi `?f`. Setelah diperbaiki, query menghasilkan 3 triple judul film Christopher Nolan, yaitu *Insomnia*, *Following*, dan *Memento*.

Kasus ini membuktikan bahwa triple dengan variabel tidak terikat tidak dibentuk oleh CONSTRUCT meskipun query dapat dieksekusi tanpa error sintaks.

### Kasus B — Perbedaan Tag Bahasa

Query `rusak_b.rq` dijalankan terhadap `film.ttl` menggunakan VALUES dan property path alternatif untuk mengambil nama dari beberapa predikat.

Hasil awal menunjukkan 4 triple untuk 3 orang, yaitu Christopher Nolan, Joko Anwar, dan Mira Lesmana. Christopher Nolan memiliki dua nilai nama yang tampak sama ketika ditampilkan, tetapi merupakan literal RDF berbeda karena informasi bahasa.

Pada `perbaikan_b.rq`, fungsi `STR()` digunakan untuk mengambil bentuk teks dari nilai nama tanpa tag bahasa. Hasil CONSTRUCT kemudian menggabungkan triple identik sehingga diperoleh 3 triple, tepat satu nama untuk setiap orang pada data contoh.

### Kasus C — Hierarki Subclass

Query `rusak_c.rq` dijalankan terhadap `kota.ttl` dan menghasilkan 0 triple karena hanya mencari individu yang secara eksplisit bertipe `ex:Kota`.

Padahal Medan memiliki tipe `ex:KotaMetropolitan` dan `ex:KotaDiIndonesia`, sedangkan Surabaya bertipe `ex:KotaDiIndonesia`. Kedua kelas tersebut merupakan subclass dari `ex:Kota`.

Perbaikan dilakukan menggunakan jalur `rdfs:subClassOf*` untuk menelusuri hubungan kelas secara tidak langsung. Setelah diperbaiki, query menghasilkan 2 triple:

```turtle
ex:Medan a ex:Kota .
ex:Surabaya a ex:Kota .
```

Tomok tidak dihasilkan karena termasuk `ex:Desa`, bukan subclass dari `ex:Kota`.

### Kesimpulan Diagnosis

Pengujian menunjukkan bahwa keberhasilan query CONSTRUCT dipengaruhi oleh keterikatan variabel, perbedaan nilai literal RDF, dan struktur hierarki kelas. Query yang valid secara sintaks belum tentu menghasilkan graf yang sesuai tujuan. Oleh karena itu, hasil CONSTRUCT harus diperiksa berdasarkan jumlah dan isi triple yang dihasilkan.

### Berkas Pengujian

- `rusak_a.rq` dan `perbaikan_a.rq` — variabel tidak terikat.
- `rusak_b.rq` dan `perbaikan_b.rq` — normalisasi nama.
- `rusak_c.rq` dan `perbaikan_c.rq` — hierarki subclass.
- `jalankan.py` — program untuk menjalankan query pada graf RDF lokal.


## Bagian E — Etika dan Diskusi

### E1. Etika Penggunaan Endpoint Publik

| No. | Aturan | a_kota_limit.rq | demo_c.py |
|---|---|---|---|
| 1 | User-Agent bermakna | Tidak relevan secara langsung karena query dijalankan melalui antarmuka browser Wikidata Query Service. Identitas permintaan dikelola oleh browser dan layanan tersebut. | Memenuhi. Program menggunakan User-Agent `KelasWebSemantik/1.0` dengan alamat kontak valid untuk mengidentifikasi klien. |
| 2 | LIMIT saat eksplorasi | Memenuhi. Query menggunakan `LIMIT 10` untuk membatasi jumlah solusi. Percobaan tambahan dilakukan dengan `LIMIT 30`. | Belum memenuhi secara eksplisit. Query pada `q_demo_b.rq` tidak menggunakan LIMIT, tetapi membatasi pencarian pada film yang disutradarai Christopher Nolan. |
| 3 | Simpan, jangan ulangi | Tidak diterapkan pada file query ini. Hasil percobaan didokumentasikan melalui screenshot, tetapi graf hasil Wikidata tidak disimpan sebagai file Turtle lokal. | Memenuhi. Program menyimpan 48 triple ke `film_nolan.ttl`, kemudian membaca kembali graf tersebut untuk menjalankan query lokal tanpa menghubungi DBpedia. |
| 4 | Jangkar jalur | Memenuhi. Property path `wdt:P31/wdt:P279* wd:Q515` menggunakan `wd:Q515` sebagai jangkar kelas kota sehingga penelusuran hierarki memiliki tujuan yang spesifik. | Tidak relevan. Query DBpedia menggunakan pola `dbo:director dbr:Christopher_Nolan` dan tidak menggunakan property path `*`. |

### Contoh Query yang Melanggar Aturan Keempat

Berikut adalah contoh query yang tidak mengikuti prinsip penggunaan jangkar pada property path:

```sparql
SELECT ?awal ?akhir
WHERE {
    ?awal wdt:P279* ?akhir .
}
```

Query tersebut menggunakan property path `wdt:P279*` dengan kedua ujung berupa variabel yang tidak dibatasi. Akibatnya, endpoint berpotensi harus menelusuri banyak hubungan subclass dalam graf Wikidata, termasuk pasangan simpul yang terhubung oleh jalur dengan panjang nol atau lebih.

Query ini tidak menggunakan jangkar berupa IRI tertentu, sehingga ruang pencariannya menjadi sangat luas dan berisiko membebani endpoint publik. Query tersebut hanya dituliskan sebagai contoh pelanggaran dan tidak dijalankan.

### Kesimpulan Etika

Penggunaan endpoint SPARQL publik harus mempertimbangkan efisiensi query dan beban server. Penerapan LIMIT, identitas klien yang jelas, penyimpanan hasil secara lokal, dan penggunaan property path yang memiliki jangkar merupakan langkah untuk mengurangi permintaan yang tidak diperlukan. Berdasarkan hasil tugas ini, query lokal juga terbukti memungkinkan pemrosesan data RDF tanpa harus terus-menerus mengakses endpoint publik.

### Diskusi 1 — Taksonomi Berubah

Materialisasi menghasilkan triple eksplisit berdasarkan hierarki kelas pada saat aturan dijalankan. Pada percobaan C1, graf `kampus.ttl` awalnya berisi 9 triple. Setelah materialisasi, `kampus_v4.ttl` memiliki 13 triple, termasuk 4 triple turunan. Salah satunya adalah `ex:budi rdf:type foaf:Person`, yang diperoleh melalui hubungan subclass.

Pada percobaan C2, Rina ditambahkan sebagai `ex:DosenTetap`. Setelah materialisasi ulang, jumlah triple turunan meningkat menjadi 6 dan graf akhir berisi 16 triple. Dua kesimpulan tambahan adalah `ex:rina rdf:type ex:Dosen` dan `ex:rina rdf:type foaf:Person`.

Jika setahun kemudian taksonomi berubah, misalnya hubungan `ex:Dosen rdfs:subClassOf foaf:Person` dihapus, triple hasil materialisasi yang bergantung pada hubungan tersebut dapat menjadi tidak valid. Contohnya adalah pernyataan bahwa Budi dan Rina merupakan `foaf:Person`, apabila tidak terdapat jalur pewarisan lain yang mendukungnya.

Untuk menemukan triple yang sudah tidak didukung, tim perlu membedakan data asli dan hasil materialisasi, kemudian menjalankan ulang aturan menggunakan taksonomi terbaru. Hasil penalaran baru dibandingkan dengan triple turunan lama untuk mengidentifikasi kesimpulan yang tidak lagi dapat diturunkan.

Berkas `kampus_v4.ttl` dan `kampus_turunan.ttl` menunjukkan bahwa hasil materialisasi bergantung pada kondisi graf dan waktu pelaksanaan aturan. Oleh karena itu, setiap perubahan taksonomi memerlukan pemeriksaan ulang, penghapusan kesimpulan yang tidak lagi didukung, serta pembaruan graf produksi.

### Diskusi 2 — SELECT atau CONSTRUCT

Untuk membuat dashboard populasi kota, saya memilih SELECT karena hasilnya berupa tabel yang dapat digunakan langsung untuk menampilkan nama kota dan jumlah penduduk. Pada Bagian A, SELECT dengan LIMIT 30 menghasilkan 30 baris yang mewakili 30 kota berbeda. Sementara itu, CONSTRUCT LIMIT 10 menghasilkan 30 triple dan LIMIT 30 menghasilkan 90 triple. Hasil ini membuktikan bahwa LIMIT membatasi jumlah solusi, bukan jumlah triple hasil CONSTRUCT.

Meskipun pada sampel SELECT tersebut tidak ditemukan kota berulang, data lokal `kota.ttl` menunjukkan bahwa Medan memiliki dua nilai populasi, yaitu 2.435.252 dan 2.097.610. Hal tersebut memperlihatkan pentingnya memperhatikan kemungkinan beberapa nilai populasi untuk satu kota.

Untuk menggabungkan data kota dari tiga sumber, saya memilih CONSTRUCT karena dapat mengubah kosakata berbeda menjadi struktur RDF yang seragam, misalnya menggunakan `ex:Kota`, `rdfs:label`, dan `ex:populasi`.

Pengujian Bagian D Kasus B juga menunjukkan masalah integrasi data akibat literal dengan tag bahasa berbeda. Query awal menghasilkan 4 triple untuk 3 orang. Setelah normalisasi menggunakan `BIND(STR(?n) AS ?namaNormal)`, hasilnya menjadi 3 triple karena nilai nama yang identik digabungkan.

Selain itu, Bagian D Kasus C menunjukkan bahwa `rdfs:subClassOf*` dapat menemukan Medan dan Surabaya sebagai kota meskipun tipenya dinyatakan melalui subclass.

Dengan demikian, CONSTRUCT lebih sesuai untuk mengintegrasikan dan menyeragamkan graf RDF dari beberapa sumber, sedangkan SELECT lebih tepat untuk mengambil data yang akan ditampilkan pada dashboard.

### Cek Diri

| Pernyataan | Bukti dari Tugas Ini |
|---|---|
| 1. CONSTRUCT mencetak template untuk setiap solusi dan menggabungkan triple kembar. | Bagian A: LIMIT 10 menghasilkan 30 triple dan LIMIT 30 menghasilkan 90 triple dengan tiga pola template. Bagian D Kasus B: setelah normalisasi nama, 4 triple berkurang menjadi 3 karena triple identik digabungkan. |
| 2. Triple dengan variabel tidak terikat tidak dicetak, tanpa error. | Bagian D Kasus A: `rusak_a.rq` menghasilkan 0 triple karena variabel `?film` tidak terikat. Setelah diperbaiki menjadi `?f`, query menghasilkan 3 triple judul film Nolan. |
| 3. UNION, jalur, dan VALUES menyatukan predikat berbeda. | Bagian D Kasus B: VALUES membatasi tiga individu, sedangkan property path alternatif `foaf:name\|rdfs:label\|schema:name` memungkinkan nama diambil melalui predikat berbeda. UNION memiliki fungsi menggabungkan alternatif pola pencocokan, tetapi belum diuji secara terpisah pada revisi final. |
| 4. Jalur `+` dan `*` menelusuri hierarki tanpa menyebut perantara. | Bagian C: jalur `rdfs:subClassOf*` menghasilkan 4 triple turunan pada putaran pertama dan total 6 setelah Rina ditambahkan. Bagian D Kasus C: penggunaan `rdfs:subClassOf*` menghasilkan 2 triple untuk Medan dan Surabaya. Operator `+` memiliki fungsi serupa, tetapi mensyaratkan minimal satu langkah dan belum diuji langsung. |
| 5. Materialisasi menyimpan kesimpulan, tetapi perlu dihitung ulang ketika data atau taksonomi berubah. | Bagian C: `kampus_v4.ttl` memiliki 13 triple, termasuk 4 triple turunan. Setelah Rina ditambahkan, jumlah individu `foaf:Person` tetap 3 sebelum materialisasi ulang. Setelah aturan dijalankan kembali, `kampus_turunan.ttl` memiliki 16 triple, termasuk 6 triple turunan. |

### Kesimpulan Akhir

Melalui tugas ini, saya memahami bahwa CONSTRUCT membentuk graf RDF berdasarkan solusi WHERE, sedangkan SELECT menghasilkan data tabular. Pengujian juga menunjukkan pentingnya normalisasi literal, penelusuran hierarki subclass, materialisasi ulang, serta penggunaan endpoint publik secara efisien. Hasil percobaan disimpan dalam file Turtle dan didokumentasikan agar dapat diuji kembali secara lokal.