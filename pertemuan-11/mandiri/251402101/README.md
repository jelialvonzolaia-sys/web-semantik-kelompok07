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
### E1. Etika Endpoint

**1. Pemeriksaan `a_kota_limit.rq` dan `demo_c.py` terhadap Empat Aturan Endpoint:**

| Aturan | `a_kota_limit.rq` | `demo_c.py` |
| :--- | :--- | :--- |
| **1. User-Agent bermakna** | Tidak relevan pada berkas `.rq` karena header HTTP `User-Agent` dikirim otomatis oleh antarmuka web browser saat mengakses Wikidata Query Service. | **Terpenuhi** pada baris `UA = "KelasWebSemantik/1.0 (jelialvonzolaia@gmail.com)"` dan `sp = SPARQLWrapper("https://dbpedia.org/sparql", agent=UA)`. |
| **2. LIMIT saat eksplorasi** | **Terpenuhi** pada baris terakhir `LIMIT 10` yang membatasi hasil menjadi 10 *solution* (30 triple) saat eksplorasi data kota. | Tidak relevan karena kueri menyaring satu sutradara spesifik (`dbo:director dbr:Christopher_Nolan`) yang himpunan hasilnya sudah kecil dan terukur (16 film / 48 triple). |
| **3. Simpan, jangan ulangi** | Tidak relevan secara otomatis di berkas `.rq` karena kueri dijalankan interaktif di browser tanpa skrip penyimpanan lokal. | **Terpenuhi** pada baris `g.serialize("film_nolan.ttl", format="turtle")` yang menyimpan graf hasil DBpedia ke penyimpanan lokal satu kali, lalu baris `lokal = Graph().parse("film_nolan.ttl")` yang menjalankan kueri lanjutan (`Q_TANPA_TANGGAL`) secara luring tanpa mengulang permintaan ke server. |
| **4. Jangkar jalur** | **Terpenuhi** pada baris `?kota wdt:P31/wdt:P279* wd:Q515 ;` karena jalur transitif `wdt:P279*` berjangkar pada IRI kelas spesifik `wd:Q515` (*city*), bukan diapit dua variabel bebas. | Tidak relevan karena kueri `q_demo_b.rq` yang dimuat oleh `demo_c.py` tidak menggunakan operator jalur properti (`*` maupun `+`). |

**2. Versi `a_kota_limit.rq` yang Melanggar Aturan 4 & Mengapa Jauh Lebih Mahal:**

```sparql
# CONTOH BURUK — JANGAN DIJALANKAN DI ENDPOINT
CONSTRUCT {
  ?kota a [http://contoh.id/Kota](http://contoh.id/Kota) ;
        rdfs:label ?namaKota ;
        [http://contoh.id/populasi](http://contoh.id/populasi) ?populasi .
}
WHERE {
  ?kota wdt:P31/wdt:P279* ?kelasKota ;
        wdt:P17 wd:Q252 ;
        wdt:P1082 ?populasi ;
        rdfs:label ?namaKota .
  FILTER ( LANG(?namaKota) = "id" )
}
LIMIT 10
```

### Diskusi 1 - Taksonomi berubah
Jika hasil materialisasi dari Demo D disimpan langsung ke graf produksi dan setahun kemudian aturan taksonomi berubah—misalnya `ex:Dosen` tidak lagi menjadi subkelas `foaf:Person` atau `ex:DosenTetap` dipisahkan dari `ex:Dosen`—maka triple tipe turunan seperti `ex:budi a ex:Dosen`, `ex:budi a foaf:Person`, `ex:sari a foaf:Person`, serta `ex:rina a ex:Dosen` dan `ex:rina a foaf:Person` menjadi salah. Masalah utamanya adalah sulit menemukan mana saja triple yang harus dihapus karena fakta eksplisit dan fakta hasil inferensi sudah tercampur dalam satu berkas. Pada `kampus_v4.ttl` dari bagian C1, seluruh triple dasar dari `kampus.ttl` (seperti `ex:budi a ex:DosenTetap`) digabung menjadi satu berkas dengan triple hasil `CONSTRUCT` (`ex:budi a foaf:Person`), sehingga tidak ada penanda asal-usul (*provenance*) untuk membedakan mana fakta asli dan mana kesimpulan lama. Sebaliknya, `kampus_turunan.ttl` dari bagian C2 menyimpan 6 triple hasil inferensi secara terpisah dari fakta dasar `kampus.ttl`. Dengan pemisahan seperti di `kampus_turunan.ttl`, tim cukup menghapus berkas turunan yang usang, memperbarui relasi `rdfs:subClassOf` pada ontologi inti, lalu menjalankan ulang materialisasi pada fakta dasar agar graf produksi kembali konsisten.

