import paramiko

host = "157.173.208.254"
port = 65002
user = "u577647812"
password = 'E=j$`01yHi^?XfpoM@|CD"5H4'

remote_base_aratio = "/home/u577647812/domains/edisongiraldo.com/public_html/aratio"

files_to_upload = [
    ("h:/Mi unidad/2026/Cali/Edisongiraldo.com/Aratio-App-Mirror/pages/usuarios.php", "pages/usuarios.php"),
]

ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())

try:
    ssh.connect(host, port, user, password)
    sftp = ssh.open_sftp()
    
    print("Uploading to ARATIO directory...")
    for local_path, relative_path in files_to_upload:
        remote_path = f"{remote_base_aratio}/{relative_path}"
        try:
            sftp.put(local_path, remote_path)
            print(f"Uploaded {relative_path} to {remote_path}")
        except Exception as e:
            print(f"Failed to upload to {remote_path}: {e}")
            
    sftp.close()
    print("Upload complete!")

finally:
    ssh.close()
