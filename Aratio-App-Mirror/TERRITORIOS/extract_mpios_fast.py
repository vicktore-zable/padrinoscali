
import os
import re

# Optimized script for Google Drive File Stream (Mi Unidad)
# Reads large chunks to minimize network requests
path = r'h:\Mi unidad\2025\5d\app\Multi-Campaign Management System\TERRITORIOS\VEREDAS_O76.geojson'
excluded = {'CALI', 'BUENAVENTURA', 'PALMIRA', 'JAMUNDÍ', 'JAMUNDI', 'CARTAGO', 'YUMBO'}

mpios = set()
chunk_size = 10 * 1024 * 1024 # 10MB chunks

if not os.path.exists(path):
    print(f"Error: File not found at {path}")
    exit(1)

with open(path, 'r', encoding='utf-8', errors='ignore') as f:
    while True:
        chunk = f.read(chunk_size)
        if not chunk:
            break
        
        # Use regex to find municipality names
        # Format: "NOMB_MPIO": "NAME"
        matches = re.findall(r'"NOMB_MPIO":\s*"([^"]+)"', chunk)
        for name in matches:
            name_up = name.upper().strip()
            if name_up and name_up not in excluded:
                mpios.add(name_up)

# Sort and print
print("---LISTA_INICIO---")
for m in sorted(list(mpios)):
    print(m)
print("---LISTA_FIN---")
