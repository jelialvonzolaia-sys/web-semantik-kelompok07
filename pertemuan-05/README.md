# Pertemuan 5 — Ontology

## Bagian 1 — Anatomy of an Ontology

### Domain: Kampus

| Elemen Ontology | Nama | Keterangan |
|---|---|---|
| Class | `Person` | Kelas yang merepresentasikan orang yang berada dalam lingkungan kampus. |
| Class | `Course` | Kelas yang merepresentasikan mata kuliah yang tersedia di kampus. |
| Class | `Department` | Kelas yang merepresentasikan program studi atau departemen dalam kampus. |
| Subclass | `Student` | Subclass dari `Person` yang merepresentasikan mahasiswa. |
| Subclass | `Lecturer` | Subclass dari `Person` yang merepresentasikan dosen. |
| Individual | `Jona` | Individu dari class `Student` yang merepresentasikan seorang mahasiswa. |
| Individual | `DrDedy` | Individu dari class `Lecturer` yang merepresentasikan seorang dosen. |
| Individual | `WebSemantik` | Individu dari class `Course` yang merepresentasikan mata kuliah Web Semantik. |
| Object Property | `takesCourse` | Menghubungkan seorang `Student` dengan `Course` yang diambilnya. |
| Object Property | `teachesCourse` | Menghubungkan seorang `Lecturer` dengan `Course` yang diajarnya. |
| Axiom | `Student disjointWith Lecturer` | Menyatakan bahwa `Student` dan `Lecturer` merupakan dua class yang saling terpisah. |

### Struktur Sederhana Ontology

```text
Person
├── Student
└── Lecturer

Course

Department