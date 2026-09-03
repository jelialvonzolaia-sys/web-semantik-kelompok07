# Pertemuan 1 - Pengenalan Web Semantik

## 1. Eksplorasi Wikidata
- Nama entitas: Universitas Sumatera Utara
- Identifier Wikidata: Q4200341
- Deskripsi: National university in North Sumatera, Indonesia
- Negara: Indonesia
- Lokasi: Medan Tuntungan
- Tahun berdiri: 4 Juni 1952
- Website: <https://www.usu.ac.id/>
- Keanggotaan: ASEAN University Network
- Akreditasi: National Accreditation Board for Higher Education

## 2. Entitas, Atribut, dan Relasi

| Informasi | Kategori | Alasan |
|---|---|---|
| Indonesia | Entitas | Karena bisa berdiri sendiri dan memiliki identitas |
| ASEAN  | Entitas | Karena ASEAN adalah sebuah organisasi yang bisa berdiri sendiri dan memiliki identitas tersendirinya juga |
| Universitas Sumatera Utara → accredited by → National Accreditation Board for Higher Education | Relasi | Dimana menghubungkan USU dengan lembaga yang memberinya akreditas |
| 4 Juni 1952 | Atribut | Tidak bisa berdiri sendiri, karena merupakan sebuah nilai yaitu tanggal atau waktu |
| USU → member of → ASEAN | Relasi | Disini menghubungkan para member/anggota antara ASEAN dan USU |

## 3. Eksplorasi Schema.org

| Property | Fungsi | Contoh Nilai |
|---|---|---|
| ... | ... | ... |

## 4. Pertanyaan Evaluasi

### 1. Apa perbedaan web tradisional dan Web Semantik?
Jawaban: 

Web tradisional adalah web yang isinya dapat dibaca oleh manusia. Kontennya berbentuk halaman yang berisikan teks, gambar, atau video bebas seperti tulisan biasa, seperti artikel, blog, berita, dan lain-lain. Manusia bisa membaca dan paham maksud kontennya, akan tetapi komputer hanya bisa melihat bahwa itu adalah kumpulan huruf dan kata, tanpa mengerti makna atau hubungan antar informasinya.

Sedangkan Web Semantik adalah pengembangan dari web tradisional, yang di mana informasi disusun dengan struktur yang jelas agar komputer/mesin bisa memahami. Caranya, informasi dipecah jadi entitas, atribut, dan relasi, lalu ditulis dalam pola Subject → Predicate → Object. Dengan format seperti itu, komputer bisa membaca hubungan antara data dan bisa mengelola datanya, misalnya untuk menyambungkan informasi dari berbagai sumber atau menjawab pertanyaan.

### 2. Mengapa entitas membutuhkan identifier unik?
Jawaban: 

Suatu entitas membutuhkan identifier unik agar dapat dikenali dan dibedakan secara tepat dari entitas lainnya. Identifier ini mencegah ambiguitas, terutama ketika beberapa entitas memiliki nama yang sama atau mirip. Selain itu, identifier unik mempermudah pencarian, pengelolaan, dan penghubungan data antarentitas.

Dalam Web Semantik, identifier unik digunakan untuk memastikan setiap entitas dapat diidentifikasi dan dihubungkan dengan benar. Contohnya, Universitas Sumatera Utara memiliki identifier Wikidata Q4200341, sehingga dapat dibedakan dari entitas universitas lainnya.

### 3. Jelaskan subject, predicate, dan object.
Jawaban:
- Subject adalah awal suatu pernyataan.
- Predicate adalah jenis relasi subject dengan nilainya.
- Object adalah nilai atau target tujuan relasi.
Perbedaan ketiga hal ini terletak pada peran dalam membentuk suatu pernyataan data. Contohnya: Universitas Sumatera Utara (object) -> memberOf (predicate) -> ASEAN University Network (object).

### 4. Apa keuntungan hubungan antarentitas?
Jawaban: 
- Makna yang jelas dan pencarian yang terstruktur : mencari jawaban yang terstruktur, identitas entitas juga unik global melalui URI, agar terhindar dari ambiguitas.
- Meningkatkan akurasi AI(GraphRAG) : menympan data sebagai knowledge graph untuk memberi fakta presisi dan relasi eksplisit yang dapat dilacak. kegunaannya agar kelemahan AI berbasis LLM tidak terlihat, seperti halusinasi, sumber yang tidak jelas, pengetahuan membek, dan sulit diaudit.
- Memungkinkan Inferensi Otomatis : data yang terhubung akan menarik kesimpulan dari informasi yang sudah ada walaupun faktanya tidak pernah ditulis secara eksplisist, contohnya menyimpulkan letak provinsi secara transitif.

### 5. Bagaimana Knowledge Graph membantu AI?
Jawaban: 

Menurut kelompok kami, **Knowledge Graph dapat membantu sistem pencarian atau AI dalam memahami informasi dengan cara menunjukkan hubungan antar informasi secara lebih jelas**. Jadi, AI tidak hanya melihat informasi sebagai kumpulan kata, tetapi juga bisa memahami keterkaitan antara satu entitas dengan entitas lainnya. Misalnya, sistem dapat mengetahui bahwa Menara Eiffel berada di Paris, dan Paris merupakan ibu kota Prancis. Dengan begitu, sistem pencarian atau AI dapat memberikan informasi yang lebih relevan, memahami konteks pertanyaan dengan lebih baik, serta menghasilkan jawaban yang lebih tepat.
