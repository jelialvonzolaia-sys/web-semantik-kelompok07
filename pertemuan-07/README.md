# Pertemuan 7 - Serialisasi RDF

## Artefak
- Graf asal: [jumlah triple]
- Format ekspor: Turtle, JSON-LD, N-Triples
- Named graph: [nama graf 1] dan [nama graf 2]

## Reifikasi dan provenance
- Triple yang dianotasi: ex:ida ex:mengajar ex:web_semantik
- Creator: ex:ida
- Date: 2026-10-01
- Source: Data akademik kampus

## Perbandingan
- Format paling mudah dibaca manusia: Turtle, karena sintaksnya ringkas
  (memakai prefix dan titik koma untuk menggabungkan predikat dari
  subjek yang sama) sehingga mirip kalimat dan tidak penuh tanda kurung
  atau tag.
- Format untuk HTML/API: JSON-LD, karena berbasis JSON yang sudah
  dipakai luas oleh web dan API, dan bisa disisipkan langsung ke HTML
  lewat tag <script type="application/ld+json">.
- Perbedaan reifikasi klasik dan RDF-star:

  Reifikasi klasik lebih verbose karena RDF standar tidak bisa menjadikan
  sebuah triple sebagai subjek triple lain. Untuk menganotasi satu triple,
  triple itu harus "dibongkar" menjadi node perantara (`ex:stmt-01`) yang
  memiliki `rdf:type rdf:Statement`, `rdf:subject`, `rdf:predicate`, dan
  `rdf:object`. Baru setelah itu anotasi (creator, date, source) ditempelkan
  ke node perantara tersebut. Pada program ini, 1 fakta membutuhkan
  **7 triple tambahan**.

```turtle
  ex:stmt-01 a rdf:Statement ;
      rdf:subject     ex:ida ;
      rdf:predicate   ex:mengajar ;
      rdf:object      ex:web_semantik ;
      dcterms:creator ex:ida ;
      dcterms:date    "2026-10-01"^^xsd:date ;
      dcterms:source  "Data akademik kampus" .
```

  RDF-star menyelesaikan masalah yang sama secara berbeda: triple dibungkus
  utuh dengan `<< >>` dan langsung dipakai sebagai subjek. Tidak ada node
  perantara dan tidak ada pembongkaran, cukup 1 baris untuk satu anotasi.

```turtle
  << ex:ida ex:mengajar ex:web_semantik >> dct:creator ex:ida .
```

  Secara konsep: reifikasi klasik menjelaskan triple lewat deskripsi tentang
  triple (node buatan), sedangkan RDF-star menjadikan triple itu sendiri
  sebagai objek yang bisa dibicarakan (quoted triple).

## Tabel Membandingkan Serialisasi RDF
| Format | Kekuatan Utama | Skenario Tepat |
|---|---|---|
| Tutle | Ringkas dan mudah dibaca manusia | Membuat dan memeriksa data RDF secara manual karena penulisannya sederhana |
| JSON-LD | Cocok web/API dan HTML | Menghubungkan data semantik dengan aplikasi web dan API yang menggunakan JSON |
| RDF/XML | Kompatibilitas data lama | Cocok ketika harus bertukar data dengan aplikasi lama yang menggunakan formal RDF/XML |
| N-Triples | Satu triple per baris; stabil untuk diff | Data RDF berukuran besar, pengolahan otomatis, dan memeriksa perubahan data |
| N-Quads | Menambahkan konteks graf | Ketika data berasal dari beberapa graf dan setiap pernyataan perlu kita tau dari mana asal grafnya |

**Kesimpulan :** Jika ingin membaca lebih mudah menggunakan Turtle. Untuk aplikasi web cocoknya menggunakan JSON-LD. N-Quads cocok kalau data memeiliki beberapa konteks graf.

## Perbandingan
- Format paling mudah dibaca manusia: [isi dan alasan]
- Format untuk HTML/API: [isi dan alasan]
- Perbedaan reifikasi klasik dan RDF-star: [isi]

## Refleksi

1. **Mengapa named graph berguna saat menggabungkan data dari sumber berbeda?**

   Named graph berguna karena kita bisa mengetahui data berasal dari sumber mana, meskipun semua data digabungkan dalam satu dataset. Dengan begitu, data dari setiap sumber tetap bisa dibedakan dan lebih mudah diperiksa jika ada perbedaan atau kesalahan informasi.

2. **Mengapa provenance penting untuk sebuah triple?**

   Provenance penting untuk mengetahui asal suatu triple, siapa yang menyatakan informasi tersebut, dan kapan informasi itu dicatat. Dengan begitu, sumber informasi bisa ditelusuri dan kepercayaan terhadap data dapat dinilai dengan lebih baik.

3. **Format apa yang Anda pilih untuk git diff, dan mengapa?**

   Kelompok kami memilih format N-Triples (`.nt`) karena setiap triple ditulis dalam satu baris. Hal ini membuat perubahan data lebih mudah dilihat dan dibandingkan menggunakan `git diff`, terutama ketika ada penambahan atau penghapusan triple.
