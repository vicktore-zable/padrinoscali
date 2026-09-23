#!/usr/bin/env python3
import paramiko
import os

host = "157.173.208.254"
port = 65002
user = "u577647812"
password = 'E=j$`01yHi^?XfpoM@|CD"5H4'

# Files to upload
files = [
    (
        "F:/xampp2/htdocs/aratio/pages/campanas.php",
        "/home/u577647812/domains/edisongiraldo.com/public_html/aratio/pages/campanas.php",
    ),
    (
        "F:/xampp2/htdocs/aratio/pages/candidatos.php",
        "/home/u577647812/domains/edisongiraldo.com/public_html/aratio/pages/candidatos.php",
    ),
    (
        "F:/xampp2/htdocs/aratio/pages/colaboradores.php",
        "/home/u577647812/domains/edisongiraldo.com/public_html/aratio/pages/colaboradores.php",
    ),
    (
        "F:/xampp2/htdocs/aratio/pages/colaboradores_red.php",
        "/home/u577647812/domains/edisongiraldo.com/public_html/aratio/pages/colaboradores_red.php",
    ),
    (
        "F:/xampp2/htdocs/aratio/api/colaboradores.php",
        "/home/u577647812/domains/edisongiraldo.com/public_html/aratio/api/colaboradores.php",
    ),
    (
        "F:/xampp2/htdocs/aratio/api/territorios.php",
        "/home/u577647812/domains/edisongiraldo.com/public_html/aratio/api/territorios.php",
    ),
]

print(f"Connecting to {host}:{port}...")
ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
ssh.connect(host, port=port, username=user, password=password)
print("Connected!")

sftp = ssh.open_sftp()

for local, remote in files:
    try:
        print(f"Uploading {os.path.basename(local)}...")
        sftp.put(local, remote)
        print(f"  OK: {remote}")
    except Exception as e:
        print(f"  ERROR: {e}")

sftp.close()
ssh.close()
print("\nDone!")
