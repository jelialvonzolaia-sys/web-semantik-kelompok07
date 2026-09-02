# Pertemuan 2 - Format Dokumen XML

## 1. Profil XML

Struktur XML profil mahasiswa tersebut menggunakan satu elemen utama (*root element*), yaitu `<profil>` dengan atribut `nim`. Di dalam elemen root terdapat beberapa elemen anak (*child elements*) yang mendeskripsikan data mahasiswa, yaitu `<nama>`, `<angkatan>`, `<programStudi>`, dua elemen `<hobi>` yang digunakan untuk menyimpan data hobi, serta `<deskripsi>` yang berisi informasi singkat tentang mahasiswa. Seluruh elemen disusun secara hierarkis dan konsisten sehingga dokumen XML memenuhi syarat *well-formed*.

## 2. Analisis Kesalahan XML

| No | Bagian yang Salah | Alasan | Perbaikan |
|---|---|---|---|
| 1 | `<nama>Budi Santoso</Nama>` | Dalam aturan well-formed XML sintaksnya bersifat case-sensitive. Tag pembuka `<nama>` dan penutup menggunakan huruf kecil dan huruf besar | `<nama>Budi Santoso</nama>` |
| 2 | `<angkatan> 2024` | Di dalam aturan well-formed semua pembuka tag wajib memeiliki penutup tag | `<angkatan> 2024 </angkatan>` |
| 3 | `<hobi>` | Hobi di pakai 2 kali dapat merusak baik dari kerapian maupun jalannya program | menggunakan perulangan `<daftar hobi>"besisi beberapa hobi..."</daftar hobi>` |
| 4 | `<deskripsi>Saya suka AI & Web Semantik</deskripsi>` | '&' akan dianggap awal referensi entitas, agar tidak terjadi error karakternya harus diganti ke entity bawaan | `<deskripsi>Saya suka AI &amp; Web Semantik</deskripsi>` |

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
**1. Apa perbedaan utama XML dan HTML?**

Jawaban:

'HTML' berfokus pada tampilan sehingga memiliki tag yang baku, sedangkan 'XML' berfokus pada makna yang berfokus pada tag yang dimodelkan sendiri sesuai konteks data.

**2. Apa yang dimaksud dokumen XML yang well-formed?**

Jawaban:

XML well-formed adalah dokumen yang memilki seluruh aturan sintaksis dasar dalam pembuatannya. Aturan yang ada antara lain:
 1. Wajib memiliki tepat satu root element
 2. Setiap tag pembuka wajib memiliki tag penutup
 3. Bersifat case-sensitive
 4. Tidak boleh tumpang tindih
 5. Nilai atribut wajib diapit tanda kutip
 6. Nama tag tidak boleh diawali angka
Jika aturan yang diberlakukan tidak sepenuhnya digunakan maka akan terjadi error.
 
**3. Jelaskan perbedaan well-formed dan valid.**

Jawaban:

**Well-Formed:**
Sebuah dokumen XML dikatakan well-formed jika memenuhi aturan sintaksis dasar XML. Artinya, dokumen tersebut memiliki tepat satu root element, setiap tag pembuka memiliki tag penutup yang sesuai, elemen bersarang dengan benar, penulisan tag bersifat case-sensitive, dan atribut ditulis menggunakan tanda kutip. Jika aturan tersebut tidak dipenuhi, parser XML akan menolak dokumen tersebut.

**Valid:**
Sebuah dokumen XML dikatakan valid jika selain memenuhi aturan well-formed, dokumen tersebut juga mematuhi aturan struktur tambahan yang didefinisikan dalam suatu skema, seperti DTD (Document Type Definition) atau XSD (XML Schema Definition). Skema tersebut dapat menentukan elemen yang boleh digunakan, urutan atau hubungan antar-elemen, atribut yang diperlukan, serta tipe data yang diizinkan.

**4. Mengapa XSD lebih kuat dibandingkan DTD?**

Jawaban:

XSD lebih unggul karena dapat menutup dua kekurangan utama dari DTD.
 - Tipe Data => dalam DTD tidak memeliki data yang spesifik yang membuat angka dan teks diperlukan sama persis. Sedangkan XSD menggunakan tipe data bawaan secara eksplisit seperti string, int, date, gYear, decimal, dan boolean.
 - Identitas kosakata => DTD tidak mendukung namespace, sedangkan XSD mendukung fitur namespace, kardinalitas, minOccrus/maxOccrus, dan derivasi tipe.
 - Format penulisan berbasis XML => DTD dibuat menggunakan sintaksnya sendiri dengan ringkas, sedangkan XSD dibuat dengan XML sepenuhnya.
 - Kontrol struktur dan kardinalitas lanjutan => Pda DTD hanya menggunakan simbol '+', '*', '?'. Sedangkan XSD manggunakan fitur kardinalitas dari minOccrus/maxOccrus dan juga mendukung derivasi tipe.

**5. Mengapa namespace penting ketika data XML berasal dari beberapa kosakata berbeda?**

Jawaban:

**6. Apa kegunaan XPath dalam pengolahan dokumen XML?**

Jawaban:

