# -*- coding: utf-8 -*-
import paramiko

HOST = "157.173.208.254"
PORT = 65002
USER = "u577647812"
PASS = 'E=j$`01yHi^?XfpoM@|CD"5H4'

def upload_index():
    client = paramiko.SSHClient()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    client.connect(HOST, port=PORT, username=USER, password=PASS)
    sftp = client.open_sftp()
    
    local_path = r"h:\Mi unidad\2026\Cali\Edisongiraldo.com\Aratio-App-Mirror\index.php"
    remote_path = "/home/u577647812/domains/edisongiraldo.com/public_html/aratio/index.php"
    
    print(f"Uploading {local_path} to {remote_path}...")
    sftp.put(local_path, remote_path)
    print("Upload complete.")
    
    sftp.close()
    client.close()

if __name__ == "__main__":
    upload_index()
