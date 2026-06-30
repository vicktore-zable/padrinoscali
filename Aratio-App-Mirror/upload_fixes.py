import paramiko
import os
import sys

HOST = "157.173.208.254"
PORT = 65002
USER = "u577647812"
PASSWORD = "EDG$v6xSHUWhjrxE"
REMOTE_PATH = "/home/u577647812/domains/padrinoscali.org/public_html/aratio"
LOCAL_PATH = os.getcwd()

FILES_TO_UPLOAD = [
    "pages/colaboradores.php",
    "api/colaboradores.php",
    "api_colaboradores_verified.php",
    "config/config.php",
    "test_api.php"
]

def main():
    print("Conectando al servidor...")
    client = paramiko.SSHClient()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    try:
        client.connect(HOST, port=PORT, username=USER, password=PASSWORD, timeout=30)
    except Exception as e:
        print(f"Error de conexión: {e}")
        sys.exit(1)

    sftp = client.open_sftp()
    
    uploaded = 0
    for f in FILES_TO_UPLOAD:
        local_file = os.path.join(LOCAL_PATH, f)
        remote_file = f"{REMOTE_PATH}/{f}"
        if os.path.exists(local_file):
            print(f"Subiendo {f}...")
            try:
                sftp.put(local_file, remote_file)
                uploaded += 1
                print(f"  OK")
            except Exception as e:
                print(f"  Error: {e}")
        else:
            print(f"Archivo no encontrado localmente: {local_file}")
            
    sftp.close()
    client.close()
    print(f"\nFinalizado. {uploaded}/{len(FILES_TO_UPLOAD)} archivos subidos.")

if __name__ == "__main__":
    main()
