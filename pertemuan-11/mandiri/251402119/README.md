# Pertemuan 11 - Tugas Mandiri: Dari Endpoint ke Graf Lokal

## Bagian A - LIMIT dan triple
- Triple dengan LIMIT 10: 30
- Triple dengan LIMIT 30: 90
- Baris SELECT LIMIT 10 / kota berbeda: 10 / 10
- Mengapa tidak (atau mengapa tepat) 30 triple
  
  **Jawaban:** Hasil saya tepat 30. `LIMIT` membatasi jumlah solution, bukan jumlah
  triple, dan template CONSTRUCT-nya punya 3 pola, jadi 10 solution
  menghasilkan paling banyak 10 × 3 = 30 triple. Dari tabel SELECT, kesepuluh
  barisnya berasal dari 10 kota yang berbeda (Jakarta, Tual, Soreang,
  Cikarang Pusat, Cibinong, Cianjur, Atambua, Sintang, Indramayu, dan
  Purwokerto), tidak ada kota yang muncul dua kali, sehingga tidak ada triple
  kembar yang harus digabung. Jika ada kota yang muncul dua kali (misalnya
  karena punya dua nilai populasi), pola `a ex:Kota` dan `rdfs:label` akan
  mencetak triple yang sama persis dan digabung, sehingga totalnya bisa
  kurang dari 30. Pada data saya hal itu tidak terjadi, jadi batas atasnya
  tercapai.

## Bagian B - DBpedia ke Turtle
- Output demo_c.py: 48 triple, film tanpa tanggal: 16
- Sumber data: DBpedia 
- Triple yang tetap tercetak untuk film tanpa tanggal
  
  **Jawaban:** rdf:type schema:Movie, schema:name, dan schema:director. Triple schema:datePublished tidak tercetak karena OPTIONAL tetap meloloskan film meskipun ?rilis tidak memiliki nilai (unbound). Akibatnya, triple pada template yang memuat variabel tersebut dilewati, bukan dianggap sebagai error. Pada hasil saya, terdapat 16 film tanpa tanggal, sehingga totalnya 16 × 3 = 48 triple.

- Query lokal tanpa internet
  
  **Jawaban:** Berhasil dijalankan. Query diproses oleh RDFLib di laptop menggunakan file film_nolan.ttl, sehingga tidak membutuhkan koneksi internet. Hasilnya tetap menunjukkan 16 film tanpa tanggal. Sebaliknya, jika seluruh demo_c.py dijalankan tanpa internet, program akan gagal saat mengakses DBpedia melalui sp.queryAndConvert(), sehingga proses selanjutnya tidak berjalan.
  
- Mengapa dbo:Film memberi 0 pada film_nolan.ttl
  
  **Jawaban:** Karena hasil CONSTRUCT menggunakan tipe schema:Movie, bukan dbo:Film. dbo:Film hanya digunakan pada bagian WHERE untuk mengambil data dari DBpedia dan tidak dimasukkan ke graf hasil. Oleh karena itu, query yang mencari individu bertipe dbo:Film tidak menemukan data yang cocok.

## Bagian C - Materialisasi
- Output demo_d.py: Sebelum: 0, Sesudah: 3, Ada Rina: 3
- Mengapa Ada Rina bernilai 3
  
  **Jawaban:** Materialisasi hanya berjalan sekali, yaitu sebelum Rina ditambahkan. Rina masuk hanya dengan satu triple asli (ex:rina a ex:DosenTetap), sedangkan triple turunan seperti ex:rina a ex:Dosen dan ex:rina a foaf:Person belum pernah dibuat. Query ?x a foaf:Person hanya mencocokkan triple yang benar-benar ada di graf, dan RDFLib tidak menyimpulkan sendiri bahwa ex:DosenTetap merupakan turunan dari foaf:Person. Agar Rina terhitung, aturan CONSTRUCT harus dijalankan ulang.

- Output materialisasi_ulang.py
  
  **Jawaban:** Putaran 1: 4 triple baru | Person: 3; Rina masuk | Person: 3; Putaran 2 | Person: 4; Triple turunan disimpan terpisah: 6.

- Tiga triple yang bukan baru
  
  **Jawaban:** ex:budi a ex:DosenTetap, ex:sari a ex:Dosen, dan ex:andi a ex:Mahasiswa. Ketiganya dicetak oleh bagian a dari jalur a/rdfs:subClassOf* ketika rdfs:subClassOf* berjalan nol langkah, sehingga ?super sama dengan tipe langsung individunya. Triple ini sudah ada di kampus.ttl, jadi tidak dihitung sebagai triple baru. Keempat triple baru (budi a Dosen, budi a foaf:Person, sari a foaf:Person, dan andi a foaf:Person) berasal dari satu langkah atau lebih melalui rdfs:subClassOf.

