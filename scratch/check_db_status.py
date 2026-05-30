# -*- coding: utf-8 -*-
import paramiko

HOST = "157.173.208.254"
PORT = 65002
USER = "u577647812"
PASS = 'E=j$`01yHi^?XfpoM@|CD"5H4'
DB_USER = "u577647812_aratio"
DB_PASS = "Aratio2026*"
DB_NAME = "u577647812_aratio"

def check_db():
    client = paramiko.SSHClient()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    client.connect(HOST, port=PORT, username=USER, password=PASS)
    
    query = f"mysql -u {DB_USER} -p'{DB_PASS}' -e 'SELECT id, campana_id, nombre, latitud, longitud FROM eventos;' {DB_NAME}"
    stdin, stdout, stderr = client.exec_command(query)
    print(stdout.read().decode())
    
    query_camp = f"mysql -u {DB_USER} -p'{DB_PASS}' -e 'SELECT id, nombre FROM campanas;' {DB_NAME}"
    stdin, stdout, stderr = client.exec_command(query_camp)
    print(stdout.read().decode())
    
    client.close()

if __name__ == "__main__":
    check_db()
