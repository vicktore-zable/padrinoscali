#!/usr/bin/env python3
"""Deploy autocompleto QR + seguridad mod_eventos a Hostinger."""
import paramiko, os, sys

HOST = "157.173.208.254"
PORT = 65002
USER = "u577647812"
PASS = 'E=j$`01yHi^?XfpoM@|CD"5H4'
LOCAL = r"F:\drive2026\2026\Cali\Edisongiraldo.com\Aratio-App-Mirror"

REMOTE_DIRS = [
    "/home/u577647812/domains/padrinoscali.org/public_html/aratio",
    "/home/u577647812/domains/edisongiraldo.com/public_html/aratio",
]

FILES = [
    "api/colaboradores.php",
    "mod_eventos/pages/qr_registro.php",
    "mod_eventos/api/asistencia.php",
    "mod_eventos/api/_security.php",
    "mod_eventos/api/reportes.php",
    "mod_eventos/api/exportar_asistencia.php",
]

def main():
    print(f"Conectando a {HOST}:{PORT} como {USER}...")
    ssh = paramiko.SSHClient()
    ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    try:
        ssh.connect(HOST, port=PORT, username=USER, key_filename=os.path.expanduser("~/.ssh/id_ed25519"), timeout=15)
        print("[OK] Conectado via SSH key")
    except Exception:
        ssh.connect(HOST, port=PORT, username=USER, password=PASS, timeout=30)
        print("[OK] Conectado via password")

    sftp = ssh.open_sftp()
    ok = fail = 0

    for remote_base in REMOTE_DIRS:
        print(f"\n--- Destino: {remote_base} ---")
        for rel_path in FILES:
            local_path = os.path.join(LOCAL, rel_path.replace("/", os.sep))
            remote_path = f"{remote_base}/{rel_path}"

            if not os.path.exists(local_path):
                print(f"  [SKIP] No existe local: {rel_path}")
                fail += 1
                continue

            local_size = os.path.getsize(local_path)
            print(f"  Subiendo: {rel_path} ({local_size:,} bytes) ...", end=" ", flush=True)
            try:
                sftp.put(local_path, remote_path)
                remote_stat = sftp.stat(remote_path)
                print(f"OK ({remote_stat.st_size:,} bytes)")
                ok += 1
            except Exception as e:
                print(f"FAIL: {e}")
                fail += 1

    sftp.close()
    ssh.close()
    print(f"\n[DONE] {ok} OK, {fail} fallidos.")

if __name__ == "__main__":
    main()
