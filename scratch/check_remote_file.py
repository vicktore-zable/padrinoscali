# -*- coding: utf-8 -*-
import paramiko

HOST = "157.173.208.254"
PORT = 65002
USER = "u577647812"
PASS = 'E=j$`01yHi^?XfpoM@|CD"5H4'

def check_file():
    client = paramiko.SSHClient()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    client.connect(HOST, port=PORT, username=USER, password=PASS)
    stdin, stdout, stderr = client.exec_command('ls -l /home/u577647812/domains/edisongiraldo.com/public_html/aratio/pages/eventos.php')
    print(stdout.read().decode())
    client.close()

if __name__ == "__main__":
    check_file()
