import paramiko

HOST = "157.173.208.254"
PORT = 65002
USER = "u577647812"
PASS = 'EDG$v6xSHUWhjrxE'
REMOTE_DIR = "/home/u577647812/domains/padrinoscali.org/public_html/aratio"
LOCAL_DIR  = r"F:\xampp2\htdocs\aratio"

FILES = [
    # v2.5.0 — Perfil con Curriculum + Mejoras Visuales
    "api/leader-network.php",
    "pages/portal_perfil.php",
    "pages/portal_eventos.php",
    "mod_lider/src/Views/portal/profile.php",
    "mod_lider/src/Views/portal/events.php",
    "mod_lider/src/Views/portal/network.php",
    "mod_lider/src/Views/portal/dashboard.php",
    "mod_lider/src/Controllers/LeaderPortalController.php",
    "mod_lider/src/Views/layouts/portal.php",
    "mod_lider/src/Models/Curriculum.php",
    "index.php",
    "root_config.php",
    "CHANGELOG.md",
]

ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
ssh.connect(HOST, PORT, USER, PASS)
sftp = ssh.open_sftp()

ok = 0
fail = 0
for f in FILES:
    local  = LOCAL_DIR  + "\\" + f.replace("/", "\\")
    remote = REMOTE_DIR + "/" + f
    print(f"Subiendo: {f} ...", end=" ")
    try:
        sftp.put(local, remote)
        print("OK")
        ok += 1
    except Exception as e:
        print(f"FAIL: {e}")
        fail += 1

sftp.close()
ssh.close()
print(f"\nListo. {ok} OK, {fail} fallidos.")
