
# Pertemuan 12 — Inferensi dan Reasoning

Dokumentasi tugas kelompok Web Semantik Pertemuan 12.


## Langkah 2 - Tebak lalu cek

| Soal | Profil | Tebakan | Hasil Mesin | Penjelasan bila meleset |
|------|--------|---------|-------------|-------------------------|
| A | RDFS | 3 baris | 3 baris | Tebakan tepat. Profil RDFS menurunkan tipe data melalui `rdfs:subClassOf` dari instance subkelas (`DosenTamu`, `MahasiswaBerprestasi`) ke kelas induk hingga `ex:Orang`. |
| B | OWL RL | 20 baris | 10 baris | Tebakan meleset karena asumsi kombinasi jalur terlalu banyak. OWL RL secara transitif (`owl:TransitiveProperty`) menurunkan 10 kombinasi jalur hierarki lokasi dari `ex:ruang301` hingga `ex:medan`. |
| C | RDFS | 0 baris | 0 baris | Tebakan tepat. Profil RDFS tidak mengenal `owl:inverseOf`, sehingga aksioma invers diabaikan begitu saja tanpa pesan error. |
| C | OWL RL | 1 baris | 1 baris | Tebakan tepat. Profil OWL RL mengaktifkan relasi invers, berhasil menurunkan `ex:dibimbing` dari relasi `ex:membimbing`. |
| D | OWL RL | False | False | Tebakan tepat. Berdasarkan *Open World Assumption*, relasi `ex:medan` sebagai `ex:Tempat` tidak dideklarasikan secara eksplisit di data sehingga dianggap belum diketahui (`false`). |

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

### Berkas Query

- `queries/q8_univ_tanpa_jalur.rq`
- `queries/q9_univ_dengan_jalur.rq`

### Bukti Screenshot

- `screenshots/output-q8.png` — hasil Q8 (877).
- `screenshots/output-wikidata.png` — hasil Q9 (1343).
- `screenshots/output-subclass.png` — hasil analisis politeknik (332).

**Tanggal pengujian:** 16 November 2026.

## Refleksi
1. Mengapa graf asserted disimpan terpisah dari graf inferred, dan apa akibatnya bila keduanya dicampur seperti kampus_usu_v4.ttl?

2. Pada Langkah 2, profil RDFS tidak memberi tahu bahwa aksioma OWL diabaikan. Bagaimana Anda akan memastikan profil yang dipakai sudah tepat untuk data Anda?
Jawaban: Untuk memastikan profil inferensi yang digunakan sudah tepat dan menghindari diabaikannya aksioma OWL secara diam-diam oleh profil RDFS, langkah utama yang harus dilakukan adalah menganalisis kebutuhan skema data (file Turtle) terlebih dahulu sebelum memilih profil. Jika data melibatkan aksioma tingkat lanjut seperti owl:TransitiveProperty atau owl:inverseOf, kita wajib menggunakan profil yang lebih tinggi seperti OWL RL, karena RDFS secara desain tidak mendukung fitur tersebut dan akan mengabaikannya tanpa pesan error. Selain itu, validasi silang dengan membandingkan hasil kueri antarprofil (misalnya RDFS vs OWL RL) perlu dilakukan untuk memastikan seluruh aturan relasi dan inferensi tereksekusi dengan benar sesuai ekspektasi data.

3. Kapan Anda memilih menulis jalur seperti wdt:P279* di query, dan kapan memilih menjalankan reasoner lalu menyimpan closure?