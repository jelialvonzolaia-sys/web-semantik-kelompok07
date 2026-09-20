# Pertemuan 4 — Metadata dan Interoperabilitas

## Identitas sumber
- Judul: Java How to Program, Tenth Edition, Early Objects
- Pembuat: Paul Deitel dan Harvey Deitel
- URI sumber: https://example.org/sumber/java-how-to-program-tenth-edition
- Jenis sumber: Text

## Pemetaan Dublin Core Terms

| Properti | Nilai | Alasan pemilihan |
|---|---|---|
| dcterms:title | Java How to Program, Tenth Edition, Early Objects | Menunjukkan judul sumber belajar yang digunakan. |
| dcterms:creator | Paul Deitel dan Harvey Deitel | Menunjukkan pembuat atau penulis sumber belajar. |
| dcterms:description | Buku pembelajaran yang membahas pemrograman Java secara bertahap, mulai dari dasar pemrograman, object-oriented programming, struktur data, database, hingga pengembangan aplikasi web. | Menjelaskan isi dan cakupan sumber belajar secara singkat. |
| dcterms:created | 2015 | Menunjukkan tahun publikasi yang tercantum pada sumber. |
| dcterms:type | Text | Sumber yang digunakan berupa buku teks dalam format PDF. |
| dcterms:language | en | Sumber ditulis menggunakan bahasa Inggris. |
| dcterms:rights | Copyright © 2015, 2012 and 2009 Pearson Education, Inc. All rights reserved. | Menunjukkan informasi hak cipta yang tercantum pada sumber. |
| dcterms:publisher | Pearson Education, Inc. | Menunjukkan penerbit sumber belajar. |

## Hasil validasi
- JSON-LD Playground: Tidak terdapat syntax error. RDF/N-Quads menunjukkan subject URI yang sama dengan metadata Turtle.
- Schema Markup Validator: Tidak terdapat error atau warning setelah properti `rights` diperbaiki menjadi `copyrightNotice`.

## Refleksi

1. **Mengapa URI yang sama penting untuk Turtle dan JSON-LD?**

   URI yang sama digunakan agar Turtle dan JSON-LD merujuk pada sumber yang sama. Walaupun format penulisannya berbeda, keduanya tetap menggunakan identitas sumber yang sama sehingga metadata yang dibuat tetap konsisten dan tidak dianggap sebagai dua sumber yang berbeda. Dengan menggunakan URI yang sama, data dari Turtle dan JSON-LD juga lebih mudah dibandingkan, dihubungkan, dan digunakan kembali dalam sistem Web Semantik.

2. **Apa perbedaan peran DC Terms dan schema.org pada pekerjaan ini?**

   DC Terms dan schema.org memiliki peran yang berbeda dalam merepresentasikan metadata. DC Terms digunakan untuk memberikan istilah metadata yang umum dan terstruktur, seperti `title`, `creator`, `description`, `created`, dan `publisher`, sehingga informasi sumber dapat dipertukarkan dan dipahami secara konsisten. Sementara itu, schema.org digunakan untuk mendeskripsikan informasi menggunakan vocabulary yang lebih luas dan banyak digunakan dalam pengembangan web, sehingga metadata dapat lebih mudah dipahami oleh mesin pencari dan aplikasi web.

3. **Sebutkan satu risiko jika metadata HTML, Turtle, dan JSON-LD tidak konsisten.**

   Salah satu risiko jika ketiga metadata tersebut tidak konsisten adalah terjadinya perbedaan informasi ketika data dibaca oleh sistem yang berbeda. Akibatnya, ketepatan data dapat terganggu karena sistem dapat mengenali informasi yang berbeda untuk sumber yang sama.

## Catatan akhir

Metadata HTML, Turtle, dan JSON-LD telah diselaraskan pada informasi utama seperti judul, pembuat, deskripsi, tanggal, bahasa, dan hak cipta. Ketiga format menggunakan informasi yang merujuk pada sumber yang sama sehingga metadata dapat lebih mudah dipertukarkan dan digunakan oleh sistem yang berbeda.