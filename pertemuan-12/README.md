
# Pertemuan 12 — Inferensi dan Reasoning

## Langkah 1 - Closure dengan tangan
| # | Triple turunan | Aturan | Premis |
|---|----------------|--------|--------|
| 1 | `ex:budi a ex:Dosen` | `rdfs2` | `ex:membimbing rdfs:domain ex:Dosen` + `ex:budi ex:membimbing ex:andi` |
| 2 | `ex:andi a ex:Mahasiswa` | `rdfs3` | `ex:membimbing rdfs:range ex:Mahasiswa` + `ex:budi ex:membimbing ex:andi` |
| 3 | `ex:budi ex:mengenal ex:andi` | `rdfs7` | `ex:membimbing rdfs:subPropertyOf ex:mengenal` + `ex:budi ex:membimbing ex:andi` |
| 4 | `ex:DosenTamu rdfs:subClassOf ex:Pegawai` | `rdfs11` | `ex:DosenTamu rdfs:subClassOf ex:Dosen` + `ex:Dosen rdfs:subClassOf ex:Pegawai` |
| 5 | `ex:Dosen rdfs:subClassOf ex:Orang` | `rdfs11` | `ex:Dosen rdfs:subClassOf ex:Pegawai` + `ex:Pegawai rdfs:subClassOf ex:Orang` |
| 6 | `ex:sari a ex:Dosen` | `rdfs9` | `ex:DosenTamu rdfs:subClassOf ex:Dosen` + `ex:sari a ex:DosenTamu` |
| 7 | `ex:andi a ex:Orang` | `rdfs9` | `ex:Mahasiswa rdfs:subClassOf ex:Orang` + `ex:andi a ex:Mahasiswa` |
| 8 | `ex:DosenTamu rdfs:subClassOf ex:Orang` | `rdfs11` | `ex:DosenTamu rdfs:subClassOf ex:Pegawai` + `ex:Pegawai rdfs:subClassOf ex:Orang` |
| 9 | `ex:budi a ex:Pegawai` | `rdfs9` | `ex:Dosen rdfs:subClassOf ex:Pegawai` + `ex:budi a ex:Dosen` |
| 10 | `ex:sari a ex:Pegawai` | `rdfs9` | `ex:Dosen rdfs:subClassOf ex:Pegawai` + `ex:sari a ex:Dosen` |
| 11 | `ex:budi a ex:Orang` | `rdfs9` | `ex:Dosen rdfs:subClassOf ex:Orang` + `ex:budi a ex:Dosen` |
| 12 | `ex:sari a ex:Orang` | `rdfs9` | `ex:Dosen rdfs:subClassOf ex:Orang` + `ex:sari a ex:Dosen` |

- Jumlah triple turunan bermakna: 12
- Triple yang baru ditemukan di putaran kedua: `ex:andi a ex:Orang`, `ex:DosenTamu rdfs:subClassOf ex:Orang`, `ex:budi a ex:Pegawai`, `ex:sari a ex:Pegawai`, `ex:budi a ex:Orang`, dan `ex:sari a ex:Orang` (sesuai premis pada tabel di atas).
- Triple asserted yang tidak menurunkan apa pun, dan alasannya: `ex:andi a ex:MahasiswaBerprestasi`, karena tidak ada pernyataan `rdfs:subClassOf` (atau relasi ontologis lain) yang menghubungkan `ex:MahasiswaBerprestasi` dengan kelas lain. Reasoner tidak menyimpulkan relasi berdasarkan kemiripan nama kelas.

## Langkah 2 - Tebak lalu cek

| Soal | Profil | Tebakan | Hasil Mesin | Penjelasan bila meleset |
|------|--------|---------|-------------|-------------------------|
| A | RDFS | 3 baris | 3 baris | Tebakan tepat. Profil RDFS menurunkan tipe data melalui `rdfs:subClassOf` dari instance subkelas (`DosenTamu`, `MahasiswaBerprestasi`) ke kelas induk hingga `ex:Orang`. |
| B | OWL RL | 20 baris | 10 baris | Tebakan meleset karena asumsi kombinasi jalur terlalu banyak. OWL RL secara transitif (`owl:TransitiveProperty`) menurunkan 10 kombinasi jalur hierarki lokasi dari `ex:ruang301` hingga `ex:medan`. |
| C | RDFS | 0 baris | 0 baris | Tebakan tepat. Profil RDFS tidak mengenal `owl:inverseOf`, sehingga aksioma invers diabaikan begitu saja tanpa pesan error. |
| C | OWL RL | 1 baris | 1 baris | Tebakan tepat. Profil OWL RL mengaktifkan relasi invers, berhasil menurunkan `ex:dibimbing` dari relasi `ex:membimbing`. |
| D | OWL RL | False | False | Tebakan tepat. Berdasarkan *Open World Assumption*, relasi `ex:medan` sebagai `ex:Tempat` tidak dideklarasikan secara eksplisit di data sehingga dianggap belum diketahui (`false`). |

