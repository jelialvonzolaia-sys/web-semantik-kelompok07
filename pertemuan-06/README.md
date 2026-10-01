## IRI dasar graf
(https://contoh.github.io/web-semantik/221202001/kampus#)

## IRI, Literal, Blank Node, dan Prefix
1. Identifikasi jenis node untuk `ex:ida`, `"Ida Adi"@id`, dan `[ ex:kota "Medan" ]`.

   Jawaban :
   - `ex:ida` (IRI/URI) seperti ID unik atau alamat entitas. Karena punya IRI, dia bisa jadi subject yang kita kasih properti atau relasi lain.
   - `"Ida Adi"@id` (Literal) cuma nilai data mentah berisi teks biasa. `@id` penanda kalau teksnya menggunakan bahasa Indonesia.
   -  `[ ex:kota "Medan" ]` (Blank Node) node anonim atau tanpa ID/nama khusus. Dipakai untuk mengelompokkan informasi tanpa perlu membuat IRI baru.
   
2. Mengapa literal tidak boleh menjadi subject RDF?

   Jawaban :

   Karena literal itu nilai akhir (data mentah seperti teks, angka, tanggal). RDF didisain agar subject berupa ID unik (IRI atau Blank Node) yang bisa memiliki berbagai hubungan. Jika string `"Ida Adi"` dijadikan subject, sistem akan bingung karena teks biasa tidak punya ID unik untuk ditempeli relasi lain.
   
4. Buat IRI dasar untuk graf Anda dengan pola HTTP, misalnya `https://contoh.github.io/web-semantik/ISI_NIM/kampus#`.

   Jawaban :
   - `https://contoh.github.io/web-semantik/221202001/kampus#`
   - `@prefix ex: <https://contoh.github.io/web-semantik/221202001/kampus#>`
   
5. Tuliskan kepanjangan namespace `rdf`, `rdfs`, `xsd`, dan `foaf`.

   Jawaban :
   - `rdf` (Resource Description Framework): Standar dasar untuk nyusun struktur data berbentuk triple (subject-predicate-object).
   - `rdfs` (RDF Schema): ekstensi buat nambahin struktur hirarki, seperti nentuin yang mana *class* dan yang mana *subclass*.
   - `xsd` (XML Schema Definition): standar buat nentuin tipe data literal, contoh angka bulat(`integer`), teks (`string`), atau tanggal(`date`).
   - `foaf` (Friend of a Friend): kosa kata populer yang biasa dipakai untuk mendeskripsikan data orang, nama, dan hubungan sosial.   

## Ringkasan graf
- Jumlah triple: 27 triple
- Namespace yang digunakan: `ex`, `foaf`, `rdf`, dan `xsd`
- Entitas:
  - 3 Dosen: Muhammad Isa Dadi Hasibuan, Dedy Arisandi, Lia Silviana
  - 3 Mata Kuliah: Web Semantik, Manajemen Sistem Basis Data, Matematika Diskrit
  - 2 Mahasiswa: Patricia, Jeli

## Contoh triple
1. [subject] - [predicate] - [object]
2. [subject] - [predicate] - [object]
3. [subject] - [predicate] - [object]

## Perbandingan serialisasi
- Turtle: [pengamatan]
- JSON-LD: [pengamatan]
- Pernyataan yang sama: [isi]

## Refleksi
1. Kapan object harus berupa IRI dan kapan berupa literal?
2. Mengapa prefix membantu keterbacaan tanpa mengubah IRI?

   Jawaban :

   Prefix hanya singkatan untuk mengganti URI namespace yang panjang. Penggunaan prefix membuat sintaks kode jauh lebih mudah dibaca tanpa mengubah arti maupun struktur data RDF yang sebenarnya.

   Saat program dijalankan atau diproses oleh parser, prefix akan otomatis digabungkan kembali dengan nama lokal entitas menjadi IRI yang utuh secara background.

   **Tanpa prefix**
   ```turtle
      <https://contoh.github.io/web-semantik/221202001/kampus#ida> <http://www.w3.org/1999/02/22-rdf-syntax-ns#type> <https://contoh.github.io/web-semantik/221202001/kampus#Lecturer> .
      <https://contoh.github.io/web-semantik/221202001/kampus#ida> <http://xmlns.com/foaf/0.1/name> "Ida Adi"@id .
    ```

   **Menggunakan prefix**
   ```turtle
      @prefix ex: <https://contoh.github.io/web-semantik/221202001/kampus#> .
      @prefix rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#> .
      @prefix foaf: <http://xmlns.com/foaf/0.1/> .

      ex:ida rdf:type ex:Lecturer ;
             foaf:name "Ida Adi"@id .
    ```

4. Sebutkan satu kesalahan pemodelan yang Anda hindari pada graf ini.
   
   Jawaban :

   Yaitu menggunakan string/literal biasa untuk entitas objek yang harusnya memiliki properti lanjutan (seperti MatKul).

   Jika MatKul ditulis sebagai string teks biasa pada relasi mengajar, MatKul tersebut akan menjadi nilai terminal dan tidak bisa dihubungkan ke data lain seperti jumlah SKS, jadwal kuliah, atu daftar mahasiswa yang mengambil. Jadi, MatKul dimodelkan menggunakan IRI agar dapat berfungsi sebagai subjek di triple lain.

   **Model yang salah**
   ```turtle
      @prefix ex: <https://contoh.github.io/web-semantik/221202001/kampus#> .

      ex:ida ex:mengajar "Web Semantik" .
    ```

   **Model yang benar**
   ```turtle
      @prefix ex: <https://contoh.github.io/web-semantik/221202001/kampus#> .
      @prefix foaf: <http://xmlns.com/foaf/0.1/> .
      @prefix xsd: <http://www.w3.org/2001/XMLSchema#> .

      ex:ida ex:mengajar ex:web_semantik .

      ex:web_semantik foaf:name "Web Semantik"@id .
      ex:web_semantik ex:jumlahSks "3"^^xsd:integer .
    ```
      
