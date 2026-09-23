import paramiko
import os
import filecmp

host = "157.173.208.254"
port = 65002
user = "u577647812"
password = 'EDG$v6xSHUWhjrxE'

remote_base = "/home/u577647812/domains/edisongiraldo.com/public_html/aratio"
local_base = "F:/xampp2/htdocs/aratio"
tmp_base = "tmp_remote"

if not os.path.exists(tmp_base):
    os.makedirs(tmp_base)

ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
ssh.connect(host, port=port, username=user, password=password)

sftp = ssh.open_sftp()

stdin, stdout, stderr = ssh.exec_command(f"find {remote_base} -type f -mtime -3")
files = stdout.read().decode().strip().split('\n')

diff_count = 0
for f in files:
    if not f: continue
    rel_path = f.replace(remote_base + "/", "")
    
    # Download to tmp_base
    local_path = os.path.join(tmp_base, rel_path)
    os.makedirs(os.path.dirname(local_path), exist_ok=True)
    
    # print(f"Downloading {rel_path}...")
    sftp.get(f, local_path)
    
    # Compare with local
    real_local_path = os.path.join(local_base, rel_path)
    if os.path.exists(real_local_path):
        if not filecmp.cmp(local_path, real_local_path, shallow=False):
            print(f"DIFFERENCE FOUND: {rel_path}")
            diff_count += 1
    else:
        print(f"NEW FILE IN REMOTE: {rel_path}")
        diff_count += 1

if diff_count == 0:
    print("No differences found in recently modified remote files.")
else:
    print(f"Total differences found: {diff_count}")

sftp.close()
ssh.close()
