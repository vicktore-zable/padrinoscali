import paramiko
import os

HOST = "157.173.208.254"
PORT = 65002
USER = "u577647812"
PASS = 'EDG$v6xSHUWhjrxE'

import sys

def upload_file(local_path, remote_path):
    print(f"Subiendo {local_path} a {remote_path}...")
    try:
        ssh = paramiko.SSHClient()
        ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
        ssh.connect(HOST, PORT, USER, PASS)
        sftp = ssh.open_sftp()
        sftp.put(local_path, remote_path)
        sftp.close()
        ssh.close()
        print("Subida exitosa.")
    except Exception as e:
        print(f"Error: {e}")

if __name__ == "__main__":
    if len(sys.argv) > 1:
        local = sys.argv[1]
        remote = sys.argv[2] if len(sys.argv) > 2 else "/home/u577647812/domains/edisongiraldo.com/public_html/aratio/" + os.path.basename(local)
        upload_file(local, remote)
    else:
        # Default for the check script
        upload_file(r"F:\xampp2\htdocs\aratio\scratch\check_user.php", "/home/u577647812/domains/edisongiraldo.com/public_html/aratio/scratch/check_user.php")
