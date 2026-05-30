import paramiko

HOST = "157.173.208.254"
PORT = 65002
USER = "u577647812"
PASS = "EDG$v6xSHUWhjrxE"

def check_files():
    ssh = paramiko.SSHClient()
    ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    ssh.connect(HOST, port=PORT, username=USER, password=PASS, timeout=15)
    sftp = ssh.open_sftp()
    
    pages_dir = '/home/u577647812/domains/edisongiraldo.com/public_html/aratio/pages'
    storage_dir = '/home/u577647812/domains/edisongiraldo.com/public_html/aratio/storage'
    
    print("Remote verification:")
    try:
        pages = sftp.listdir(pages_dir)
        print(f"  actividad_instagram.php in pages: {'actividad_instagram.php' in pages}")
    except Exception as e:
        print(f"  Error reading pages: {e}")
        
    try:
        storage = sftp.listdir(storage_dir)
        print(f"  instagram_data.json in storage: {'instagram_data.json' in storage}")
    except Exception as e:
        print(f"  Error reading storage: {e}")
        
    sftp.close()
    ssh.close()

if __name__ == "__main__":
    check_files()
