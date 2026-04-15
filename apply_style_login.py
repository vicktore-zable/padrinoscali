# -*- coding: utf-8 -*-
import paramiko, sys, io
sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8', errors='replace')

HOST="157.173.208.254"; PORT=65002; USER="u577647812"
PASSWORD='E=j$`01yHi^?XfpoM@|CD"5H4'
ARATIO="/home/u577647812/domains/edisongiraldo.com/public_html/aratio"

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(HOST, port=PORT, username=USER, password=PASSWORD, timeout=20)

stdin, stdout, stderr = client.exec_command(f"cat {ARATIO}/login.php")
content = stdout.read().decode('utf-8')

# 1. Update Colors
new_content = content.replace("primary: '#1e3a5f'", "primary: '#0d9488'")
new_content = new_content.replace("secondary: '#d4af37'", "secondary: '#2dd4bf'")

# 2. Add Credits in Login Footer
credits_login = """
            <!-- Credits -->
            <div class="mt-8 text-center pt-6 border-t border-gray-100">
                <p class="text-[10px] uppercase tracking-widest text-gray-400 font-medium mb-1">Tecnología por</p>
                <p class="text-xs font-bold text-teal-600">ARATIO</p>
            </div>
"""

# Try to insert before </div> (the card closure)
if "</form>" in new_content:
    new_content = new_content.replace("</form>", "</form>\n" + credits_login)

# Write back
sftp = client.open_sftp()
with sftp.open(f"{ARATIO}/login.php", "w") as f:
    f.write(new_content)
sftp.close()

print("login.php actualizado con nuevos colores y créditos.")
client.close()
