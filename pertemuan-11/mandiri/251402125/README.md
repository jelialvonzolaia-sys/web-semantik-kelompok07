# Pertemuan 11 - Tugas Mandiri: Dari Endpoint ke Graf Lokal

## Bagian A - LIMIT dan triple
- Triple dengan LIMIT 10: 30 triple
- Triple dengan LIMIT 30: 90 triple
- Baris SELECT LIMIT 10 / kota berbeda: 10 / 10
- Mengapa tidak (atau mengapa tepat) 30 triple: Tepat 30 triple karena `LIMIT 10` membatasi 10 solution dari `WHERE`, dan saat dijalankan kebetulan ke-10 baris pertama isinya kota yang berbeda semua (masing-masing cuma punya 1 nilai populasi dan 1 label ID). Jadi tidak ada triple kembar yang terbuang, dan 3 pola `CONSTRUCT` langsung menghasilkan 10 X 3 = 30 triple. 

## Bagian B - DBpedia ke Turtle
- Output demo_c.py: 48 triple, film tanpa tanggal: 16 (terdapat 16 film Christopher Nolan dan seluruhnya tidak memiliki properti tanggal rilis schema:datePublished, sehingga masing-masing film mencetak tepat 3 triple: 16 X 3 = 48 triple)
- Sumber data: DBpedia
- Triple yang tetap tercetak untuk film tanpa tanggal: Tiga triple yang tercetak adalah `?film a schema:Movie`, `?film schema:name ?judul`, dan `?film schema:director dbr:Christopher_Nolan`. Sementara triple `?film schema:datePublished ?rilis` otomatis tidak dicetak. Tidak muncul pesan error karena di SPARQL, variabel `?rilis` berada di blok `OPTIONAL`, ketika variabel di template `CONSTRUCT` unbound, SPARQL otomatis mengabaikan pola triple tersebut tanpa menggagalkan proses konstruksi triple lainnya.
- Query lokal tanpa internet: Query lokalnya tetap berhasil karena `film_nolan.ttl` sudah tersimpan di komputer dan dibaca langsung secara offline oleh `rdflib`. Kalau seluruh `demo_c.py` dijalankan ulang tanpa internet, program langsung error saat memanggil `sp.queryAndConvert()` karena tidak bisa terhubung ke endpoint DBpedia.
- Mengapa dbo:Film memberi 0 pada film_nolan.ttl: Karena di query `CONSTRUCT` sebelumnya, semua `dbo:Film` sudah diubah jadi `schema:Movie`. Di file `film_nolan.ttl` sudah tidak ada lagi triple `dbo:Film`, jadi hasilnya 0.

## Bagian C - Materialisasi
- Output demo_d.py: 

  Sebelum: 0 
  Sesudah: 3  
  Ada Rina: 3
- Mengapa Ada Rina bernilai 3: Karena `CONSTRUCT` hanya dijalankan sekali sebelum data Rina masuk. Setelah `ex:rina a ex:DosenTetap` ditambahkan, triple `ex:rina a foaf:Person` belum terbuat karena aturan materialisasinya belum di-run ulang.
- Output materialisasi_ulang.py: 

  Putaran 1: 4 triple baru | Person: 3
  Rina masuk | Person: 3
  Putaran 2 | Person: 4
  Triple turunan disimpan terpisah: 6
- Tiga triple yang bukan baru: Tiga triple `rdf:type` asli milik 3 orang awal yang sudah ada di `kampus.ttl`. Ikut tercetak karena tanda `*` pada `a/rdfs:subClassOf*` juga mengambil kelas aslinya sendiri (0 langkah).
- Kegunaan kampus_turunan.ttl dan langkah manual yang tersisa: Data asli di `kampus.ttl` tetap bersih karena triple turunannya dipisah, jadi kalau aturan subkelas berubah kita tidak perlu repot menghapus triple lama dari file utama. Manualnya tinggal hapus aturan `rdfs:subClassOf` di `kampus.ttl`, lalu generate ulang file `kampus_turunan.ttl`.

## Bagian D - Diagnosis
| Query | Hasil mesin | Gejala | Penyebab (baris yang salah) | Hasil setelah diperbaiki |
|-------|-------------|--------|-----------------------------|--------------------------|
| rusak_a | 0 tripel | Graf kosong, tanpa error | Variabel di baris `CONSTRUCT { ?film ex:judul ?j . }` beda nama dengan di `WHERE { ?f dbo:director ... } (?film` vs `?f)`, jadi `?film` tidak dapat nilai apa pun. | 3 triple
  `dbr:Following ex:judul "Following"@en .`
  `dbr:Insomnia ex:judul "Insomnia"@en .`
  `dbr:Memento ex:judul "Memento"@en .` |
