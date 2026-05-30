import paramiko
import os

host = "157.173.208.254"
port = 65002
user = "u577647812"
password = 'E=j$`01yHi^?XfpoM@|CD"5H4'

remote_base = "/home/u577647812/domains/edisongiraldo.com/public_html/aratio"

files_to_upload = [
    ("h:/Mi unidad/2026/Cali/Edisongiraldo.com/scratch/add_curriculum_fields.php", f"{remote_base}/api/add_curriculum_fields.php"),
    ("h:/Mi unidad/2026/Cali/Edisongiraldo.com/Aratio-App-Mirror/api/colaboradores.php", f"{remote_base}/api/colaboradores.php"),
    ("h:/Mi unidad/2026/Cali/Edisongiraldo.com/Aratio-App-Mirror/pages/colaboradores.php", f"{remote_base}/pages/colaboradores.php"),
    ("h:/Mi unidad/2026/Cali/Edisongiraldo.com/Aratio-App-Mirror/pages/colaborador_detalle.php", f"{remote_base}/pages/colaborador_detalle.php"),
    ("h:/Mi unidad/2026/Cali/Edisongiraldo.com/Aratio-App-Mirror/registro-lider.php", f"{remote_base}/registro-lider.php"),
]

print("Connecting to server...")
ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())

try:
    ssh.connect(host, port, user, password)
    sftp = ssh.open_sftp()
    
    for local_path, remote_path in files_to_upload:
        print(f"Uploading {local_path} -> {remote_path}")
        sftp.put(local_path, remote_path)
    
    sftp.close()
    print("Upload complete.")

    print("Running database migration script...")
    stdin, stdout, stderr = ssh.exec_command(f"php {remote_base}/api/add_curriculum_fields.php")
    out = stdout.read().decode()
    err = stderr.read().decode()
    print("STDOUT:", out)
    if err:
        print("STDERR:", err)
        
    print("Deployment and Migration finished!")

finally:
    ssh.close()
