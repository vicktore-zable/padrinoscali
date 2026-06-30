import paramiko
import os
import sys

HOST = "157.173.208.254"
PORT = 65002
USER = "u577647812"
PASSWORD = "EDG$v6xSHUWhjrxE"
REMOTE_PATH = "/home/u577647812/domains/padrinoscali.org/public_html"

def main():
    client = paramiko.SSHClient()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    client.connect(HOST, port=PORT, username=USER, password=PASSWORD, timeout=30)
    sftp = client.open_sftp()
    
    try:
        with sftp.file(f"{REMOTE_PATH}/aratio/php-errors.log", "r") as f:
            print("=== php-errors.log ===")
            print(f.read().decode("utf-8")[-2000:])
    except Exception as e:
        print("No php-errors.log found in aratio: ", e)
        
    try:
        with sftp.file(f"{REMOTE_PATH}/php-errors.log", "r") as f:
            print("=== public_html/php-errors.log ===")
            print(f.read().decode("utf-8")[-2000:])
    except Exception as e:
        print("No php-errors.log found in public_html: ", e)
        
    sftp.close()
    client.close()

if __name__ == "__main__":
    main()
