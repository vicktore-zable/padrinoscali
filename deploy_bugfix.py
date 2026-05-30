# -*- coding: utf-8 -*-
import sys
sys.stdout.reconfigure(encoding='utf-8')
#!/usr/bin/env python3
"""
Deploy bugfix: sube los 3 archivos corregidos a produccion.
Archivos:
  - api/organizaciones.php
  - mod_organizaciones/dashboard.php
  - mod_diaD/index.php
"""
import paramiko
import os

HOST     = "157.173.208.254"
PORT     = 65002
USER     = "u577647812"
PASS     = 'E=j$`01yHi^?XfpoM@|CD"5H4'
REMOTE   = "/home/u577647812/domains/edisongiraldo.com/public_html/aratio"
LOCAL    = r"H:\Mi unidad\2026\Cali\Edisongiraldo.com\Aratio-App-Mirror"

FILES = [
    "api/organizaciones.php",
    "mod_organizaciones/dashboard.php",
    "mod_diaD/index.php",
]

def main():
    print(f"Conectando a {HOST}:{PORT} como {USER}...")
    ssh = paramiko.SSHClient()
    ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    ssh.connect(HOST, port=PORT, username=USER, password=PASS, timeout=30)
    sftp = ssh.open_sftp()
    print("[OK] Conexion SFTP establecida\n")

    for rel_path in FILES:
        local_path  = os.path.join(LOCAL, rel_path.replace("/", os.sep))
        remote_path = f"{REMOTE}/{rel_path}"
        
        if not os.path.exists(local_path):
            print(f"[ERROR] No encontrado localmente: {local_path}")
            continue
        
        local_size = os.path.getsize(local_path)
        print(f"Subiendo: {rel_path} ({local_size:,} bytes)")
        sftp.put(local_path, remote_path)
        
        # Verificar que se subió
        remote_stat = sftp.stat(remote_path)
        print(f"  [OK] Remoto: {remote_stat.st_size:,} bytes\n")

    sftp.close()
    ssh.close()
    print("[DONE] Deploy completado. Todos los archivos actualizados en produccion.")

if __name__ == "__main__":
    main()