| rusak_b | 4 tripel | Dua nilai yang tampak sama | Pada baris `?o foaf:name|rdfs:label|schema:name ?n .`, nama Christopher Nolan diambil dari dua properti berbeda dengan format tag bahasa beda ("Christopher Nolan" dan "Christopher Nolan"@en), jadi dianggap 2 nilai berbeda oleh RDF | 3 triple
  `dbr:Christopher_Nolan ex:nama "Christopher Nolan" .`
  `dbr:Joko_Anwar ex:nama "Joko Anwar" .`
  `dbr:Mira_Lesmana ex:nama "Mira Lesmana" .` |
| rusak_c | 0 tripel | Superkelas tidak ditemukan | Baris `?x a ex:Permukiman .` di dalam `WHERE` tidak pakai jalur `rdfs:subClassOf*`, sehingga kota yang bertipe subkelas di bawah `ex:Permukiman` tidak ikut ketemu | 3 triple
  `ex:Medan rdf:type ex:Permukiman .`
  `ex:Surabaya rdf:type ex:Permukiman .`
  `ex:Tomok rdf:type ex:Permukiman .` |

## Bagian E - Etika dan diskusi
| Aturan | a_kota_limit.rq | demo_c.py |
|--------|-----------------|-----------|
| 1 User-Agent | Tidak perlu ditulis di file `.rq` karena query ini dijalankan manual lewat browser (`query.wikidata.org`), jadi User-Agent otomatis dikirim oleh browser | Ada di baris `UA = "KelasWebSemantik/1.0 (email@kampus.ac.id)"` (diganti email sendiri) lalu dipasang di `SPARQLWrapper(..., agent=UA)` |
| 2 LIMIT | Ada di baris terakhir `LIMIT 10` (dan diubah jadi `LIMIT 30` saat tes kedua) supaya datanya tidak ditarik semua sekaligus. | Tidak wajib pakai LIMIT karena query `q_demo_b.rq` sudah khusus mencari film dari satu sutradara saja (`dbr:Christopher_Nolan`) yang jumlahnya memang cuma belasan |
| 3 Simpan, jangan ulangi | Tidak berlaku di sini karena file ini cuma berisi teks query yang di-copy ke browser, bukan script untuk menyimpan file | Ada di baris `g.serialize("film_nolan.ttl", format="turtle")`, terus query kedua (`Q_TANPA_TANGGAL`) langsung baca file lokal itu lewat `Graph().parse("film_nolan.ttl")` |
| 4 Jangkar jalur | Memenuhi di baris `?kota wdt:P31/wdt:P279* wd:Q515 ;` karena tanda `*` berujung jelas ke IRI `wd:Q515` (kota), bukan ke variabel bebas | Aman karena di `q_demo_b.rq` sama sekali tidak pakai jalur `*` atau `+`, dan polanya langsung mengarah ke IRI jelas seperti `dbr:Christopher_Nolan` |
- Versi yang melanggar aturan 4 (tidak dijalankan) dan alasannya:

```sparql
PREFIX ex: <http://contoh.id/>
CONSTRUCT {
  ?kota a ex:Kota ;
        rdfs:label ?namaKota ;
        ex:populasi ?populasi .
}
WHERE {
  ?kota wdt:P31/wdt:P279* ?jenisKota ;
        wdt:P17 wd:Q252 ;
        wdt:P1082 ?populasi ;
        rdfs:label ?namaKota .
  FILTER ( LANG(?namaKota) = "id" )
}
LIMIT 10
```

Alasan : Ujung wd:Q515 diganti jadi variabel bebas ?jenisKota, sehingga wdt:P279* tidak punya patokan IRI. Endpoint Wikidata jadi harus menelusuri semua subkelas dari seluruh entitas di Indonesia yang bikin bebannya sangat berat dan rawan timeout.

### Diskusi 1 - Taksonomi berubah
Kalau hasil materialisasi langsung disimpan ke graf produksi lalu setahun kemudian aturan kelasnya berubah (misalnya `ex:Dosen` dihapus dari subkelas `foaf:Person`), maka triple yang jadi salah adalah triple hasil turunan seperti `ex:budi a foaf:Person` atau `ex:rina a foaf:Person` yang dulu muncul lewat jalur `a/rdfs:subClassOf*`. Triple ini tidak valid lagi karena data aslinya cuma mencatat mereka sebagai dosen, bukan `foaf:Person`.   

Kalau pakai cara` kampus_v4.ttl` di bagian C1, mencari dan menghapus triple yang salah ini bakal susah karena data asli dari `kampus.ttl` dan 3 triple hasil turunan langsung digabung jadi satu file. Bisa jadi bingung membedakan mana `foaf:Person` yang memang diinput manual dari awal dan mana yang muncul gara-gara aturan lama. Beda ceritanya kalau `kampus_turunan.ttl` di bagian C2 yang memisahkan 6 triple turunan ke file sendiri. Di situ semua hasil inferensi kelihatan jelas dan tidak tercampur data utama. Cara memperbaikinya tinggal hapus aturan `rdfs:subClassOf` yang lama di file utama, hapus `kampus_turunan.ttl` yang lama, lalu jalankan ulang `materialisasi_ulang.py` supaya file turunannya ke-generate ulang sesuai struktur baru

