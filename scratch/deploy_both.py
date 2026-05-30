import paramiko

host = "157.173.208.254"
port = 65002
user = "u577647812"
password = 'E=j$`01yHi^?XfpoM@|CD"5H4'

remote_base_root = "/home/u577647812/domains/edisongiraldo.com/public_html"
remote_base_aratio = "/home/u577647812/domains/edisongiraldo.com/public_html/aratio"

files_to_upload = [
    ("h:/Mi unidad/2026/Cali/Edisongiraldo.com/Aratio-App-Mirror/api/colaboradores.php", "api/colaboradores.php"),
    ("h:/Mi unidad/2026/Cali/Edisongiraldo.com/Aratio-App-Mirror/pages/colaboradores.php", "pages/colaboradores.php"),
    ("h:/Mi unidad/2026/Cali/Edisongiraldo.com/Aratio-App-Mirror/pages/colaborador_detalle.php", "pages/colaborador_detalle.php"),
    ("h:/Mi unidad/2026/Cali/Edisongiraldo.com/Aratio-App-Mirror/registro-lider.php", "registro-lider.php"),
]

ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())

try:
    ssh.connect(host, port, user, password)
    sftp = ssh.open_sftp()
    
    # Upload to aratio directory
    print("Uploading to ARATIO directory...")
    for local_path, relative_path in files_to_upload:
        remote_path = f"{remote_base_aratio}/{relative_path}"
        try:
            sftp.put(local_path, remote_path)
            print(f"Uploaded {relative_path} to {remote_path}")
        except Exception as e:
            print(f"Failed to upload to {remote_path}: {e}")
            
    # Upload to root directory
    print("Uploading to ROOT directory...")
    for local_path, relative_path in files_to_upload:
        remote_path = f"{remote_base_root}/{relative_path}"
        try:
            sftp.put(local_path, remote_path)
            print(f"Uploaded {relative_path} to {remote_path}")
        except Exception as e:
            print(f"Failed to upload to {remote_path}: {e}")
            
    sftp.close()
    print("Upload complete to both directories!")

finally:
    ssh.close()
