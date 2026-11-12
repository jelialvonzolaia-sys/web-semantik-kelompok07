# Pertemuan 11 - Tugas Mandiri: Dari Endpoint ke Graf Lokal

## Bagian A - LIMIT dan triple
- Triple dengan LIMIT 10: 30 triple (batas atas: 10 solution × 3 pola template = 30 triple)
- Triple dengan LIMIT 30: 90 triple (batas atas: 30 solution × 3 pola template = 90 triple)
- Baris SELECT LIMIT 10 / kota berbeda: 10 baris / 10 kota berbeda
- Mengapa tidak (atau mengapa tepat) 30 triple: Hasil mencapai 30 triple karena dari 10 solution yang dihasilkan klausa WHERE, setiap kota memiliki label bahasa Indonesia (`LANG(?namaKota) = "id"`) dan data populasi yang unik, sehingga 3 pola dalam template CONSTRUCT mencetak 30 triple unik tanpa ada triple kembar yang digabungkan.

## Bagian B - DBpedia ke Turtle
- Output demo_c.py: 12 triple, film tanpa tanggal: 1
- Sumber data: Data contoh film.ttl (karena endpoint DBpedia mengalami connection reset)
- Triple yang tetap tercetak untuk film tanpa tanggal: Triple `schema:Movie`, `schema:director`, dan `schema:name` tetap tercetak karena berada di luar klausa OPTIONAL. Triple `schema:datePublished` tidak dicetak karena properti `dbo:releaseDate` di klausa OPTIONAL bernilai kosong (unbound). Tidak ada pesan error karena SPARQL memperlakukan OPTIONAL sebagai pencocokan fakultatif, bukan galat sintaks/runtime.
- Query lokal tanpa internet: Berhasil karena query dijalankan pada berkas lokal `film_nolan.ttl` / `film.ttl` yang tersimpan di disk lokal tanpa memerlukan koneksi HTTP. Jika `demo_c.py` dijalankan ulang secara penuh tanpa internet, program akan gagal (error) pada saat mengeksekusi `sp.queryAndConvert()` karena tidak bisa terhubung ke server DBpedia.
- Mengapa dbo:Film memberi 0 pada film_nolan.ttl: Karena klausa CONSTRUCT pada `q_demo_b.rq` mengubah tipe/kelas dari `dbo:Film` menjadi `schema:Movie`. Dalam berkas Turtle hasil CONSTRUCT, semua entitas film hanya memiliki predikat tipe `schema:Movie`, sehingga pencarian dengan `dbo:Film` tidak akan cocok dengan triple mana pun.

## Bagian C - Materialisasi
- Output demo_d.py: Sebelum: 0 | Sesudah: 3 | Ada Rina: 3
- Mengapa Ada Rina bernilai 3: Karena materialisasi pada `demo_d.py` hanya dijalankan satu kali sebelum entitas `ex:rina` ditambahkan ke graf. Ketika `ex:rina` dimasukkan sebagai `ex:DosenTetap`, aturan inferensi `a/rdfs:subClassOf*` tidak dijalankan ulang, sehingga triple `ex:rina a foaf:Person` tidak pernah dibuat/ditambahkan ke graf.
- Output materialisasi_ulang.py: Putaran 1: 4 triple baru | Person: 3 ; Rina masuk | Person: 3 ; Putaran 2 | Person: 4 ; Triple turunan disimpan terpisah: 6
- Tiga triple yang bukan baru: Triple keanggotaan kelas awal/langsung (`ex:budi a ex:DosenTetap`, `ex:siti a ex:DosenKontrak`, dan `ex:joko a ex:Mahasiswa`). Ketiga triple ini dicetak oleh bagian nol-transisi (jangkar `*`) dari jalur `a/rdfs:subClassOf*` yang mencocokkan `?x a ?x_type` di mana `?x_type` adalah kelas langsungnya sendiri yang sudah ada di graf awal.
- Kegunaan kampus_turunan.ttl dan langkah manual yang tersisa: Berkas terpisah ini berguna agar triple hasil inferensi/materialisasi terisolasi dari data utama (fakta mentah). Jika taksonomi berubah (misalnya `ex:Dosen` bukan lagi subkelas `foaf:Person`), kita cukup menghapus berkas `kampus_turunan.ttl` tanpa merusak data mentah. Langkah manual yang tetap harus dilakukan adalah menghapus berkas `kampus_turunan.ttl` lama dan menjalankan ulang script materialisasi.

## Bagian D - Diagnosis
| Query | Hasil mesin | Gejala | Penyebab (baris yang salah) | Hasil setelah diperbaiki |
|-------|-------------|--------|-----------------------------|--------------------------|
| rusak_a | 0 triple | Graf kosong, tanpa error | Salah ketik nama variabel di mana template CONSTRUCT memakai `?film` tetapi klausa WHERE (baris 3) memakai `?f`. | 3 triple |
| rusak_b | 4 triple | Dua nilai yang tampak sama / triple lebih banyak | Tidak ada filter tag bahasa pada klausa WHERE (baris 4), sehingga orang dengan beberapa label bahasa (seperti Chris Nolan dengan `"Christopher Nolan"` dan `"Christopher Nolan"@en`) mencetak beberapa triple. | 3 triple |
| rusak_c | 0 triple | Superkelas tidak ditemukan / WHERE tidak cocok | Klausa WHERE (baris 3) mencari entitas yang bertipe `ex:Permukiman` secara langsung, padahal di `kota.ttl` entitas hanya bertipe subkelasnya (`ex:KotaMetropolitan`, `ex:KotaDiIndonesia`, `ex:Desa`) tanpa jalur `subClassOf*`. | 3 triple |

## Bagian E - Etika dan diskusi
| Aturan | a_kota_limit.rq | demo_c.py |
|--------|-----------------|-----------|
| 1 User-Agent | Tidak relevan (query SPARQL murni yang ditempel langsung ke UI Wikidata Query Service). | Dipenuhi pada baris `UA = "KelasWebSemantik/1.0 (email@kampus.ac.id)"` yang dikirim via SPARQLWrapper. |
| 2 LIMIT | Dipenuhi dengan adanya klausa `LIMIT 10` pada baris terakhir query. | Tidak relevan secara eksplisit karena query hanya mengambil data terikat untuk sutradara spesifik (`dbr:Christopher_Nolan`). |
| 3 Simpan, jangan ulangi | Tidak relevan karena eksekusi visual di browser. | Dipenuhi pada baris `g.serialize("film_nolan.ttl", format="turtle")` di mana hasil query pertama disimpan ke disk lokal. |
| 4 Jangkar jalur | Dipenuhi pada `wdt:P31/wdt:P279* wd:Q515` di mana jalur berjangkar pada IRI `wd:Q515` (city) dan `wd:Q252` (Indonesia). | Tidak relevan karena query tidak menggunakan jalur ekspresi properti `*` atau `+`. |

- Versi yang melanggar aturan 4 (tidak dijalankan) dan alasannya:
  `SELECT * WHERE { ?s wdt:P31/wdt:P279* ?o }`
  Alasan: Query ini tidak memiliki jangkar IRI di kedua ujung variabel (`?s` maupun `?o`), sehingga mesin triplestore harus menelusuri seluruh hierarki kelas di Wikidata tanpa batas. Hal ini menyebabkan traversal graf secara masif, memakan Resource CPU/Memori endpoint publik, dan berujung pada HTTP 504 Timeout.

