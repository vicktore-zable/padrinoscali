import paramiko

HOST = "157.173.208.254"
PORT = 65002
USER = "u577647812"
PASS = 'EDG$v6xSHUWhjrxE'

files = [
    ("h:/Mi unidad/2026/Cali/Edisongiraldo.com/scratch/test_db.php", "/home/u577647812/domains/edisongiraldo.com/public_html/aratio/test_db.php")
]

try:
    ssh = paramiko.SSHClient()
    ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    ssh.connect(HOST, PORT, USER, PASS)
    sftp = ssh.open_sftp()
    
    for local, remote in files:
        print(f"Uploading {local} to {remote}...")
        sftp.put(local, remote)
        
    sftp.close()
    ssh.close()
    print("Upload successful.")
except Exception as e:
    print(f"Error: {e}")
