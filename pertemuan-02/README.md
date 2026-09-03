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
1. Mengapa kedua elemen title tersebut tidak dianggap sama?

   Jawaban :
   
   Elemen `title` tidak sama karena URI namespace-nya berbeda melalui prefix-nya (`.../buku` vs `.../web`), maka meskipun local name-nya sama-sama "title", keduanya     dianggap sebagai elemen yang berbeda dan tidak akan saling bentrok. 
   
2. Apa fungsi prefix buku: dan web:?

   Jawaban :

   Prefix berfungsi sebagai singkatan untuk menghubungkan sebuah elemen ke URI namespace tertentu yang sudah dideklarasikan. Fungsinya adalah membedakan asal/konteks suatu elemen, sehingga elemen dengan nama sama tapi berasal dari "kosakata" (vocabulary/skema) yang berbeda tetap bisa dibedakan secara jelas dalam satu dokumen XML yang sama.

3. Apa fungsi atribut xmlns?

   Jawaban :

   Atribut `xmlns` digunakan untuk mendeklarasikan sebuah namespace, yaitu mengaitkan sebuah prefix dengan sebuah URI unik. Contoh: `xmlns:buku="https://example.org/buku"` berarti setiap elemen/atribut yang memakai prefix `buku:` termasuk dalam namespace dengan identitas URI tersebut. Tujuannya adalah menghindari ambiguitas dan tabrakan nama (naming conflict) ketika beberapa skema/vocabulary XML digabungkan dalam satu dokumen.\
   
4. Apakah URI namespace harus dapat dibuka sebagai halaman web? Jelaskan.

   Jawaban :

   Tidak harus. URI pada `xmlns` berfungsi sebagai **identifier** , bukan sebagai alamat (locator) yang wajib bisa diakses atau menampilkan halaman/dokumen tertentu. Yang penting URI tersebut unik, sehingga tidak akan bertabrakan dengan namespace milik pihak lain dan biasanya digunakan URI berbasis domain yang dimiliki organisasi karena domain sudah dijamin unik. Meski begitu, dalam praktiknya banyak namespace (misalnya namespace RDF, XHTML) memang mengarah ke halaman yang berisi dokumentasi atau skema, sebagai bentuk kebaikan (best practice) agar orang lain bisa memahami maksud namespace tersebut (tapi ini opsional), bukan keharusan teknis dari spesifikasi XML.

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

Namespace penting karena dapat mencegah terjadinya konflik atau kebingungan antara elemen yang memiliki nama sama, tetapi berasal dari kosakata atau sumber yang berbeda. Dengan menggunakan namespace, setiap elemen memiliki identitas yang jelas berdasarkan URI atau prefix tertentu. Hal ini memungkinkan data XML dari beberapa sumber yang berbeda digabungkan dalam satu dokumen tanpa terjadi bentrokan nama elemen.

**6. Apa kegunaan XPath dalam pengolahan dokumen XML?**

Jawaban:

XPath semacam "alamat" yang dipakai buat nemuin bagian tertentu di dalam file XML, misalnya mau cari elemen apa, ada di mana letaknya, atau mau ambil isinya.

Jadi gunanya kurang lebih:
- Membantu **mencari** elemen atau data tertentu di dalam XML tanpa harus baca semua isi filenya satu-satu.
- Membantu **menunjukkan letak/posisi** suatu elemen, misalnya elemen `title` ada di dalam elemen `data`.
- Sering dipakai kalau kita mau ambil data dari XML pakai program atau tools tertentu.


