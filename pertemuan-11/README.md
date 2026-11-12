# Pertemuan 11 - CONSTRUCT dan Hierarki Data

## Langkah 1 - Solution menjadi triple
| Solution | ?d | ?mk | Triple yang dicetak |
|----------|----|-----|---------------------|
| 1 | ex:budi | ex:tif2108 | ex:tif2108 a ex:MataKuliahAktif; ex:tif2108 ex:diampuOleh ex:budi |
| 2 | ex:budi | ex:tif2104 | ex:tif2104 a ex:MataKuliahAktif; ex:tif2104 ex:diampuOleh ex:budi |
| 3 | ex:sari | ex:tif2108 | ex:tif2108 a ex:MataKuliahAktif; ex:tif2108 ex:diampuOleh ex:sari |
| 4 | ex:ida | ex:tif2104 | ex:tif2104 a ex:MataKuliahAktif; ex:tif2104 ex:diampuOleh ex:ida |

- Jumlah solution: 4
- Jumlah cetakan triple: 8 (4 solution × 2 pola template)
- Jumlah triple di graf hasil: 6
- Triple yang tercetak lebih dari sekali: ex:tif2108 a ex:MataKuliahAktif (2×, solution 1 dan 3) dan ex:tif2104 a ex:MataKuliahAktif (2×, solution 2 dan 4)

## Langkah 2 - Tebak lalu cek
| Soal | Tebakan | Hasil mesin | Penjelasan bila meleset |
|------|---------|-------------|-------------------------|
| A | 2 baris | 3 baris | `rdfs:subClassOf*` mencakup panjang nol, sehingga `ex:Dosen` ikut muncul bersama `DosenTetap` dan `DosenTamu` |
| B | 4 baris | 4 baris, 2 kali sari muncul | Sari bertipe `DosenTetap` dan `DosenTamu`, keduanya ke `pegawai` dari jalur berbeda. Jadi Sari muncul 2 kali ditambah `DosenTetap` dan `DosenTamu` |
| C | 2 tripel | 3 tripel | CONSTRUCT menduplikasi tripel unik sehingga `ex:sari a ex:Pegawai` hanya dicetak sekali |
| D | 0 tripel | 0 tripel | karena `ex:rudi` tidak memiliki `ex:nama` jadi variable n (`?n`) tidak terikat. Triple template di lewati diam diam tanpa error dan grafnya kosong |

## Langkah 3 - Graf kampus lokal
- Berkas sumber dan IRI dasar graf: [isi]
- Kelas dosen dan properti nama: [isi]
- Triple hasil pemetaan: [isi]
- foaf:Person sebelum / sesudah: [isi] / [isi]
- Triple asli vs triple turunan: [jawaban]
- Dosen baru setelah program selesai: [jawaban]

## Langkah 4 - Wikidata
- Jumlah triple Q6: 144
- Jumlah baris Q6 versi SELECT: 50
- Penjelasan perbedaan: Jumlah triple hasil `CONSTRUCT` (144) tidak sama dengan 3 × jumlah baris `SELECT` (3 × 50 = 150). Hal ini terjadi karena dalam satu graf RDF, kumpulan triple bersifat himpunan (*set*) sehingga triple yang identik otomatis dide-duplikasi dan hanya disimpan satu kali. Pada versi `SELECT`, beberapa universitas yang tercatat memiliki lebih dari satu wilayah administratif (`wdt:P131`)—seperti Universitas Indonesia dan Universitas Andalas—muncul dalam beberapa baris terpisah, tetapi pada `CONSTRUCT`, triple `a ex:Universitas` dan `ex:nama` untuk universitas tersebut tidak digandakan.
- Q7 dengan + vs *: Secara jumlah baris terlihat tidak ada perubahan (sama-sama 30 baris karena terpotong klausa `LIMIT 30`), namun secara isi terdapat perbedaan pada awal hasil: operator `+` (*one or more steps*) hanya menelusuri subkelas turunan di bawah `wd:Q3918` (*universitas*), sedangkan operator `*` (*zero or more steps*) juga menyertakan kelas induk `wd:Q3918` (universitas itu sendiri) pada jalur dengan panjang nol.
- Kelas yang mengejutkan dan alasannya: **`wd:Q2406423` (*university in the Netherlands* / universitas di Belanda)** (dan juga `wd:Q11421293` / universitas negeri di Jepang). Awalnya mengejutkan karena kategori berbasis wilayah negara muncul sebagai kelas (*subclass*), bukan sekadar instansi universitas di negara tersebut. Hal ini terjadi karena ontologi Wikidata memodelkan pengelompokan institusi spesifik per negara/status (seperti universitas di Belanda atau universitas negeri di Jepang) sebagai subkelas khusus dari kelas umum universitas (`wd:Q3918`) melalui rantai properti `wdt:P279` (*subclass of*).

