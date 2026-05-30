#!/usr/bin/env python3
import paramiko

host = "157.173.208.254"
port = 65002
user = "u577647812"
password = 'E=j$`01yHi^?XfpoM@|CD"5H4'

ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
ssh.connect(host, port=port, username=user, password=password)

stdin, stdout, stderr = ssh.exec_command(
    "ls -la /home/u577647812/domains/edisongiraldo.com/public_html/aratio/mod_jac/"
)
print("mod_jac:", stdout.read().decode())

stdin, stdout, stderr = ssh.exec_command(
    "ls -la /home/u577647812/domains/edisongiraldo.com/public_html/aratio/mod_diaD/"
)
print("mod_diaD:", stdout.read().decode())

ssh.close()
