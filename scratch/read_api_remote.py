# -*- coding: utf-8 -*-
import paramiko

HOST = "157.173.208.254"
PORT = 65002
USER = "u577647812"
PASS = 'E=j$`01yHi^?XfpoM@|CD"5H4'

def read_api():
    client = paramiko.SSHClient()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    client.connect(HOST, port=PORT, username=USER, password=PASS)
    sftp = client.open_sftp()
    
    path = "/home/u577647812/domains/edisongiraldo.com/public_html/aratio/api/eventos.php"
    with sftp.open(path, 'r') as f:
        print(f.read(1000).decode())
    client.close()

if __name__ == "__main__":
    read_api()
