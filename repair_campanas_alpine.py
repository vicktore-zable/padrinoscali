# -*- coding: utf-8 -*-
"""
CORRECCIÓN DEFINITIVA: Repara el bloque Alpine.js return{} en pages/campanas.php
El script fix_all_api_paths.py danio la propiedad 'form: {}' del objeto Alpine.
"""
import paramiko, sys, io
sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8', errors='replace')

HOST="157.173.208.254"; PORT=65002; USER="u577647812"
PASSWORD='E=j$`01yHi^?XfpoM@|CD"5H4'
ARATIO="/home/u577647812/domains/edisongiraldo.com/public_html/aratio"

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(HOST, port=PORT, username=USER, password=PASSWORD, timeout=20)

sftp = client.open_sftp()

with sftp.open(f"{ARATIO}/pages/campanas.php", 'r') as f:
    content = f.read().decode('utf-8')

# The broken block (what the file has now):
broken = """            modalNuevo: false,
            modalEditar: false,
            loading: false,
            loadingMunicipios: false,
                color_primario: '#FF00FF', color_secundario: '#FFD700'
            },
            municipio_raw: '',"""

# The correct block (what it should be):
fixed = """            modalNuevo: false,
            modalEditar: false,
            loading: false,
            loadingMunicipios: false,
            form: {
                id: null, codigo: '', nombre: '', slogan: '', descripcion: '',
                estado: 'planificacion', candidato_id: '', eleccion_id: '',
                departamento: '', municipio: '',
                fecha_inicio: '', fecha_fin: '', meta_votos: '', presupuesto: '',
                color_primario: '#FF00FF', color_secundario: '#FFD700'
            },
            municipio_raw: '',"""

if broken in content:
    new_content = content.replace(broken, fixed)
    with sftp.open(f"{ARATIO}/pages/campanas.php", 'w') as f:
        f.write(new_content)
    print("CORREGIDO: pages/campanas.php - bloque form{} restaurado correctamente.")
else:
    print("El bloque roto no coincide exactamente. Mostrando contexto real:")
    # Find what's actually around 'loadingMunicipios'
    idx = content.find('loadingMunicipios: false,')
    if idx != -1:
        print(repr(content[idx:idx+300]))
    else:
        print("loadingMunicipios no encontrado")

sftp.close()
client.close()
