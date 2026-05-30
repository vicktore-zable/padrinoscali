import os, glob, re

pages_dir = r'h:\Mi unidad\2026\Cali\Edisongiraldo.com\Aratio-App-Mirror\pages'
files = glob.glob(os.path.join(pages_dir, '*.php'))

for path in files:
    with open(path, 'r', encoding='utf-8') as f:
        content = f.read()

    original = content
    # Replace fetch('/api/...
    content = re.sub(r"fetch\('(/api/)", r"fetch('/aratio\1", content)
    content = re.sub(r'fetch\("(/api/)', r'fetch("/aratio\1', content)
    content = re.sub(r"fetch\(`(/api/)", r"fetch(`/aratio\1", content)

    # Replace fetch('/api_...
    content = re.sub(r"fetch\('(/api_)", r"fetch('/aratio\1", content)
    content = re.sub(r'fetch\("(/api_)', r'fetch("/aratio\1', content)
    content = re.sub(r"fetch\(`(/api_)", r"fetch(`/aratio\1", content)

    # Replace fetch('/territorios/...
    content = re.sub(r"fetch\('(/territorios/)", r"fetch('/aratio\1", content)
    content = re.sub(r'fetch\("(/territorios/)', r'fetch("/aratio\1', content)
    content = re.sub(r"fetch\(`(/territorios/)", r"fetch(`/aratio\1", content)

    # Replace fetch('/?...
    content = re.sub(r"fetch\('(/\?)", r"fetch('/aratio\1", content)
    content = re.sub(r'fetch\("(/\?)', r'fetch("/aratio\1', content)
    content = re.sub(r"fetch\(`(/\?)", r"fetch(`/aratio\1", content)

    if content != original:
        with open(path, 'w', encoding='utf-8') as f:
            f.write(content)
        print(f'Fixed {os.path.basename(path)}')
