# Pertemuan 7 - Serialisasi RDF

## Artefak
- Graf asal: [jumlah triple]
- Format ekspor: Turtle, JSON-LD, N-Triples
- Named graph: [nama graf 1] dan [nama graf 2]

## Reifikasi dan provenance
- Triple yang dianotasi: [isi]
- Creator: [isi]
- Date: [isi]
- Source: [isi]

## Tabel Membandingkan Serialisasi RDF
| Format | Kekuatan Utama | Skenario Tepat |
|---|---|---|
| Tutle | Ringkas dan mudah dibaca manusia | Membuat dan memeriksa data RDF secara manual karena penulisannya sederhana |
| JSON-LD | Cocok web/API dan HTML | Menghubungkan data semantik dengan aplikasi web dan API yang menggunakan JSON |
| RDF/XML | Kompatibilitas data lama | Cocok ketika harus bertukar data dengan aplikasi lama yang menggunakan formal RDF/XML |
| N-Triples | Satu triple per baris; stabil untuk diff | Data RDF berukuran besar, pengolahan otomatis, dan memeriksa perubahan data |
| N-Quads | Menambahkan konteks graf | Ketika data berasal dari beberapa graf dan setiap pernyataan perlu kita tau dari mana asal grafnya |

**Kesimpulan :** Jika ingin membaca lebih mudah menggunakan Turtle. Untuk aplikasi web cocoknya menggunakan JSON-LD. N-Quads cocok kalau data memeiliki beberapa konteks graf.

## Perbandingan
- Format paling mudah dibaca manusia: [isi dan alasan]
- Format untuk HTML/API: [isi dan alasan]
- Perbedaan reifikasi klasik dan RDF-star: [isi]

## Refleksi
1. Mengapa named graph berguna saat menggabungkan data dari sumber berbeda?
2. Mengapa provenance penting untuk sebuah triple?
3. Format apa yang Anda pilih untuk git diff, dan mengapa?
