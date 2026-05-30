import paramiko
import os

host = "157.173.208.254"
port = 65002
user = "u577647812"
password = 'E=j$`01yHi^?XfpoM@|CD"5H4'

remote_base = "/home/u577647812/domains/edisongiraldo.com/public_html/"
local_base = "h:/Mi unidad/2026/Cali/Edisongiraldo.com/scratch/remote_files/"

files_to_download = [
    "aratio/pages/usuarios.php",
    "aratio/pages/colaborador_detalle.php",
    "aratio/pages/colaboradores.php",
    "aratio/api/add_curriculum_fields.php",
    "aratio/api/colaboradores.php",
    "aratio/registro-lider.php",
    "registro-lider.php",
    "aratio/test_jenny.php",
    "aratio/test_queries.php",
    "aratio/reset_jenny.php"
]

def download_files():
    if not os.path.exists(local_base):
        os.makedirs(local_base)
        
    try:
        ssh = paramiko.SSHClient()
        ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
        ssh.connect(host, port=port, username=user, password=password)
        
        sftp = ssh.open_sftp()
        
        for f in files_to_download:
            remote_path = os.path.join(remote_base, f).replace("\\", "/")
            local_path = os.path.join(local_base, f.replace("/", "_"))
            
            try:
                print(f"Downloading {remote_path} to {local_path}...")
                sftp.get(remote_path, local_path)
            except Exception as e:
                print(f"Failed to download {f}: {str(e)}")
                
        sftp.close()
        ssh.close()
    except Exception as e:
        print("Error:", str(e))

if __name__ == "__main__":
    download_files()
