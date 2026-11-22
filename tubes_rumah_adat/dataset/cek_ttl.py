from pathlib import Path
import sys

from rdflib import Graph, Namespace, OWL, RDF

if hasattr(sys.stdout, "reconfigure"):
    sys.stdout.reconfigure(encoding="utf-8")

# Arahkan ke file ontology/rumah_adat.ttl relatif terhadap root project
ttl_path = Path(__file__).resolve().parent.parent / "ontology" / "rumah_adat.ttl"

# 1. Muat dan baca file rumah_adat.ttl
g = Graph()
try:
    g.parse(ttl_path, format="turtle")
    print(f"✅ SUKSES! Sintaks Turtle 100% VALID (0 error): {ttl_path.name}")
    print(f"📊 Total Triples terbentuk: {len(g)} triples\n")
except Exception as e:
    print("❌ ERROR SINTAKS DITEMUKAN:")
    print(e)
    sys.exit(1)

# 2. Hitung jumlah data per kelas (sesuai @prefix ra di ontology/rumah_adat.ttl)
RA = Namespace("http://example.org/ontology/rumah-adat#")
kelas_list = [
    "RumahAdat",
    "Suku",
    "Marga",
    "BahasaDaerah",
    "Wilayah",
    "Tradisi",
    "Ornamen",
]

print("📋 Ringkasan Jumlah Entitas:")
for kelas in kelas_list:
    jumlah = len(list(g.subjects(RDF.type, RA[kelas])))
    print(f"   - {kelas}: {jumlah} data")

# 3. Cek cepat kelengkapan relasi (objek yang dirujuk tapi belum dideklarasikan tipenya)
typed_nodes = {s for s, _, _ in g.triples((None, RDF.type, None))}
obj_props = list(g.subjects(RDF.type, OWL.ObjectProperty))
missing_targets = [
    (s.split("#")[-1], p.split("#")[-1], o.split("#")[-1])
    for p in obj_props
    for s, _, o in g.triples((None, p, None))
    if o not in typed_nodes
]

print()
if missing_targets:
    print(f"⚠️ Ditemukan {len(missing_targets)} referensi URI tanpa rdf:type:")
    for s, p, o in missing_targets[:10]:
        print(f"   - {s} -> {p} -> {o}")
else:
    print("✅ Semua target relasi (ObjectProperty) memiliki deklarasi rdf:type.")