## Langkah 5 - Graf dari Python
- Jumlah triple universitas.ttl: [142]
- Jumlah baris universitas_per_kota.csv: [40] (Tidak termasuk header)
- setReturnFormat(TURTLE): [ Digunakan untuk menentukan bahwa hasil query dari endpoint Wikidata diminta dalam format Turtle (Turtle RDF), sehingga data yang diterima dapat digunakan sebagai graf RDF ]
- Graph().parse(data=...): [ Digunakan untuk membaca data Turtle dari hasil endpoint dan mengubahnya menjadi graf RDF menggunakan library RDFLib. Graf tersebut kemudian disimpan ke dalam file universitas.ttl ]
- Mengapa q_kota tidak memanggil endpoint:[ Karena data universitas sudah diambil satu kali dari Wikidata dan disimpan dalam file universitas.ttl. Query q_kota dijalankan langsung pada graf RDF lokal menggunakan objek g, sehingga tidak perlu mengirim permintaan lagi ke endpoint Wikidata. Hal ini juga mengurangi permintaan ke endpoint publik dan menjadikan file Turtle sebagai cache data ]

## Refleksi
1. Mengapa jumlah triple hasil CONSTRUCT bisa lebih kecil dari jumlah baris SELECT dengan WHERE yang sama?
   
   **Jawab:** Soalnya SELECT menampilkan satu baris untuk setiap solution, sedangkan CONSTRUCT hasilnya berupa graf, dan graf itu himpunan, jadi triple yang sama persis cuma disimpan sekali. Kalau beberapa solution mencetak triple yang identik, hasilnya digabung jadi satu. Contohnya di tugas ini, "ex:tif2108 a ex:MataKuliahAktif" tercetak dari 2 solution (budi dan sari), tapi di graf hasil cuma ada 1. Jadi kalau templatenya cuma mencetak satu pola triple per solution, jumlah triple bisa menjadi lebih kecil dari jumlah baris SELECT karena ada yang duplikat.

2. Bila taksonomi di graf kampus Anda berubah bulan depan, apa yang terjadi pada triple turunan di kampus_usu_v4.ttl?
   
   **Jawab:** Triple turunan di kampus_usu_v4.ttl tidak ikut berubah otomatis. Triple itu cuma hasil CONSTRUCT yang sudah disimpan ke file, jadi isinya "foto" dari kondisi saat query dijalankan. Kalau taksonomi berubah, triple turunan bisa jadi sudah ketinggalan zaman (basi) atau tidak sesuai lagi dengan data aslinya. Supaya update, query CONSTRUCT-nya harus dijalankan ulang dan filenya dibuat ulang dari data yang baru.

3. Kapan Anda menyimpan hasil query sebagai tabel (CSV), dan kapan sebagai graf (Turtle)?

   **Jawab:** Gunakan CSV ketika hasil query `SELECT` hanya butuh dianalisis dalam bentuk tabel, karena bentuknya datar dan mudah dibuka di Excel. Gunakan Turtle saat query `CONSTRUCT` yang dipakai sebagai data RDF baru yang ingin disimpan dan digunakan kembali kedalam *knowledge graph*.