import paramiko

host = "157.173.208.254"
port = 65002
user = "u577647812"
password = 'EDG$v6xSHUWhjrxE'

ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
ssh.connect(host, port=port, username=user, password=password)

# Find files modified in the last 3 days
stdin, stdout, stderr = ssh.exec_command("find /home/u577647812/domains/edisongiraldo.com/public_html/aratio -type f -mtime -3")
files = stdout.read().decode().strip().split('\n')
for f in files:
    print(f)

ssh.close()
