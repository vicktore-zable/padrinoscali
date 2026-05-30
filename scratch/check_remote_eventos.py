# -*- coding: utf-8 -*-
import paramiko

HOST = "157.173.208.254"
PORT = 65002
USER = "u577647812"
PASS = 'E=j$`01yHi^?XfpoM@|CD"5H4'
REMOTE_PATH = "/home/u577647812/domains/edisongiraldo.com/public_html/aratio/pages/eventos.php"

def check_remote():
    client = paramiko.SSHClient()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    client.connect(HOST, port=PORT, username=USER, password=PASS)
    sftp = client.open_sftp()
    
    with sftp.open(REMOTE_PATH, 'r') as f:
        content = f.read().decode('utf-8')
    
    print(f"Longitud remota: {len(content)}")
    print("--- ULTIMAS 10 LINEAS ---")
    lines = content.splitlines()
    for line in lines[-10:]:
        print(line)
        
    sftp.close()
    client.close()

if __name__ == "__main__":
    check_remote()
