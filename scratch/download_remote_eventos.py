# -*- coding: utf-8 -*-
import paramiko

HOST = "157.173.208.254"
PORT = 65002
USER = "u577647812"
PASS = 'E=j$`01yHi^?XfpoM@|CD"5H4'

def download_file():
    client = paramiko.SSHClient()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    client.connect(HOST, port=PORT, username=USER, password=PASS)
    sftp = client.open_sftp()
    
    remote_path = "/home/u577647812/domains/edisongiraldo.com/public_html/aratio/pages/eventos.php"
    local_path = r"h:\Mi unidad\2026\Cali\Edisongiraldo.com\scratch\remote_eventos.php"
    
    print(f"Downloading {remote_path} to {local_path}...")
    sftp.get(remote_path, local_path)
    print("Download complete.")
    
    sftp.close()
    client.close()

if __name__ == "__main__":
    download_file()
