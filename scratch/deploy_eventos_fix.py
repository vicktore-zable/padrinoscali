# -*- coding: utf-8 -*-
import paramiko, os

HOST = "157.173.208.254"
PORT = 65002
USER = "u577647812"
PASS = 'E=j$`01yHi^?XfpoM@|CD"5H4'
REMOTE_PATH = "/home/u577647812/domains/edisongiraldo.com/public_html/aratio/pages/eventos.php"
LOCAL_PATH = r"h:\Mi unidad\2026\Cali\Edisongiraldo.com\Aratio-App-Mirror\pages\eventos.php"

def upload_eventos():
    print(f"--- Desplegando eventos.php completo con correcciones ---")
    
    try:
        client = paramiko.SSHClient()
        client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
        client.connect(HOST, port=PORT, username=USER, password=PASS, timeout=20)
        sftp = client.open_sftp()
        
        sftp.put(LOCAL_PATH, REMOTE_PATH)
        print(f"[DEPLOYED] {REMOTE_PATH} actualizado desde local")

        sftp.close()
        client.close()
        
    except Exception as e:
        print(f"Error: {e}")

if __name__ == "__main__":
    upload_eventos()
