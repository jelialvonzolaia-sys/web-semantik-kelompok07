from rdflib import Graph, Namespace, Literal
from rdflib.namespace import RDF, FOAF, XSD

g = Graph()

EX = Namespace("https://contoh.github.io/web-semantik/221202001/kampus#")

g.bind("ex", EX)
g.bind("foaf", FOAF)

# Dosen dan mata kuliah
g.add((EX.ida, RDF.type, EX.Lecturer))
g.add((EX.ida, FOAF.name, Literal("Muhammad Isa Dadi Hasibuan S.Kom., M.Kom", lang="id")))

g.add((EX.web_semantik, RDF.type, EX.Course))
g.add((EX.web_semantik, FOAF.name, Literal("Web Semantik", lang="id")))

g.add((EX.ida, EX.mengajar, EX.web_semantik))

# Dosen dan mata kuliah (tripple baru)
g.add((EX.dedy_arisandi, RDF.type, EX.Lecturer))
g.add((EX.dedy_arisandi, FOAF.name, Literal("Dedy Arisandi S.T., M.Kom.", lang="id")))

g.add((EX.basis_data, RDF.type, EX.Course))
g.add((EX.basis_data, FOAF.name, Literal("Manajemen Sistem Basis Data", lang="id")))

g.add((EX.dedy_arisandi, EX.mengajar, EX.basis_data))


g.add((EX.lia_silviana, RDF.type, EX.Lecturer))
g.add((EX.lia_silviana, FOAF.name, Literal("Lia Silviana S.TI., M.Kom", lang="id")))

g.add((EX.matematika_diskrit, RDF.type, EX.Course))
g.add((EX.matematika_diskrit, FOAF.name, Literal("Matematika Diskrit", lang="id")))

g.add((EX.lia_silviana, EX.mengajar, EX.matematika_diskrit))


# Mahasiswa
g.add((EX.patricia, RDF.type, EX.Student))
g.add((EX.patricia, FOAF.name, Literal("Patricia", lang="id")))
g.add((EX.patricia, EX.mengambil, EX.web_semantik))

g.add((EX.jeli, RDF.type, EX.Student))
g.add((EX.jeli, FOAF.name, Literal("Jeli", lang="id")))
g.add((EX.jeli, EX.mengambil, EX.basis_data))


# Jumlah kredit
g.add((EX.web_semantik, EX.jumlahKredit, Literal(3, datatype=XSD.integer)))
g.add((EX.basis_data, EX.jumlahKredit, Literal(3, datatype=XSD.integer)))
g.add((EX.matematika_diskrit, EX.jumlahKredit, Literal(3, datatype=XSD.integer)))


# Hari kuliah
g.add((EX.web_semantik, EX.hariKuliah, Literal("Jumat", lang="id")))
g.add((EX.basis_data, EX.hariKuliah, Literal("Rabu", lang="id")))
g.add((EX.matematika_diskrit, EX.hariKuliah, Literal("Kamis", lang="id")))


print(g.serialize(format="turtle"))

g.serialize("kampus_usu.ttl", format="turtle")
g.serialize("kampus_usu.jsonld", format="json-ld", indent=2)

