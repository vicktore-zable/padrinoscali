#!/usr/bin/env python3
import paramiko

host = "157.173.208.254"
port = 65002
user = "u577647812"
password = 'E=j$`01yHi^?XfpoM@|CD"5H4'

files = [
    (
        "F:/xampp2/htdocs/aratio/mod_diaD/index.php",
        "/home/u577647812/domains/edisongiraldo.com/public_html/aratio/mod_diaD/index.php",
    ),
    (
        "F:/xampp2/htdocs/aratio/mod_diaD/dashboard.php",
        "/home/u577647812/domains/edisongiraldo.com/public_html/aratio/mod_diaD/dashboard.php",
    ),
    (
        "F:/xampp2/htdocs/aratio/mod_jac/index.php",
        "/home/u577647812/domains/edisongiraldo.com/public_html/aratio/mod_jac/index.php",
    ),
    (
        "F:/xampp2/htdocs/aratio/mod_jac/dashboard.php",
        "/home/u577647812/domains/edisongiraldo.com/public_html/aratio/mod_jac/dashboard.php",
    ),
    (
        "F:/xampp2/htdocs/aratio/pages/dashboard.php",
        "/home/u577647812/domains/edisongiraldo.com/public_html/aratio/pages/dashboard.php",
    ),
]

ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
ssh.connect(host, port=port, username=user, password=password)
sftp = ssh.open_sftp()

for local, remote in files:
    import os

    name = os.path.basename(local)
    print(f"Uploading {name}...")
    sftp.put(local, remote)
    print(f"  OK")

sftp.close()
ssh.close()
print("Done!")
