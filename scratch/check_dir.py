import paramiko

ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
ssh.connect('157.173.208.254', 65002, 'u577647812', 'E=j$`01yHi^?XfpoM@|CD"5H4')

stdin, stdout, stderr = ssh.exec_command('ls -la /home/u577647812/domains/edisongiraldo.com/public_html/')
print('ROOT:\n', stdout.read().decode())

stdin, stdout, stderr = ssh.exec_command('ls -la /home/u577647812/domains/edisongiraldo.com/public_html/aratio/')
print('ARATIO:\n', stdout.read().decode())

ssh.close()