## Langkah 3 - Reasoner pada graf kampus
- Berkas sumber dan IRI dasar graf: [isi]
- Asserted / closure / inferred / bermakna: [isi] / [isi] / [isi] / [isi]
- Triple di v4 yang tidak ada di asserted: [isi]
- Turunan yang muncul karena domain dan range ex:mengajar: [jawaban]
- Bila menambah dosen baru: [jawaban]

## Langkah 4 — Wikidata

- **Q8 / Q9:** 877 / 1343
- **Penyebab selisih:** Q8 hanya menghitung entitas universitas
  di Indonesia yang secara langsung memiliki tipe university
  (Q3918). Q9 menggunakan property path wdt:P31/wdt:P279*
  sehingga turut menghitung entitas yang bertipe subclass dari
  university. Selisih hasil kedua query adalah 466 entitas.

- **Satu subclass yang menyumbang selisih:** Polytechnic
  (politeknik, Q845392), sebanyak 332 entitas. Berdasarkan
  query analisis, entitas tersebut terhubung ke kelas university
  melalui hierarki subclass, tetapi tidak langsung memiliki
  tipe university (Q3918).

- **WDQS: materialisasi, saat query, atau tidak keduanya?**
  Pada percobaan ini, hierarki subclass ditelusuri saat query
  melalui property path wdt:P279* yang ditulis secara eksplisit.
  Hasil Q9 tidak membuktikan bahwa WDQS otomatis melakukan
  materialisasi seluruh triple turunan atau menjalankan
  penalaran RDFS. Mesin query mengikuti jalur subclass yang
  tercatat di Wikidata.

## Langkah 5 - Konsistensi
- Pesan galat RDFS / OWL RL: [isi] / [isi]
- Individu yang disebut pesan: [isi]
- Setelah baris terbalik dihapus: [isi]
- Isi laporan_kelas.csv sebelum dan sesudah: [isi]
- Dari mana dosen menjadi mata kuliah: [jawaban]

## Refleksi
1. Mengapa graf asserted disimpan terpisah dari graf inferred, dan apa akibatnya bila keduanya dicampur seperti kampus_usu_v4.ttl?

  Jawaban: Graf asserted dan inferred sebaiknya disimpan terpisah supaya kita bisa membedakan data yang memang sudah ada sejak awal dengan data yang dihasilkan oleh proses inferensi. Dengan begitu, kita lebih mudah memeriksa asal suatu informasi dan mengetahui mana fakta awal dan mana hasil penalaran. Jika keduanya dicampur seperti pada kampus_usu_v4.ttl, kita bisa kesulitan membedakan kedua jenis data tersebut. Selain itu, ketika data awal berubah, hasil inferensi yang sudah tersimpan juga perlu diperiksa kembali agar tidak ada informasi yang sudah tidak sesuai.

2. Pada Langkah 2, profil RDFS tidak memberi tahu bahwa aksioma OWL diabaikan. Bagaimana Anda akan memastikan profil yang dipakai sudah tepat untuk data Anda?

Jawaban: Untuk memastikan profil inferensi yang digunakan sudah tepat dan menghindari diabaikannya aksioma OWL secara diam-diam oleh profil RDFS, langkah utama yang harus dilakukan adalah menganalisis kebutuhan skema data (file Turtle) terlebih dahulu sebelum memilih profil. Jika data melibatkan aksioma tingkat lanjut seperti owl:TransitiveProperty atau owl:inverseOf, kita wajib menggunakan profil yang lebih tinggi seperti OWL RL, karena RDFS secara desain tidak mendukung fitur tersebut dan akan mengabaikannya tanpa pesan error. Selain itu, validasi silang dengan membandingkan hasil kueri antarprofil (misalnya RDFS vs OWL RL) perlu dilakukan untuk memastikan seluruh aturan relasi dan inferensi tereksekusi dengan benar sesuai ekspektasi data.

3. Kapan Anda memilih menulis jalur seperti wdt:P279* di query, dan kapan memilih menjalankan reasoner lalu menyimpan closure?

Jawaban: Saya memilih menggunakan wdt:P279* ketika ingin mencari hubungan antar kelas berdasarkan jalur subclass langsung melalui query, tanpa harus menjalankan proses inferensi terlebih dahulu. Cara ini cocok jika kebutuhannya hanya untuk satu atau beberapa query tertentu. Sementara itu, menjalankan reasoner dan menyimpan closure lebih cocok jika hasil inferensi akan digunakan berulang kali. Dengan begitu, hasil penalaran sudah tersedia dan tidak perlu dihitung ulang setiap kali menjalankan query.
