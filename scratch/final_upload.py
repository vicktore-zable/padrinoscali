# -*- coding: utf-8 -*-
import paramiko

HOST = "157.173.208.254"
PORT = 65002
USER = "u577647812"
PASS = 'E=j$`01yHi^?XfpoM@|CD"5H4'

def final_upload():
    client = paramiko.SSHClient()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    client.connect(HOST, port=PORT, username=USER, password=PASS)
    sftp = client.open_sftp()
    
    files = [
        (r"h:\Mi unidad\2026\Cali\Edisongiraldo.com\Aratio-App-Mirror\pages\eventos.php", "/home/u577647812/domains/edisongiraldo.com/public_html/aratio/pages/eventos.php"),
        (r"h:\Mi unidad\2026\Cali\Edisongiraldo.com\Aratio-App-Mirror\index.php", "/home/u577647812/domains/edisongiraldo.com/public_html/aratio/index.php")
    ]
    
    for local, remote in files:
        print(f"Uploading {local} to {remote}...")
        sftp.put(local, remote)
    
    print("All uploads complete.")
    sftp.close()
    client.close()

if __name__ == "__main__":
    final_upload()
