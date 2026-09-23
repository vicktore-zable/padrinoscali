#!/usr/bin/env python3
import paramiko
import os
import sys

host = "157.173.208.254"
port = 65002
user = "u577647812"
password = 'E=j$`01yHi^?XfpoM@|CD"5H4'

remote_base = "/home/u577647812/domains/edisongiraldo.com/public_html/aratio"

files = [
    ("pages/campanas.php", "pages/"),
    ("pages/candidatos.php", "pages/"),
    ("pages/colaboradores.php", "pages/"),
    ("pages/colaboradores_red.php", "pages/"),
    ("api/colaboradores.php", "api/"),
    ("api/territorios.php", "api/"),
]

local_base = r"F:\xampp2\htdocs\aratio"

print(f"Connecting to {host}:{port}...")
ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())

try:
    ssh.connect(host, port=port, username=user, password=password)
    print("Connected!")

    sftp = ssh.open_sftp()

    for local_file, remote_dir in files:
        src = os.path.join(local_base, local_file)
        dst = os.path.join(remote_base, remote_dir, os.path.basename(local_file))

        print(f"Uploading {local_file} -> {dst}")
        sftp.put(src, dst)
        print(f"  ✓ Done")

    sftp.close()
    ssh.close()
    print("\n¡Todos los archivos subidos!")

except Exception as e:
    print(f"Error: {e}")
    sys.exit(1)
