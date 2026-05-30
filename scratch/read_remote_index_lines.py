# -*- coding: utf-8 -*-
import paramiko

HOST = "157.173.208.254"
PORT = 65002
USER = "u577647812"
PASS = 'E=j$`01yHi^?XfpoM@|CD"5H4'

def get_lines(path):
    client = paramiko.SSHClient()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    client.connect(HOST, port=PORT, username=USER, password=PASS)
    stdin, stdout, stderr = client.exec_command(f'sed -n "100,150p" {path}')
    print(stdout.read().decode())
    client.close()

if __name__ == "__main__":
    get_lines("/home/u577647812/domains/edisongiraldo.com/public_html/aratio/index.php")
