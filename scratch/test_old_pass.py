import paramiko

HOST = "157.173.208.254"
PORT = 65002
USER = "u577647812"
PASS_OLD = 'E=j$`01yHi^?XfpoM@|CD"5H4'

ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
try:
    ssh.connect(HOST, port=PORT, username=USER, password=PASS_OLD, timeout=10)
    print("OLD password still works!")
    ssh.close()
except Exception as e:
    print(f"OLD password FAILED: {e}")
