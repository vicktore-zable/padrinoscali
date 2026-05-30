import paramiko
import os
import sys

HOST = "157.173.208.254"
PORT = 65002
USER = "u577647812"
PASS = 'EDG$v6xSHUWhjrxE'

try:
    ssh = paramiko.SSHClient()
    ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    ssh.connect(HOST, PORT, USER, PASS)
    stdin, stdout, stderr = ssh.exec_command("grep -rl 'Padrino' /home/u577647812/domains/edisongiraldo.com/public_html/aratio/")
    data = stdout.read()
    with open("h:/Mi unidad/2026/Cali/Edisongiraldo.com/scratch/grep_output.txt", "wb") as f:
        f.write(data)
    ssh.close()
    print("Done")
except Exception as e:
    print(f"Error: {e}")
