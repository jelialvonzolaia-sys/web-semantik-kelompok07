# Pertemuan 9 - SPARQL Dasar: Tell & Ask dan Endpoint

## Langkah 1 - Tabel binding
| Tahap | ?dosen | ?mk | ?namaMK |
|Pola 1, Baris 1|3x:budi|ex:tif2305|---------|
|Pola 1, Baris 2|ex:sari|ex:tif2410|---------|
|Pola 1, Baris 3|ex:sari|ex:tif2305|---------|
|Pola 2|--|ex:tif2305, ex:tif2410|"Web Semantik", "Data Mining"|
|Hasil Akhir|ex:budi, ex:sari, ex:sari|ex:tif2305, ex:tif2410, ex:tif2305| ex:tif2305 "Web Semantik", "Data Mining", "Web Semantik"|
- Jumlah baris hasil akhir:[3 baris]
- Peran ?mk: [Karena ?mk tidak diminta dalam klaim SELECT, melainkan hanya bertindak sebagai jembatan (join) untuk menghubungkan data dosen dengan nama mata kuliah secara logis.]

## Langkah 2 - ASK dan dunia terbuka
1. Hasil ASK { ex:budi ex:mengajar ex:tif2410 } & Tafsir OWA:

   Jawaban :

   - Hasil : false
   - Di OWA, false itu artinya data tersebut belum tercatat/belum diketahui di database, bukan berarti Budi pasti tidak mengajar TIF2410.

2. Hasil SELECT ?a ?b & Kenapa ?a Bisa Sama Dengan ?b

   Jawaban : 

   - Jumlah baris ada 5 baris:
     1. ?a = ex:budi, ?b = ex:budi (sama-sama mengajar ex:tif2305)
     2. ?a = ex:budi, ?b = ex:sari (sama-sama mengajar ex:tif2305)
     3. ?a = ex:sari, ?b = ex:budi (sama-sama mengajar ex:tif2305)
     4. ?a = ex:sari, ?b = ex:sari (sama-sama mengajar ex:tif2305)
     5. ?a = ex:sari, ?b = ex:sari (sama-sama mengajar ex:tif2410)
   - ?a = ?b: Karena di query-nya tidak pakai FILTER(?a != ?b), jadi SPARQL bebas memasangkan dosen dengan dirinya sendiri dari pola triple yang sama.

3. Penyebab Pola "Web Semantik" dapat 0 Baris

   Jawaban :

   Karena SPARQL membaca string biasa ("Web Semantik") dan string yang pakai tag bahasa ("Web Semantik"@id) sebagai dua hal yang beda. Karena tidak exact match, datanya tidak ketemu.

## Langkah 3 - Query lokal
- IRI dasar graf: [isi]
- Jumlah triple awal / akhir: [isi] / [isi]
- Fakta yang ditambahkan: [isi]

## Langkah 4 - Wikidata
- Jumlah baris Q1 dengan wdt:P31: [isi]
- Jumlah baris Q1 dengan wdt:P31/wdt:P279*: [isi]
- Penjelasan perbedaan: [isi]

## Langkah 5 - Endpoint dari Python
- agent=UA: [isi]
- setReturnFormat(JSON): [isi]
- b.get("kotaLabel", {}): [isi]

## Refleksi
1. Di titik mana SPARQL berbeda dari SQL?

Struktur Data: SQL memakai tabel berisikan baris dan kolom yang kaku, sedangkan SPARQL memakai graf triple (subjek-predikat-objek) yang lebih fleksibel.

Mekanisme Join: SPARQL melakukan join secara otomatis hanya dengan menyamakan nama variabel (seperti ?mk), tanpa perlu perintah JOIN ... ON ... seperti di SQL.

Asumsi Pengetahuan: SPARQL memakai Open World Assumption (data yang tidak tercatat belum tentu salah), sedangkan SQL memakai dunia tertutup (data yang tidak ada langsung dianggap tidak ada).

2. Kapan Anda memakai graf lokal (rdflib) dan kapan endpoint publik?

Penggunaan graf lokal dengan rdflib tepat dilakukan saat kita mengelola data privat skala kecil milik sendiri—seperti data internal Kampus USU—serta ketika memerlukan akses offline atau ingin memodifikasi data secara langsung menggunakan perintah INSERT DATA. Sebaliknya, endpoint publik digunakan ketika kita membutuhkan akses ke pengetahuan global berskala besar yang dikelola secara real-time oleh pihak luar, contohnya saat mengambil data universitas atau sungai dari Wikidata tanpa perlu menyimpan seluruh basis data tersebut di penyimpanan lokal.

3. Sebutkan satu hasil query yang mengejutkan dan jelaskan penyebabnya.

   Jawaban :

   - **Hasil** : Query pencocokan nama mata kuliah ?mk ex:nama "Web Semantik" mengembalikan 0 baris (kosong), padahal data "Web Semantik"@id jelas-jelas ada di dalam graf.
   - **Penyebab** : Terjadi karena perbedaan tipe data literal. SPARQL memperlakukan string biasa ("Web Semantik") dan string yang diberi tag bahasa ("Web Semantik"@id) sebagai dua nilai yang berbeda. Karena SPARQL melakukan pencocokan secara persis (exact match), pencarian gagal jika tag bahasanya tidak diikutkan.
