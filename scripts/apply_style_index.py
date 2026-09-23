# -*- coding: utf-8 -*-
import paramiko, sys, io
sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8', errors='replace')

HOST="157.173.208.254"; PORT=65002; USER="u577647812"
PASSWORD='E=j$`01yHi^?XfpoM@|CD"5H4'
ARATIO="/home/u577647812/domains/edisongiraldo.com/public_html/aratio"

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(HOST, port=PORT, username=USER, password=PASSWORD, timeout=20)

stdin, stdout, stderr = client.exec_command(f"cat {ARATIO}/index.php")
content = stdout.read().decode('utf-8')

# 1. Update Colors
new_content = content.replace("primary: '#1e3a5f'", "primary: '#0d9488'")
new_content = new_content.replace("secondary: '#d4af37'", "secondary: '#2dd4bf'")
new_content = new_content.replace("#1e3a5f 0%, #d4af37 100%", "#0d9488 0%, #064e3b 100%")

# 2. Add Credits in Sidebar
# Look for the last <nav> or the mt-auto div
credits_html = """
            <!-- Credits -->
            <div class="mt-8 px-4 py-4 border-t border-gray-100">
                <div class="flex items-center gap-2 opacity-60 hover:opacity-100 transition-opacity">
                    <span class="text-[10px] font-medium tracking-widest uppercase text-gray-400">Desarrollado por</span>
                    <span class="text-xs font-bold text-primary">ARATIO</span>
                </div>
            </div>
"""

if "<!-- Bottom -->" in new_content:
    new_content = new_content.replace("<!-- Bottom -->", credits_html + "\n            <!-- Bottom -->")

# Write back
sftp = client.open_sftp()
with sftp.open(f"{ARATIO}/index.php", "w") as f:
    f.write(new_content)
sftp.close()

print("index.php actualizado con nuevos colores y créditos.")
client.close()
