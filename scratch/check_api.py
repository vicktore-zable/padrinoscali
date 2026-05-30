import urllib.request
import json

req = urllib.request.Request(
    'https://edisongiraldo.com/aratio/registro-lider.php?action=lideres&campana_id=2&search=a',
    headers={'User-Agent': 'Mozilla/5.0'}
)
r = urllib.request.urlopen(req)
d = json.loads(r.read().decode('utf-8'))
print(f"Total resultados: {len(d['data'])}")
for x in d['data']:
    print(f"  - {x['nombres']} {x['apellidos']} ({x['perfil']})")