- Kegunaan kampus_turunan.ttl dan langkah manual yang tersisa
  
  **Jawaban:** Berkas ini hanya berisi triple hasil inferensi, terpisah dari data asli, sedangkan di kampus_v4.ttl triple asli dan turunan sudah bercampur. Jadi, bila suatu hari ex:Dosen tidak lagi menjadi subkelas foaf:Person, triple turunan lama bisa dikenali dan dibuang tanpa menyentuh data asli. Langkah manual yang tetap harus dilakukan adalah mengubah atau menghapus pernyataan rdfs:subClassOf di kampus.ttl, membangun ulang graf dari data asli agar triple turunan lama yang sudah tidak valid tidak ikut terbawa, kemudian menjalankan kembali materialisasi dan menyimpan hasilnya. Tidak ada proses otomatis yang mendeteksi dan menghapus triple turunan yang sudah tidak sesuai dengan aturan baru.

## Bagian D - Diagnosis
| Query | Hasil mesin | Gejala | Penyebab (baris yang salah) | Hasil setelah diperbaiki |
|-------|-------------|--------|-----------------------------|--------------------------|
| rusak_a.rq | 0 triple | Graf kosong tanpa error | Variabel `?film` pada bagian `CONSTRUCT` tidak didefinisikan di `WHERE`, karena variabel yang digunakan adalah `?f`. Akibatnya, `?film` tidak memiliki nilai sehingga triple tidak dapat dibuat. | 3 triple: Following, Insomnia, dan Memento. |
| rusak_b.rq | 4 triple | Dua nilai yang tampak sama | Baris `?o foaf:name\|rdfs:label\|schema:name ?n` mengambil literal dengan tag bahasa berbeda. Nama Christopher Nolan muncul dua kali karena satu literal memiliki tag `@en` dan satu lagi tidak. | 3 triple: Christopher Nolan, Joko Anwar, dan Mira Lesmana. |
| rusak_c.rq | 0 triple | Superkelas tidak ditemukan | Pola `?x a ex:Permukiman` hanya mencari individu yang secara langsung bertipe `ex:Permukiman`, tanpa menelusuri hubungan `rdfs:subClassOf`. | 3 triple: Medan, Surabaya, dan Tomok. |

## Bagian E - Etika dan diskusi
| Aturan | a_kota_limit.rq | demo_c.py |
|--------|-----------------|-----------|
| 1 User-Agent | Tidak diatur dalam file query karena User-Agent merupakan bagian dari HTTP request, bukan sintaks SPARQL. | Baris UA = "KelasWebSemantik/1.0 (...)" dan SPARQLWrapper("https://dbpedia.org/sparql", agent=UA). User-Agent berisi nama aplikasi dan email kontak. |
| 2 LIMIT | Baris LIMIT 10 membatasi jumlah solution yang diambil dari endpoint | Tidak menggunakan LIMIT karena query mengambil seluruh data film Nolan yang sesuai untuk disimpan. |
| 3 Simpan, jangan ulangi | Tidak menyimpan hasil karena file ini digunakan untuk menjalankan query langsung pada endpoint. | g.serialize("film_nolan.ttl", format="turtle") menyimpan hasil query. Selanjutnya, Graph().parse("film_nolan.ttl") memuat kembali data untuk diproses secara lokal. |
| 4 Jangkar jalur | Pola wdt:P31/wdt:P279* wd:Q515 menggunakan IRI wd:Q515 sebagai tujuan jalur. | Tidak relevan karena query pada file ini tidak menggunakan property path * atau +. |

- Versi yang melanggar aturan 4 (tidak dijalankan) dan alasannya:
```sparql
PREFIX ex: <http://contoh.id/>

CONSTRUCT {
  ?kota a ex:Kota ;
        rdfs:label ?namaKota ;
        ex:populasi ?populasi .
}
WHERE {
  ?kota wdt:P31 ?kelas ;
        wdt:P17 wd:Q252 ;
        wdt:P1082 ?populasi ;
        rdfs:label ?namaKota .

  ?kelas wdt:P279* ?superkelas .

  FILTER ( LANG(?namaKota) = "id" )
}
LIMIT 10
```
**Alasan:** Versi tersebut melanggar aturan 4 karena jalur wdt:P279* menghubungkan dua variabel, yaitu ?kelas dan ?superkelas, tanpa menentukan IRI tujuan. Akibatnya, endpoint perlu menelusuri berbagai kemungkinan hubungan antara kelas dan superkelas, bukan hanya mencari kelas yang mengarah ke wd:Q515 seperti pada query awal. Hal ini membuat proses pencarian menjadi lebih berat karena endpoint harus memeriksa lebih banyak kemungkinan hubungan. Walaupun sudah menggunakan LIMIT 10, batas tersebut tidak selalu membuat proses pencarian menjadi ringan karena endpoint tetap perlu mencari solution yang sesuai.

### Diskusi 1 - Taksonomi berubah
Misalnya, setahun kemudian taksonomi berubah dan ex:Dosen tidak lagi menjadi subkelas dari foaf:Person. Perubahan ini membuat beberapa triple hasil inferensi yang sebelumnya benar menjadi tidak sesuai, yaitu ex:budi a foaf:Person, ex:sari a foaf:Person, dan ex:rina a foaf:Person. Sementara itu, ex:andi a foaf:Person masih berlaku karena ex:Mahasiswa tetap menjadi subkelas foaf:Person.

