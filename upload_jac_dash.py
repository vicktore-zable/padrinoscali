#!/usr/bin/env python3
import paramiko

host = "157.173.208.254"
port = 65002
user = "u577647812"
password = 'E=j$`01yHi^?XfpoM@|CD"5H4'

local = "F:/xampp2/htdocs/aratio/mod_jac/dashboard.php"
remote = "/home/u577647812/domains/edisongiraldo.com/public_html/aratio/mod_jac/dashboard.php"

ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
ssh.connect(host, port=port, username=user, password=password)
sftp = ssh.open_sftp()

print("Uploading mod_jac/dashboard.php...")
sftp.put(local, remote)
print("Done!")

sftp.close()
ssh.close()
