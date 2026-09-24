## Ontology mini kampus
- IRI dasar: (https://example.org/ontology/kampus#)
- Domain: Kampus

| Komponen | Isi yang dibuat |
| :--- | :--- |
| **Class** | `Pizza` |
| **Subclass** | `CheesyPizza` |
| **Object property** | `hasTopping` |
| **Datatype property** | `hasCaloricContent` |
| **Individual** | `America` |
| **Axiom/disjointness** | `DisjointClasses: Pizza, PizzaBase, PizzaTopping` |

## Layer Cake
Jelaskan posisi ontology dalam Semantic Web Layer Cake: Ontology/OWL merupakan lapisan yang berada di atas RDF/RDFS dan di bawah SPARQL dalam Semantic Web Layer Cake. RDF/RDFS menjadi dasar untuk merepresentasikan data, hubungan, dan kosakata, sedangkan ontology/OWL menambahkan makna yang lebih kaya dengan mendefinisikan konsep, hubungan, dan aturan dalam suatu domain. Dengan demikian, ontology membantu memberikan struktur dan makna yang lebih jelas pada data RDF/RDFS serta mendukung proses penalaran. Setelah struktur dan makna data tersedia, SPARQL dapat digunakan untuk melakukan query terhadap graph RDF tersebut.

## Perbandingan serialisasi
- Perbedaan Turtle dan RDF/XML: 
a. Format Penulisan : Turtle (ttl) menggunakan tipe penulisan yang ringkas dan berbasis prefix dengan menggunakan tanda ( ; ) atau ( , ) untuk menghemat baris. Di sisi lain, RDF/XML (rdf) menggunakan struktur dengan tag pembuka dan penutup ( <rdf:RDF> , <owl:Class>, dll) sehingga terlihat lebih panjang.
b. Deklarasi dan Penggunaan URI : Prefix pada "ttl" didefinisikan di bagian atas dengan @prefix secara singkat, sedangkan pada  "rdf" deklarasi namespace langsung didefinisikan di dalam elemen root XML dengan atribut "xmlns".
- Kesamaan makna: Kedua file merepresentasikan model data yang sama, yaitu memuat logika ontologi kampus yang identik seperti, hierarki class, properti, individual, hingga aturan disjoint yang telah dibuat. Hal ini berarti, keduanya menghasilkan graf pengetahuan yang setara.

## Refleksi
1. Apa perbedaan ontology dan taksonomi?

2. Mengapa domain pada OWL bukan constraint database?

3. Mengapa kosakata yang sudah ada sebaiknya dipakai kembali sebelum membuat yang baru?

    Kosakata yang sudah ada sebaiknya digunakan kembali agar data memiliki istilah dan makna yang sudah umum digunakan. Hal ini juga membuat data lebih mudah dipahami dan dihubungkan dengan data dari sumber lain. Kosakata baru sebaiknya dibuat hanya jika belum ada istilah yang sesuai dengan kebutuhan.