### Diskusi 1 - Taksonomi berubah
Jika taksonomi berubah setelah satu tahun (misalnya suatu subkelas dilepas dari hirarki superkelasnya), pada pendekatan **Demo D (C1)** seluruh triple hasil inferensi telah tercampur permanen di dalam berkas graf produksi `kampus_v4.ttl`. Triple yang kini salah adalah triple inferensi lama yang masih tersimpan secara eksplisit (seperti `ex:budi a foaf:Person`). Untuk menemukannya pada `kampus_v4.ttl`, kita harus melakukan penelusuran manual atau membandingkan setiap triple tipe dengan hirarki taksonomi baru satu per satu, yang sangat sulit dilakukan.

Sebaliknya, pada **C2 (`kampus_turunan.ttl`)**, data mentah (`kampus.ttl`) dan data materialisasi (`kampus_turunan.ttl`) disimpan secara terpisah. Ketika taksonomi berubah, kita hanya perlu menghapus berkas `kampus_turunan.ttl` yang lama, lalu menjalankan ulang `materialisasi_ulang.py`. Dengan demikian, triple yang salah langsung hilang otomatis tanpa menyisakan sampah data pada graf produksi utama.

### Diskusi 2 - SELECT atau CONSTRUCT
Untuk **dashboard populasi kota**, saya memilih **SELECT**. Berdasarkan temuan Bagian A, klausa `CONSTRUCT` membatasi jumlah *solution* (bukan jumlah triple), serta dapat menghasilkan duplikasi jika suatu entitas memiliki multiple populasi/label. Pada dashboard, kita membutuhkan data tabular yang bersih dan spesifik (misalnya tepat kolom `?namaKota` dan `?populasi`) untuk dirender ke tabel atau grafik UI secara langsung tanpa perlu parsing struktur graf RDF.

Untuk **menggabungkan data kota dari tiga sumber**, saya memilih **CONSTRUCT**. Berdasarkan temuan Bagian D (`rusak_b.rq`), sumber data heterogen sering menggunakan predikat berbeda (`foaf:name`, `rdfs:label`, `schema:name`) atau tag bahasa yang bervariasi. Perintah `CONSTRUCT` memungkinkan kita melakukan pemetaan (*mapping*) dan penyelarasan ontologi (*schema alignment*) dari berbagai format sumber menjadi satu model graf tunggal yang konsisten (misalnya semua nama dipetakan ke `ex:nama`). Selain itu, sifat `CONSTRUCT` yang otomatis menggabungkan triple kembar (*deduplication*) sangat membantu dalam eliminasi redundansi data antar sumber.

### Cek diri
| Pernyataan | Bukti dari tugas ini |
|------------|----------------------|
| 1 | Bagian A: Query dengan LIMIT 10 menghasilkan 30 triple karena klausa WHERE mengembalikan 10 solution dan template CONSTRUCT memiliki 3 pola triple ($10 \times 3 = 30$). |
| 2 | Bagian B & Bagian D (`rusak_a.rq`): Film *Insomnia* yang tidak memiliki `dbo:releaseDate` (variabel `?rilis` tak terikat) tetap berhasil dieksekusi tanpa error, hanya saja triple `schema:datePublished` tidak dicetak ke graf. |
| 3 | Bagian D (`rusak_b.rq`): Penggunaan jalur alternatif predikat `foaf:name\|rdfs:label\|schema:name` berhasil menyatukan pencarian nama dari berbagai properti yang berbeda pada data orang. |
| 4 | Bagian C & D (`perbaikan_c.rq`): Penggunaan `a/rdfs:subClassOf* ex:Permukiman` berhasil menelusuri entitas `ex:Medan` (`ex:KotaMetropolitan`) dan `ex:Tomok` (`ex:Desa`) secara otomatis tanpa perlu menyebut kelas perantaranya satu per satu. |
| 5 | Bagian C (`demo_d.py` vs `materialisasi_ulang.py`): Ketika `ex:rina` ditambahkan, jumlah `foaf:Person` pada `demo_d.py` tetap 3. Jumlah baru menjadi 4 pada `materialisasi_ulang.py` setelah aturan inferensi dihitung dan dijalankan ulang. |