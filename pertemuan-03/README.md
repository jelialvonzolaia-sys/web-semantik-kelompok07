# Latihan Pertemuan 3 - JSON-LD dan Structured Data

## Identitas
- Nama: ISI_NAMA
- NIM: ISI_NIM

## Struktur Hasil
- `profil_saya.jsonld`
- `profil_perbaikan.jsonld`
- `seminar.html`
- folder `screenshots`

## 1. JSON Biasa dan JSON-LD
1. Perbedaan fungsi kunci: 

   Pada kunci `nama/pekerjaan` pada JSON biasa yaitu hanya atribut lokal untuk membuat struktur data yang hanya dipahami oleh pembuat skema. Sedangkan `nama/jobtitle` pada JOSN-LD, karena sudah terstandarisasi kosakata global dari schema.org dan langsung dipetakan ke IRI global, jadi datanya tidak hanya sekedar terstruktur secara sintaks melainkan bisa dipahami oleh mesin yang memiliki makna universal.
2. Fungsi `@context`, `@type`, dan `@id`:
   
   - `@context` : menentukan konteks yang digunakan yang memberitahukan mesin arti kosakata pada JSON-LD
   - `@type` : menentukan jenis entitas yang dideskripsikan yang penulisannya *case-sensitive* dan memberitahu mesin jenis objek dan entitasnya.
   - `@id` : memberikan identitas unik kepada entitas atau IRI dari node dimana ini digunakan untuk memberikan URI pada entitas.
3. Node tanpa `@id`: 

   Node akan berstatus sebagai *blank node* yang membuat node tidak memiliki identitas dan tidak bisa dirujuk oleh entitas.

## 2. Pemeriksaan schema.org
1. Alasan memilih tipe paling spesifik: 
2. Nama properti dan bahasa nilai: ...
3. Manfaat array pada `knowsAbout`: ...

## 3. Perbaikan Lima Kesalahan
| No. | Bagian Salah | Alasan | Perbaikan |
|---|---|---|---|
| 1 | "@type": "person" | Penulisan tipe yang ditentukan oleh Schema.org harus diawali huruf kapital | "@type": "Person" |
| 2 | 'name': "Rina Anggraini" | Tanda kutip satu ditandai tidak valid sebagai JSON | "name": "Rina Anggraini" |
| 3 | "birthDate": "12 September 2004" | Format tanggal yang sah adalah dengan sistem penanggalan Gregorian | "birthDate": "2004-09-12" |
| 4 | "nomorInduk": "221401001" | Properti tersebut bukan properti standar di Schema.org untuk penyimpanan identitas seseorang | "identifier": "221401001" |
| 5 | "nomorInduk": "221401001", | Properti terakhir tidak boleh diikuti tanda koma | "nomorInduk": "221401001" |

## 4. Triple dari JSON-LD Playground
Tuliskan satu baris N-Quads yang terbentuk:

```text
ISI_TRIPLE
```

## 5. Hasil Validasi
- Schema Markup Validator: ...
- Rich Results Test: ...
- JSON-LD Playground: ...

## 6. Refleksi
1. Mengapa `@context` disebut jembatan menuju makna?
2. Apa perbedaan fungsi Schema Markup Validator dan Rich Results Test?
3. Mengapa isi JSON-LD harus sama dengan konten yang terlihat pada halaman?

## Bukti
![Schema Markup Validator](screenshots/profil-schema-validator.png)
![JSON-LD Playground](screenshots/profil-playground.png)
![Rich Results Test](screenshots/seminar-rich-results.png)
