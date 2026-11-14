
from pathlib import Path
from rdflib import Graph

# Membaca graf kampus dari Pertemuan 9
SUMBER = Path("../pertemuan-09/kampus_usu_v2.ttl")

# Alternatif jika sumber berada dalam folder yang berbeda
if not SUMBER.exists():
    SUMBER = Path("pertemuan-09/kampus_usu_v2.ttl")

g = Graph().parse(SUMBER, format="turtle")
print("Sumber:", SUMBER, "| triple awal:", len(g))

PREFIX = """
PREFIX ex: <https://contoh.github.io/web-semantik/221202001/kampus#>
PREFIX foaf: <http://xmlns.com/foaf/0.1/>
PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
PREFIX schema: <https://schema.org/>
"""

# A. Pemetaan ke schema.org
q_peta = PREFIX + """
CONSTRUCT {
    ?d a schema:Person ;
       schema:name ?nama .
}
WHERE {
    ?d a ex:Lecturer ;
       foaf:name ?nama .
}
"""

peta = Graph()

for triple in g.query(q_peta):
    peta.add(triple)

print("Triple hasil pemetaan:", len(peta))
peta.serialize("dosen_schema.ttl", format="turtle")

# B. Materialisasi kelas
g.update(PREFIX + """
INSERT DATA {
    ex:Lecturer rdfs:subClassOf ex:Pegawai .
    ex:Pegawai rdfs:subClassOf foaf:Person .
}
""")

q_orang = PREFIX + """
SELECT ?x WHERE {
    ?x a foaf:Person .
}
"""

print("foaf:Person sebelum:", len(g.query(q_orang)))

turunan = g.query(PREFIX + """
CONSTRUCT {
    ?x a ?super .
}
WHERE {
    ?x a/rdfs:subClassOf+ ?super .
}
""")

baru = [triple for triple in turunan if triple not in g]

for triple in baru:
    g.add(triple)

print("Triple turunan baru:", len(baru))
print("foaf:Person sesudah:", len(g.query(q_orang)))

g.serialize("kampus_usu_v4.ttl", format="turtle")