### Diskusi 2 - SELECT atau CONSTRUCT
Untuk menampilkan dashboard populasi kota langsung ke tabel atau grafik, bentuk query yang dipakai adalah `SELECT`. Alasannya, komponen dashboard butuh data dalam bentuk tabel baris dan kolom (nama kota dan jumlah populasinya), seperti hasil 10 baris pada percobaan `SELECT` di Bagian A. Dengan `SELECT`, hasil query bisa langsung dibaca oleh antarmuka tanpa perlu repot dibentuk ulang menjadi graf RDF.   

Tapi, jika tujuannya menggabungkan data kota dari tiga sumber berbeda terlebih dahulu, maka `CONSTRUCT` jauh lebih tepat digunakan. Tiap sumber data umumnya memakai kosakata dan format penulisan yang berbeda. Hal ini terlihat pada Bagian B ketika skema `dbo:Film` harus diubah menjadi `schema:Movie`, maupun pada kasus `rusak_b.rq` yang menghasilkan 4 triple karena nama `"Christopher Nolan"` terhitung dua kali akibat perbedaan tag bahasa (`"Christopher Nolan"` dan `"Christopher Nolan"@en`). Selain itu, pengujian Bagian A juga menunjukkan bahwa jika ada data populasi ganda, `CONSTRUCT` otomatis menggabungkan triple yang kembar seperti tipe dan label kota. Lewat `CONSTRUCT`, seluruh kosakata dan format dari ketiga sumber bisa diseragamkan ke satu file graf lokal yang bersih sebelum dipakai dashboard. 

### Cek diri
| Pernyataan | Bukti dari tugas ini |
|------------|----------------------|
| 1 | Bagian A (`a_kota_limit.rq`): Template `CONSTRUCT` memiliki 3 pola (`a ex:Kota, rdfs:label`, dan `ex:populasi`). Saat dijalankan dengan `LIMIT 10`, hasilnya 30 triple (10 X 3) karena ke-10 baris pada `SELECT` `LIMIT 10` kebetulan merupakan 10 kota berbeda tanpa data ganda. Saat diubah menjadi `LIMIT 30`, hasilnya tepat 90 triple (30 X 3) dari 30 kota berbeda sehingga seluruh triple uniknya tercetak utuh |
| 2 | Bagian B (`q_demo_b.rq & demo_c.py`): Pada `film_nolan.ttl`, seluruh 16 film Christopher Nolan tidak memiliki properti `dbo:releaseDate` sehingga variabel `?rilis` di dalam blok `OPTIONAL` unbound. Query tetap berjalan tanpa error dan mencetak 48 triple (16 X 3 : `a schema:Movie`, `schema:name`, dan `schema:director`), sedangkan pola `schema:datePublished ?rilis` otomatis dilewati.  |
| 3 | Bagian D (`rusak_b.rq & perbaikan_b.rq`): Klausa `VALUES ?o` membatasi 3 sutradara (`dbr:Christopher_Nolan`, `dbr:Joko_Anwar`, `dbr:Mira_Lesmana`), dan jalur `foaf:name, rdfs:label, schema:name` menggabungkan tiga predikat nama berbeda dari `film.ttl`. Hasilnya turun dari 4 triple (karena beda tag bahasa pada nama Christopher Nolan) menjadi 3 triple (1 `ex:nama` per orang) setelah diperbaiki. |
| 4 | Bagian C (`demo_d.py`) & Bagian D (`perbaikan_c.rq`): Jalur `a/rdfs:subClassOf*` menelusuri hierarki kelas tanpa menyebut kelas perantara. Di `kampus.ttl`, jumlah `foaf:Person` naik dari 0 menjadi 3 (menghasilkan 7 triple di Putaran 1 `materialisasi_ulang.py`: 3 fakta awal + 4 triple baru). Di `kota.ttl`, `perbaikan_c.rq` menaikkan hasil `ex:Permukiman` dari 0 menjadi 3 triple (Medan, Surabaya, Tomok) |
| 5 | Bagian C (`demo_d.py vs materialisasi_ulang.py`): Pada `demo_d.py`, setelah materialisasi awal menghasilkan 3 `foaf:Person`, penambahan data `ex:rina` membuat jumlah Person tetap 3 karena aturan tidak otomatis jalan ulang. Pada `materialisasi_ulang.py`, Putaran 1 menghasilkan 4 triple baru (Person: 3), saat Rina masuk tetap Person: 3, dan baru setelah Putaran 2 dijalankan ulang jumlahnya naik menjadi Person: 4 dengan total 6 triple turunan di `kampus_turunan.ttl`. |
