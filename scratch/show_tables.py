# -*- coding: utf-8 -*-
import paramiko

HOST = "157.173.208.254"
PORT = 65002
USER = "u577647812"
PASS = 'E=j$`01yHi^?XfpoM@|CD"5H4'
DB_USER = "u577647812_aratio"
DB_PASS = "Aratio2026*"
DB_NAME = "u577647812_aratio"

def show_tables():
    client = paramiko.SSHClient()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    client.connect(HOST, port=PORT, username=USER, password=PASS)
    
    query = f"mysql -u {DB_USER} -p'{DB_PASS}' -e 'SHOW TABLES;' {DB_NAME}"
    stdin, stdout, stderr = client.exec_command(query)
    print(stdout.read().decode())
    
    client.close()

if __name__ == "__main__":
    show_tables()
