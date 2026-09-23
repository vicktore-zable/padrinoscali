# -*- coding: utf-8 -*-
import paramiko, sys, io
sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8', errors='replace')

HOST="157.173.208.254"; PORT=65002; USER="u577647812"
PASSWORD='E=j$`01yHi^?XfpoM@|CD"5H4'
ARATIO="/home/u577647812/domains/edisongiraldo.com/public_html/aratio"

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(HOST, port=PORT, username=USER, password=PASSWORD, timeout=20)

stdin, stdout, stderr = client.exec_command(f"cat {ARATIO}/mod_lider/src/Views/layouts/portal.php")
content = stdout.read().decode('utf-8')

# 1. Update Colors
new_content = content.replace("'aratio-blue': '#002244'", "'aratio-blue': '#0d9488'")
new_content = new_content.replace("'aratio-navy': '#004488'", "'aratio-navy': '#0f766e'")
new_content = new_content.replace("--aratio-blue-dark: #002244;", "--aratio-blue-dark: #0d9488;")
new_content = new_content.replace("--aratio-blue-light: #004488;", "--aratio-blue-light: #0f766e;")

# 2. Add Credits in Portal Sidebar Footer
credits_portal = """
                    <!-- Aratio Credits -->
                    <div class="mt-8 px-6 py-4 border-t border-slate-100">
                        <div class="flex items-center gap-2 opacity-50 hover:opacity-100 transition-opacity">
                            <span class="text-[9px] uppercase tracking-tighter text-slate-400 font-bold">Powered by</span>
                            <span class="text-[11px] font-black text-teal-600">ARATIO</span>
                        </div>
                    </div>
"""

# Try to insert at the end of sidebar nav
if "</nav>" in new_content:
    # Use the first occurrence which is usually the sidebar nav
    new_content = new_content.replace("</nav>", "</nav>\n" + credits_portal, 1)

# Write back
sftp = client.open_sftp()
with sftp.open(f"{ARATIO}/mod_lider/src/Views/layouts/portal.php", "w") as f:
    f.write(new_content)
sftp.close()

print("portal.php actualizado con nuevos colores y créditos.")
client.close()
