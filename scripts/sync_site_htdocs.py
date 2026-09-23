# -*- coding: utf-8 -*-
import paramiko, sys, io, os, stat
sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8', errors='replace')

HOST="157.173.208.254"; PORT=65002; USER="u577647812"
PASSWORD='E=j$`01yHi^?XfpoM@|CD"5H4'
REMOTE_PATH="/home/u577647812/domains/edisongiraldo.com/public_html/aratio"
LOCAL_PATH=r"F:\xampp2\htdocs\aratio"

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(HOST, port=PORT, username=USER, password=PASSWORD, timeout=20)

sftp = client.open_sftp()

def sync_dir(remote_dir, local_dir):
    if not os.path.exists(local_dir):
        os.makedirs(local_dir)
    
    try:
        items = sftp.listdir_attr(remote_dir)
    except Exception as e:
        print(f"Skipping {remote_dir} (Error: {e})")
        return

    for item in items:
        r_path = remote_dir + "/" + item.filename
        l_path = os.path.join(local_dir, item.filename)
        
        if stat.S_ISDIR(item.st_mode):
            if item.filename in ['.git', 'node_modules', 'cache', 'logs']:
                continue
            sync_dir(r_path, l_path)
        else:
            if item.filename.endswith(('.php', '.css', '.js', '.html', '.htaccess', '.png', '.jpg', '.svg')):
                print(f"Syncing {item.filename}...")
                try:
                    sftp.get(r_path, l_path)
                except Exception as e:
                    print(f"Failed {item.filename}: {e}")

sync_dir(REMOTE_PATH, LOCAL_PATH)
sftp.close()
client.close()
print("Sync finished.")
