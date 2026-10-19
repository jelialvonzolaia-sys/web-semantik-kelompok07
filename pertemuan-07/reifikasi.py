from rdflib import Graph, URIRef, Literal, Namespace
from rdflib.namespace import RDF, DCTERMS, XSD

g = Graph()
g.parse("kampus_usu.ttl", format="turtle")
print(f"Jumlah triple awal: {len(g)}")

EX = Namespace("https://contoh.github.io/web-semantik/221202001/kampus#")
stmt = URIRef(EX + "stmt-01")

g.add((stmt, RDF.type, RDF.Statement))
g.add((stmt, RDF.subject, EX.ida))
g.add((stmt, RDF.predicate, EX.mengajar))
g.add((stmt, RDF.object, EX.web_semantik))
g.add((stmt, DCTERMS.creator, EX.ida))
g.add((stmt, DCTERMS.date, Literal("2026-10-01", datatype=XSD.date)))
g.add((stmt, DCTERMS.source, Literal("Data akademik kampus")))

print(f"Jumlah triple setelah reifikasi: {len(g)}")
print(g.serialize(format="turtle"))