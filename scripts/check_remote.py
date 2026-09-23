#!/usr/bin/env python3
import paramiko

host = "157.173.208.254"
port = 65002
user = "u577647812"
password = 'EDG$v6xSHUWhjrxE'

ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
ssh.connect(host, port=port, username=user, password=password)

stdin, stdout, stderr = ssh.exec_command("ls -la /home/u577647812/domains/")
print("Domains:", stdout.read().decode())

stdin, stdout, stderr = ssh.exec_command(
    "ls -la /home/u577647812/domains/edisongiraldo.com/public_html/"
)
print("public_html:", stdout.read().decode())

ssh.close()
