import os, re

path = r'h:\Mi unidad\2026\Cali\Edisongiraldo.com\Aratio-App-Mirror\pages\colaboradores.php'
with open(path, 'r', encoding='utf-8') as f:
    content = f.read()

# Replace fetch('/api/...
content = re.sub(r"fetch\('(/api/)", r"fetch('/aratio\1", content)
content = re.sub(r'fetch\("(/api/)', r'fetch("/aratio\1', content)
content = re.sub(r"fetch\(`(/api/)", r"fetch(`/aratio\1", content)

# Replace fetch('/api_...
content = re.sub(r"fetch\('(/api_)", r"fetch('/aratio\1", content)
content = re.sub(r'fetch\("(/api_)', r'fetch("/aratio\1', content)
content = re.sub(r"fetch\(`(/api_)", r"fetch(`/aratio\1", content)

with open(path, 'w', encoding='utf-8') as f:
    f.write(content)

print('Fixed colaboradores.php')
