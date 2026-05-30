import paramiko

HOST = "157.173.208.254"
PORT = 65002
USER = "u577647812"
PASS = 'EDG$v6xSHUWhjrxE'
REMOTE_DIR = "/home/u577647812/domains/edisongiraldo.com/public_html/aratio"
LOCAL_DIR  = r"F:\xampp2\htdocs\aratio"

FILES = [
    "api/instagram_sync.php",
    "pages/actividad_instagram.php",
    "index.php",
    "pages/dashboard.php",
]

ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
ssh.connect(HOST, PORT, USER, PASS)
sftp = ssh.open_sftp()

for f in FILES:
    local  = LOCAL_DIR  + "\\" + f.replace("/", "\\")
    remote = REMOTE_DIR + "/" + f
    print(f"Subiendo: {f} ...", end=" ")
    sftp.put(local, remote)
    print("OK")

sftp.close()
ssh.close()
print("\nListo.")