Untuk mengetahuinya, saya perlu memeriksa kembali aturan hierarki dan membandingkan hasil materialisasi lama dengan hasil yang baru. Pada C1, file kampus_v4.ttl berisi data asli dan triple hasil inferensi yang sudah digabung. Karena keduanya bercampur, saya perlu membandingkannya dengan kampus.ttl untuk mengetahui triple mana yang merupakan hasil inferensi. Berbeda dengan itu, pada C2, hasil inferensi disimpan secara terpisah dalam kampus_turunan.ttl yang berisi 6 triple turunan. Pemisahan ini memudahkan saya untuk memeriksa triple yang sudah tidak sesuai dengan aturan baru.

Caranya adalah mengubah aturan pada kampus.ttl, menjalankan ulang materialisasi dari graf asli, kemudian membandingkan hasilnya dengan materialisasi sebelumnya. Triple yang sudah tidak dihasilkan lagi perlu dihapus dari graf produksi. Jadi, menyimpan hasil inferensi secara terpisah lebih memudahkan proses pemeriksaan dan pembaruan ketika terjadi perubahan taksonomi.

### Diskusi 2 - SELECT atau CONSTRUCT
Untuk membuat dashboard populasi kota, saya lebih memilih menggunakan SELECT karena hasilnya dapat ditampilkan dalam bentuk tabel yang berisi nama kota dan jumlah penduduk. Data tersebut juga lebih mudah diurutkan, difilter, dan digunakan untuk membuat grafik. Dari Bagian A, saya mendapatkan 10 baris kota yang berbeda dan menghasilkan 30 triple pada CONSTRUCT dengan LIMIT 10. Selain itu, pada data kota.ttl, Medan memiliki dua nilai populasi, yaitu 2435252 dan 2097610. Hal ini menunjukkan bahwa satu kota bisa memiliki lebih dari satu nilai populasi. Dengan SELECT, saya bisa memeriksa dan memilih nilai yang ingin ditampilkan agar informasi pada dashboard tidak membingungkan.

Untuk menggabungkan data kota dari tiga sumber, saya lebih memilih CONSTRUCT karena hasilnya dapat dibentuk menjadi graf RDF dengan struktur yang seragam. Namun, perbedaan penulisan data tetap perlu diperhatikan. Hal ini terlihat pada Bagian D melalui rusak_b.rq, ketika nama Christopher Nolan muncul dua kali karena memiliki tag bahasa berbeda, yaitu satu tanpa tag bahasa dan satu dengan tag @en. Hasil awalnya adalah 4 triple. Setelah menggunakan STR(?n), hasilnya menjadi 3 triple. Dari hasil tersebut, saya memahami bahwa data dari beberapa sumber perlu diseragamkan terlebih dahulu agar tidak menghasilkan data yang terlihat sama, tetapi sebenarnya berbeda.

### Cek diri
| Pernyataan | Bukti dari tugas ini |
|------------|----------------------|
| 1. CONSTRUCT mencetak template sekali per solution dan menggabungkan triple kembar. | Bagian A: `LIMIT 10` menghasilkan 30 triple karena terdapat 3 pola pada template untuk setiap solution. Jika ada triple yang identik, graf hanya menyimpannya satu kali. |
| 2. Triple dengan variabel tak terikat tidak dicetak, tanpa error. | Bagian B: `demo_c.py` menghasilkan 48 triple dan 16 film tanpa tanggal. Triple `schema:datePublished` tidak terbentuk karena variabel tanggal tidak memiliki nilai. Bagian D: `rusak_a.rq` menghasilkan 0 triple karena `?film` tidak terikat, tetapi tidak menimbulkan error. |
| 3. UNION, jalur `\|`, dan VALUES menyatukan predikat berbeda. | Bagian D: `rusak_b.rq` menggunakan property path `foaf:name\|rdfs:label\|schema:name` untuk mengambil nama dari beberapa predikat dan `VALUES` untuk menentukan tiga orang. `UNION` tidak digunakan secara langsung pada query tersebut. |
| 4. Jalur `+` dan `*` menelusuri hierarki tanpa menyebut perantara. | Bagian A: `wdt:P279*` menelusuri hierarki kelas menuju `wd:Q515`. Bagian C: `rdfs:subClassOf*` menghasilkan tipe turunan, termasuk `foaf:Person`, tanpa perlu menuliskan setiap hubungan perantara. |
| 5. Materialisasi menyimpan kesimpulan, tetapi harus dihitung ulang. | Bagian C: C1 menghasilkan 3 individu `foaf:Person`, sedangkan Rina belum terhitung. Setelah materialisasi ulang pada C2, jumlahnya menjadi 4. Sebanyak 6 triple turunan disimpan dalam `kampus_turunan.ttl`. |
