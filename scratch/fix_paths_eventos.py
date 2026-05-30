# -*- coding: utf-8 -*-
import re

file_path = r"h:\Mi unidad\2026\Cali\Edisongiraldo.com\Aratio-App-Mirror\pages\eventos.php"

with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

# Replace fetch('/api/ with fetch('api/
new_content = re.sub(r"fetch\(['\"]/api/", "fetch('api/", content)

# Also fix urlRegistro if it has absolute path issues
# urlRegistro: window.location.origin + '/registro_asistencia.php?evento=' + id;
# Change to relative or correctly detect subdirectory
# Actually, the user is in /aratio/dashboard.php
# So it should be /aratio/registro_asistencia.php
new_content = new_content.replace("window.location.origin + '/registro_asistencia.php", "window.location.origin + window.location.pathname.substring(0, window.location.pathname.lastIndexOf('/')) + '/registro_asistencia.php")

with open(file_path, 'w', encoding='utf-8') as f:
    f.write(new_content)

print("Path replacement complete.")
