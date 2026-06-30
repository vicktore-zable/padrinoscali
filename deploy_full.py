import os
import paramiko


# CONFIGURACIÓN PRODUCTION (Hostinger)
# =============================================
HOST = "157.173.208.254"
PORT = 65002
USER = "u577647812"
PASS = 'EDG$v6xSHUWhjrxE'
REMOTE_DIR = "/home/u577647812/domains/padrinoscali.org/public_html/aratio"
LOCAL_DIR  = r"F:\xampp2\htdocs\aratio"

EXCLUDE_DIRS = [".git", "node_modules", "uploads", "cache", "logs", ".claude"]
EXCLUDE_FILES = [".gitignore", "config_backup_prod.php", "sync_to_prod.ps1", "deploy_full.py"]

def create_ssh_client(server, port, user, password):
    client = paramiko.SSHClient()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    client.connect(server, port, user, password)
    return client

def deploy():
    print(f"--- Iniciando Despliegue AratioPRO v2.5.0 (PadrinosCali.org) ---")
    print(f"Destino: {USER}@{HOST}:{PORT}")
    
    try:
        ssh = create_ssh_client(HOST, PORT, USER, PASS)
        sftp = ssh.open_sftp()
        
        # Función para crear directorios recursivamente en remoto
        def mkdir_p(remote_directory):
            dirs_ = []
            dir_ = remote_directory
            while len(dir_) > 1:
                dirs_.append(dir_)
                dir_, _ = os.path.split(dir_)
            
            if len(dir_) == 1 and dir_.startswith("/"):
                dirs_.append(dir_)
                
            while len(dirs_) > 0:
                dir_ = dirs_.pop()
                try:
                    sftp.stat(dir_)
                except:
                    print(f"Creando directorio remoto: {dir_}")
                    sftp.mkdir(dir_)

        count = 0
        for root, dirs, files in os.walk(LOCAL_DIR):
            # Filtrar directorios excluidos
            dirs[:] = [d for d in dirs if d not in EXCLUDE_DIRS]
            
            rel_path = os.path.relpath(root, LOCAL_DIR)
            remote_path = os.path.join(REMOTE_DIR, rel_path).replace("\\", "/")
            
            if rel_path != ".":
                mkdir_p(remote_path)
            
            for file in files:
                if file in EXCLUDE_FILES: continue
                
                local_file = os.path.join(root, file)
                remote_file = os.path.join(remote_path, file).replace("\\", "/")
                
                # print(f"Subiendo: {rel_path}/{file}")
                sftp.put(local_file, remote_file)
                count += 1
                if count % 50 == 0:
                    print(f"Progreso: {count} archivos subidos...")

        sftp.close()
        ssh.close()
        print(f"\n--- Despliegue Finalizado con Éxito ---")
        print(f"Total archivos subidos: {count}")
        
    except Exception as e:
        print(f"ERROR DURANTE EL DESPLIEGUE: {e}")

if __name__ == "__main__":
    deploy()
