import paramiko

HOST = "157.173.208.254"
PORT = 65002
USER = "u577647812"
PASS = "EDG$v6xSHUWhjrxE"

def clean_remote_files():
    ssh = paramiko.SSHClient()
    ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    ssh.connect(HOST, port=PORT, username=USER, password=PASS, timeout=15)
    sftp = ssh.open_sftp()
    
    path = '/home/u577647812/domains/edisongiraldo.com/public_html/aratio/diag_red.php'
    try:
        sftp.stat(path)
        print("diag_red.php exists on remote server. Removing...")
        sftp.remove(path)
        print("diag_red.php removed successfully from remote server.")
    except FileNotFoundError:
        print("diag_red.php does not exist on remote server (already cleaned up).")
    except Exception as e:
        print(f"Error checking/removing remote file: {e}")
        
    sftp.close()
    ssh.close()

if __name__ == "__main__":
    clean_remote_files()
