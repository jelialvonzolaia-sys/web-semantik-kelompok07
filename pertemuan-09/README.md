# Pertemuan 9 - SPARQL Dasar: Tell & Ask dan Endpoint

## Langkah 1 - Tabel binding
| Tahap | ?dosen | ?mk | ?namaMK |
|-------|--------|-----|---------|
| [isi] | [isi]  | [isi] | [isi] |
- Jumlah baris hasil akhir: [isi]
- Peran ?mk: [isi]

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
2. Kapan Anda memakai graf lokal (rdflib) dan kapan endpoint publik?
3. Sebutkan satu hasil query yang mengejutkan dan jelaskan penyebabnya.
