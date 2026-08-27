# Pertemuan 1 - Pengenalan Web Semantik

## 1. Eksplorasi Wikidata
- Nama entitas: Universitas Sumatera Utara
- Identifier Wikidata: Q4200341
- Deskripsi: National university in North Sumatera, Indonesia
- Negara: Indonesia
- Lokasi: Medan Tuntungan
- Tahun berdiri: 20 November 1957
- Website: <https://www.usu.ac.id/>
- Keanggotaan: ASEAN University Network
- Akreditasi: National Accreditation Board for Higher Education

## 2. Entitas, Atribut, dan Relasi

| Informasi | Kategori | Alasan |
|---|---|---|
| Indonesia | Entitas | Karena bisa berdiri sendiri dan memiliki identitas |
| ASEAN  | Entitas | Karena ASEAN adalah sebuah organisasi yang bisa berdiri sendiri dan memiliki identitas tersendirinya juga |
| Universitas Sumatera Utara → accredited by → National Accreditation Board for Higher Education | Relasi | Dimana menghubungkan USU dengan lembaga yang memberinya akreditas |
| 20 November 1957 | Atribut | Tidak bisa berdiri sendiri, karena merupakan sebuah nilai yaitu tanggal atau waktu |
| USU → member of → ASEAN | Relasi | Disini menghubungkan para member/anggota antara ASEAN dan USU |

## 3. Eksplorasi Schema.org

| Property | Fungsi | Contoh Nilai |
|---|---|---|
| Name | Menyatakan nama organisasi pendidikan | Universitas Sumatera Utara |
| Description | Penjelasan tentang organisasi | Universitas negeri yang berada di Medan, Sumatera Utara, Indonesia |
| Address | Menyatakan lokasi fisik organisasi pendidikan | Jl. Dr. T. Mansur No. 9, Kampus Padang Bulan, Medan, Sumatera Utara |
| Slogan | Menyatakan moto terkait organisasi | The University for Industry |
| FoundingDate | Tanggal berdirinya organisasi | 1957-11-20 |
| URL | Menyatakan alamat website | https://www.usu.ac.id/ |


## 4. Pertanyaan Evaluasi

### 1. Apa perbedaan web tradisional dan Web Semantik?
Jawaban: ...

### 2. Mengapa entitas membutuhkan identifier unik?
Jawaban: Suatu entitas membutuhkan identifier unik agar dapat dikenali dan dibedakan secara tepat dari entitas lainnya. Identifier ini mencegah ambiguitas, terutama ketika beberapa entitas memiliki nama yang sama atau mirip. Selain itu, identifier unik mempermudah pencarian, pengelolaan, dan penghubungan data antarentitas.

Dalam Web Semantik, identifier unik digunakan untuk memastikan setiap entitas dapat diidentifikasi dan dihubungkan dengan benar. Contohnya, Universitas Sumatera Utara memiliki identifier Wikidata Q4200341, sehingga dapat dibedakan dari entitas universitas lainnya.

### 3. Jelaskan subject, predicate, dan object.
Jawaban: ...

### 4. Apa keuntungan hubungan antarentitas?
Jawaban: 
- Makna yang jelas dan pencarian yang terstruktur : mencari jawaban yang terstruktur, identitas entitas juga unik global melalui URI, agar terhindar dari ambiguitas.
- Meningkatkan akurasi AI(GraphRAG) : menympan data sebagai knowledge graph untuk memberi fakta presisi dan relasi eksplisit yang dapat dilacak. kegunaannya agar kelemahan AI berbasis LLM tidak terlihat, seperti halusinasi, sumber yang tidak jelas, pengetahuan membek, dan sulit diaudit.
- Memungkinkan Inferensi Otomatis : data yang terhubung akan menarik kesimpulan dari informasi yang sudah ada walaupun faktanya tidak pernah ditulis secara eksplisist, contohnya menyimpulkan letak provinsi secara transitif.

### 5. Bagaimana Knowledge Graph membantu AI?
Jawaban: ...
