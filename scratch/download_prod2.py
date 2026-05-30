import paramiko
import os

HOST = "157.173.208.254"
PORT = 65002
USER = "u577647812"
PASS = 'EDG$v6xSHUWhjrxE'

files = [
    "mod_colab/src/Views/public/registro_lider.php"
]

try:
    ssh = paramiko.SSHClient()
    ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    ssh.connect(HOST, PORT, USER, PASS)
    sftp = ssh.open_sftp()
    
    for f in files:
        remote = f"/home/u577647812/domains/edisongiraldo.com/public_html/aratio/{f}"
        local = f"h:/Mi unidad/2026/Cali/Edisongiraldo.com/scratch/mod_registro_lider.php"
        print(f"Downloading {remote} to {local}...")
        sftp.get(remote, local)
        
    sftp.close()
    ssh.close()
    print("Download successful.")
except Exception as e:
    print(f"Error: {e}")
