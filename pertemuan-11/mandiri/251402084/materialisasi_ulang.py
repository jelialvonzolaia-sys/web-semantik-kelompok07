
from rdflib import Graph

# Membaca graf awal
g = Graph().parse("kampus.ttl", format="turtle")
awal = set(g)

# Menambahkan Rina sebagai DosenTetap
RINA = """
@prefix ex: <http://contoh.id/kampus/> .
ex:rina a ex:DosenTetap .
"""

g.parse(data=RINA, format="turtle")

print("Triple setelah menambah Rina:", len(g))

# Menjalankan materialisasi ulang
QUERY = """
CONSTRUCT {
    ?x a ?super .
}
WHERE {
    ?x a/rdfs:subClassOf* ?super .
}
"""

turunan = g.query(QUERY)

for triple in turunan:
    g.add(triple)

# Menghitung triple turunan dari graf awal
jumlah_turunan = len(set(g) - awal) - 1

print("Jumlah triple turunan:", jumlah_turunan)
print("Total triple akhir:", len(g))

# Menyimpan hasil materialisasi
g.serialize("kampus_turunan.ttl", format="turtle")

print("Berhasil menyimpan kampus_turunan.ttl")