### Diskusi 2 - SELECT atau CONSTRUCT
Untuk kebutuhan tampilan langsung dashboard populasi kota dari satu sumber data, kueri `SELECT` lebih tepat karena langsung menghasilkan data tabular (baris dan kolom) yang siap dirender ke grafik atau tabel antarmuka tanpa beban serialisasi graf RDF. Namun, pada percobaan Bagian A (`LIMIT 10`) terlihat bahwa `SELECT ?kota ?namaKota ?populasi` mengembalikan 10 baris mentah (*solution*), di mana pada kueri tanpa pembatasan khusus satu kota di Wikidata dapat muncul berulang kali apabila memiliki lebih dari satu catatan populasi dari sensus berbeda, sehingga aplikasi tetap perlu melakukan agregasi atau pemfilteran. Sebaliknya, untuk menggabungkan data kota dari tiga sumber berbeda ke dalam satu model data terpadu, `CONSTRUCT` jauh lebih unggul karena mampu memetakan berbagai kosakata sumber yang heterogen menjadi satu skema target yang seragam. Bukti pentingnya tahap normalisasi dalam `CONSTRUCT` terlihat pada `rusak_b.rq` di Bagian D: tanpa penyelarasan bentuk literal, `"Christopher Nolan"` tanpa tag bahasa (`foaf:name`) dan `"Christopher Nolan"@en` (`rdfs:label`) dianggap dua node RDF terpisah sehingga menghasilkan `4` triple alih-alih `3`. Setelah dinormalisasi dengan `BIND(STR(?n) AS ?nama)` pada `perbaikan_b.rq`, `CONSTRUCT` otomatis melebur duplikat menjadi `3` triple bersih—mekanisme yang krusial saat menyatukan data kota dari tiga sumber eksternal.

### Cek diri
| Pernyataan | Bukti dari tugas ini |
|------------|----------------------|
| 1 | Pada **Bagian A (`a_kota_limit.rq`)**, `LIMIT 10` menghasilkan 10 *solution* dan tepat `30` triple (3 pola triple per *solution*), serta pada **Bagian D (`perbaikan_b.rq`)**, 4 baris *solution* dilebur menjadi `3` triple setelah duplikat `"Christopher Nolan"` disatukan. |
| 2 | Pada **Bagian B2 (`demo_c.py` / `film_nolan.ttl`)**, ke-16 film Christopher Nolan tidak memiliki `dbo:releaseDate` sehingga variabel `?rilis` pada blok `OPTIONAL` tidak terikat, membuat triple `?film schema:datePublished ?rilis` dilewati tanpa error (tercetak `48` triple dari $16 \times 3$, bukan 64). |
| 3 | Pada **Bagian D (`perbaikan_b.rq`)**, klausa `VALUES ?o { dbr:Christopher_Nolan dbr:Joko_Anwar dbr:Mira_Lesmana }` bersama jalur alternatif `foaf:name\|rdfs:label\|schema:name` menyatukan tiga predikat nama berbeda menjadi satu predikat `ex:nama` untuk `3` tokoh. |
| 4 | Pada **Bagian D (`perbaikan_c.rq` pada `kota.ttl`)**, pola `?x a/rdfs:subClassOf* ex:Permukiman` menemukan `3` instans (`ex:Medan`, `ex:Surabaya`, `ex:Tomok`) tanpa menyebut kelas perantara `ex:Kota`, `ex:KotaMetropolitan`, `ex:KotaDiIndonesia`, maupun `ex:Desa`. |
| 5 | Pada **Bagian C1 (`demo_d.py`)**, penambahan `ex:rina a ex:DosenTetap` setelah materialisasi awal menahan jumlah `foaf:Person` di angka `3`, dan baru naik menjadi `4` (`6` triple turunan di `kampus_turunan.ttl`) setelah `materialisasi(g)` dihitung ulang pada putaran kedua di **Bagian C2 (`materialisasi_ulang.py`)**. |
