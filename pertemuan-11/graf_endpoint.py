
import csv
from pathlib import Path
from SPARQLWrapper import SPARQLWrapper, TURTLE
from rdflib import Graph

# Ganti dengan email dan NIM kamu
UA = "KelasWebSemantik/1.0 (chyntiara@students.usu.ac.id)"

# Membaca query Q6
q6 = Path("queries/q6_universitas_graf.rq").read_text(
    encoding="utf-8"
)

# Mengirim query ke Wikidata satu kali
sp = SPARQLWrapper(
    "https://query.wikidata.org/sparql",
    agent=UA
)

sp.setQuery(q6)
sp.setReturnFormat(TURTLE)
sp.setOnlyConneg(True)

# Mengambil hasil dan memasukkannya ke graf RDF
data = sp.queryAndConvert()

try:
    g = Graph().parse(data=data, format="turtle")
except Exception:
    print("Gagal memproses Turtle. Awal balasan endpoint:")
    print(str(data)[:200])
    raise

print("Triple dari Wikidata:", len(g))

# Menyimpan graf ke file Turtle
g.serialize("pertemuan-11/universitas.ttl", format="turtle")

# Query lokal pada graf
q_kota = """
PREFIX ex: <https://contoh.github.io/web-semantik/251402022/kampus#>

SELECT ?kota (COUNT(DISTINCT ?u) AS ?jumlah)
WHERE {
    ?u a ex:Universitas ;
       ex:lokasi ?kota .
}
GROUP BY ?kota
ORDER BY DESC(?jumlah) ?kota
"""

# Menyimpan hasil query lokal ke CSV
with open("pertemuan-11/universitas_per_kota.csv", 
    "w", 
    newline="", 
    encoding="utf-8"
    ) as f:
    w = csv.writer(f)
    w.writerow(["kota", "jumlah_universitas"])

    for b in g.query(q_kota):
        w.writerow([str(b.kota), int(b.jumlah)])

print("CSV tersimpan: pertemuan-11/universitas_per_kota.csv")
