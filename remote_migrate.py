import paramiko

HOST = "157.173.208.254"
PORT = 65002
USER = "u577647812"
PASS = 'E=j$`01yHi^?XfpoM@|CD"5H4'
REMOTE_FILE = "/home/u577647812/domains/edisongiraldo.com/public_html/aratio/scratch/check_user.php"

def run_remote_migration():
    print(f"--- Ejecutando Migración Remota en Hostinger ---")
    try:
        ssh = paramiko.SSHClient()
        ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
        ssh.connect(HOST, PORT, USER, PASS)
        
        cmd = f"php -l /home/u577647812/domains/edisongiraldo.com/public_html/aratio/root_config.php"
        stdin, stdout, stderr = ssh.exec_command(cmd)
        
        out = stdout.read().decode()
        err = stderr.read().decode()
        
        print("SALIDA STDOUT:")
        print(out)
        
        if err:
            print("SALIDA STDERR:")
            print(err)
            
        ssh.close()
        print("--- Migración Remota Finalizada ---")
        
    except Exception as e:
        print(f"Error: {e}")

if __name__ == "__main__":
    run_remote_migration()
