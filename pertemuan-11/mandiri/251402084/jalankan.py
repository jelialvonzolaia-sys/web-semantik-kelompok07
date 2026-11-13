
from rdflib import Graph
from pathlib import Path
import sys

# Membaca nama file query dari terminal
if len(sys.argv) < 2:
    print("Penggunaan: python jalankan.py nama_query.rq")
    sys.exit(1)

nama_query = sys.argv[1]

# Membaca graf kampus
g = Graph()
nama_graf = sys.argv[2] if len(sys.argv) > 2 else "kampus.ttl"
g.parse(nama_graf, format="turtle")

# Membaca query SPARQL
query = Path(nama_query).read_text(encoding="utf-8")

# Menjalankan query CONSTRUCT
hasil = g.query(query)

# Mengubah hasil query menjadi graf RDF
graf_hasil = Graph()
for triple in hasil:
    graf_hasil.add(triple)

print("File query:", nama_query)
print("Jumlah triple hasil:", len(graf_hasil))

# Menampilkan triple yang dihasilkan
for s, p, o in graf_hasil:
    print(s, p, o)
