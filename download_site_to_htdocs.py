# -*- coding: utf-8 -*-
import paramiko, sys, io, os
sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8', errors='replace')

HOST="157.173.208.254"; PORT=65002; USER="u577647812"
PASSWORD='E=j$`01yHi^?XfpoM@|CD"5H4'
REMOTE_PATH="/home/u577647812/domains/edisongiraldo.com/public_html/aratio"
LOCAL_PATH=r"F:\xampp2\htdocs\aratio"

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(HOST, port=PORT, username=USER, password=PASSWORD, timeout=20)

sftp = client.open_sftp()

def download_dir(remote_dir, local_dir):
    if not os.path.exists(local_dir):
        os.makedirs(local_dir)
        
    for item in sftp.listdir_attr(remote_dir):
        r_path = os.path.join(remote_dir, item.filename).replace('\\', '/')
        l_path = os.path.join(local_dir, item.filename)
        
        if str(item.st_mode)[0] == '4': # Directory (this is a bit crude but usually works for sftp attributes)
            # Better check for directory
            import stat
            if stat.S_ISDIR(item.st_mode):
                download_dir(r_path, l_path)
            else:
                print(f"Downloading {r_path}...")
                sftp.get(r_path, l_path)
        else:
            print(f"Downloading {r_path}...")
            sftp.get(r_path, l_path)

try:
    download_dir(REMOTE_PATH, LOCAL_PATH)
    print("Download complete.")
except Exception as e:
    print(f"Error: {e}")
finally:
    sftp.close()
    client.close()
