import paramiko
import os

host = "157.173.208.254"
port = 65002
user = "u577647812"
password = 'E=j$`01yHi^?XfpoM@|CD"5H4'

def find_changes():
    try:
        ssh = paramiko.SSHClient()
        ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
        ssh.connect(host, port=port, username=user, password=password)
        
        # Find files modified in the last 2 days, excluding .git and other hidden dirs
        # We also check for directories modified
        cmd = 'find /home/u577647812/domains/edisongiraldo.com/public_html/ -mtime -2 -not -path "*/.*"'
        stdin, stdout, stderr = ssh.exec_command(cmd)
        
        output = stdout.read().decode()
        error = stderr.read().decode()
        
        if error:
            print("Error:", error)
        
        print("Recently modified files (last 2 days):")
        print(output)
        
        # Also check git status if it exists
        stdin, stdout, stderr = ssh.exec_command("cd /home/u577647812/domains/edisongiraldo.com/public_html/ && git status")
        git_status = stdout.read().decode()
        if git_status:
            print("\nGit Status on Remote:")
            print(git_status)
            
        ssh.close()
    except Exception as e:
        print("Failed to connect or execute command:", str(e))

if __name__ == "__main__":
    find_changes()
