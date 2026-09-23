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

os.makedirs(tmp_base, exist_ok=True)

ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
ssh.connect(host, port=port, username=user, password=password)

stdin, stdout, stderr = ssh.exec_command(f"find {remote_base} -type f -mtime -3")
files = stdout.read().decode().strip().split('\n')

diff_count = 0
for f in files:
    if not f: continue
    rel_path = f.replace(remote_base + "/", "")
    local_path = os.path.join(tmp_base, rel_path)
    os.makedirs(os.path.dirname(local_path), exist_ok=True)
    
    # Use ssh exec_command cat to avoid paramiko sftp size mismatch
    stdin_cat, stdout_cat, stderr_cat = ssh.exec_command(f"cat '{f}'")
    content = stdout_cat.read()
    
    with open(local_path, "wb") as f_out:
        f_out.write(content)
        
    real_local_path = os.path.join(local_base, rel_path)
    if os.path.exists(real_local_path):
        if not filecmp.cmp(local_path, real_local_path, shallow=False):
            print(f"DIFFERENCE FOUND: {rel_path}")
            diff_count += 1
    else:
        print(f"NEW FILE IN REMOTE: {rel_path}")
        diff_count += 1

print(f"\nTotal differences found: {diff_count}")
ssh.close()
