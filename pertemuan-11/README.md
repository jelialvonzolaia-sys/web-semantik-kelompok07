# Pertemuan 11 - CONSTRUCT dan Hierarki Data

## Langkah 1 - Solution menjadi triple
| Solution | ?d | ?mk | Triple yang dicetak |
|----------|----|-----|---------------------|
| 1 | ex:budi | ex:tif2108 | ex:tif2108 a ex:MataKuliahAktif; ex:tif2108 ex:diampuOleh ex:budi |
| 2 | ex:budi | ex:tif2104 | ex:tif2104 a ex:MataKuliahAktif; ex:tif2104 ex:diampuOleh ex:budi |
| 3 | ex:sari | ex:tif2108 | ex:tif2108 a ex:MataKuliahAktif; ex:tif2108 ex:diampuOleh ex:sari |
| 4 | ex:ida | ex:tif2104 | ex:tif2104 a ex:MataKuliahAktif; ex:tif2104 ex:diampuOleh ex:ida |

- Jumlah solution: 4
- Jumlah cetakan triple: 8 (4 solution × 2 pola template)
- Jumlah triple di graf hasil: 6
- Triple yang tercetak lebih dari sekali: ex:tif2108 a ex:MataKuliahAktif (2×, solution 1 dan 3) dan ex:tif2104 a ex:MataKuliahAktif (2×, solution 2 dan 4)

## Langkah 2 - Tebak lalu cek
| Soal | Tebakan | Hasil mesin | Penjelasan bila meleset |
|------|---------|-------------|-------------------------|
| A    | [isi]   | [isi]       | [isi]                   |
| B    | [isi]   | [isi]       | [isi]                   |
| C    | [isi]   | [isi]       | [isi]                   |
| D    | [isi]   | [isi]       | [isi]                   |

## Langkah 3 - Graf kampus lokal
- Berkas sumber dan IRI dasar graf: [isi]
- Kelas dosen dan properti nama: [isi]
- Triple hasil pemetaan: [isi]
- foaf:Person sebelum / sesudah: [isi] / [isi]
- Triple asli vs triple turunan: [jawaban]
- Dosen baru setelah program selesai: [jawaban]

## Langkah 4 - Wikidata
- Jumlah triple Q6: [isi]
- Jumlah baris Q6 versi SELECT: [isi]
- Penjelasan perbedaan: [isi]
- Q7 dengan + vs *: [isi]
- Kelas yang mengejutkan dan alasannya: [isi]

## Langkah 5 - Graf dari Python
- Jumlah triple universitas.ttl: [isi]
- Jumlah baris universitas_per_kota.csv: [isi]
- setReturnFormat(TURTLE): [isi]
- Graph().parse(data=...): [isi]
- Mengapa q_kota tidak memanggil endpoint: [isi]

## Refleksi
1. Mengapa jumlah triple hasil CONSTRUCT bisa lebih kecil dari jumlah baris SELECT dengan WHERE yang sama?
   
   **Jawab:** Soalnya SELECT menampilkan satu baris untuk setiap solution, sedangkan CONSTRUCT hasilnya berupa graf, dan graf itu himpunan, jadi triple yang sama persis cuma disimpan sekali. Kalau beberapa solution mencetak triple yang identik, hasilnya digabung jadi satu. Contohnya di tugas ini, "ex:tif2108 a ex:MataKuliahAktif" tercetak dari 2 solution (budi dan sari), tapi di graf hasil cuma ada 1. Jadi kalau templatenya cuma mencetak satu pola triple per solution, jumlah triple bisa menjadi lebih kecil dari jumlah baris SELECT karena ada yang duplikat.

2. Bila taksonomi di graf kampus Anda berubah bulan depan, apa yang terjadi pada triple turunan di kampus_usu_v4.ttl?
   
   **Jawab:** Triple turunan di kampus_usu_v4.ttl tidak ikut berubah otomatis. Triple itu cuma hasil CONSTRUCT yang sudah disimpan ke file, jadi isinya "foto" dari kondisi saat query dijalankan. Kalau taksonomi berubah, triple turunan bisa jadi sudah ketinggalan zaman (basi) atau tidak sesuai lagi dengan data aslinya. Supaya update, query CONSTRUCT-nya harus dijalankan ulang dan filenya dibuat ulang dari data yang baru.

3. Kapan Anda menyimpan hasil query sebagai tabel (CSV), dan kapan sebagai graf (Turtle)?
