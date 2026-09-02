# Pertemuan 2 - Format Dokumen XML

## 1. Profil XML
Jelaskan secara singkat struktur XML yang Anda buat.

## 2. Analisis Kesalahan XML

| No | Bagian yang Salah | Alasan | Perbaikan |
|---|---|---|---|
| 1 | ` <nama></Nama> ` | pembuka tag ` <nama> ` dan penutup berbeda | `<nama></nama>` |
| 2 | ` <angkatan> ` | penutup taglinenya tidak ada | `<angkatan></angkatan>` |
| 3 | ` <hobi> ` | hobi di pakai 2 kali dapat merusak baik dari kerapian maupun jalannya program | menggunakan perulangan |

## 3. Analisis XML Schema
1. Root element: 'buku' -> Didefinisikan pada '<xs:element name="buku">'
2. Tipe data judul: 'string' -> Berupa teks/karakter
3. Tipe data tahun: 'gYear' -> Merupakan format tahun Gregorian, contoh: 2026
4. Tipe data harga: 'Decimal' -> Berupa angka
5. Atribut ISBN: 'use="required"' merupakan deklarasi yang digunakan agar dokumen XML valid, jadi wajib dituliskan

## 4. Analisis Namespace
1. Mengapa kedua elemen title tidak sama? ...
2. Fungsi prefix: ...
3. Fungsi xmlns: ...
4. Apakah URI namespace harus dapat dibuka? ...

## 5. Pertanyaan Evaluasi
1. Apa perbedaan utama XML dan HTML?
-> 'HTML' berfokus pada tampilan sehingga memiliki tag yang baku, sedangkan 'XML' berfokus pada makna yang berfokus pada tag yang dimodelkan sendiri sesuai konteks data.

2. Apa yang dimaksud dokumen XML yang well-formed?
-> XML well-formed adalah dokumen yang memilki seluruh aturan sintaksis dasar dalam pembuatannya. Aturan yang ada antara lain:
 a. Wajib memiliki tepat satu root element
 b. Setiap tag pembuka wajib memiliki tag penutup
 c. Bersifat case-sensitive
 d. Tidak boleh tumpang tindih
 e. Nilai atribut wajib diapit tanda kutip
 f. Nama tag tidak boleh diawali angka
 Jika aturan yang diberlakukan tidak sepenuhnya digunakan maka akan terjadi error.
 
3. Jelaskan perbedaan well-formed dan valid.
4. Mengapa XSD lebih kuat dibandingkan DTD?
5. Mengapa namespace penting ketika data XML berasal dari beberapa kosakata berbeda?
6. Apa kegunaan XPath dalam pengolahan dokumen XML?