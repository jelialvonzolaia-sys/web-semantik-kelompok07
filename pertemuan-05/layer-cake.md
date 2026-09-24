# Semantic Web Layer Cake

| Lapis | Peran | Contoh Anda |
| :--- | :--- | :--- |
| URI dan Unicode | Identitas global dan representasi karakter | URI sumber pada `metadata-sumber.ttl` |
| XML | Sintaks pertukaran data | `katalog_buku.xml` |
| RDF dan RDFS | Pernyataan graph dan kosakata dasar | JSON-LD pada `profil_saya.jsonld` |
| Ontology / OWL | Makna domain dan penalaran lebih kaya | Ontology kampus pada `ontology-kampus.owl` |
| SPARQL | Query graph RDF | Belum ada artefak terkait |
| Rules, Proof, Trust | Aturan, pembuktian, dan kepercayaan | Belum ada artefak terkait |

### Jawab singkat: mengapa ontology berada di atas RDF/RDFS dan di bawah SPARQL dalam arsitektur ini?

Ontology berada di atas RDF/RDFS karena ontology menggunakan dasar yang sudah disediakan RDF/RDFS untuk memberikan makna yang lebih terstruktur pada data, seperti konsep, hubungan, dan aturan dalam suatu domain. Sementara itu, SPARQL berada di atas ontology karena SPARQL digunakan untuk melakukan query atau mengambil informasi dari graph RDF yang sudah memiliki struktur dan makna. Jadi, urutannya menunjukkan bahwa data direpresentasikan terlebih dahulu, kemudian diberi makna, lalu dapat dicari atau diambil informasinya menggunakan SPARQL.