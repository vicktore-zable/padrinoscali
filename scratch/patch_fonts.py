# -*- coding: utf-8 -*-
import paramiko, sys, io, re

# Configuración SSH
HOST = "157.173.208.254"
PORT = 65002
USER = "u577647812"
PASS = 'E=j$`01yHi^?XfpoM@|CD"5H4'
ARATIO = "/home/u577647812/domains/edisongiraldo.com/public_html/aratio"

def patch_fonts():
    print(f"--- Aplicando Tipografía Premium (Outfit) ---")
    
    try:
        client = paramiko.SSHClient()
        client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
        client.connect(HOST, port=PORT, username=USER, password=PASS, timeout=20)
        sftp = client.open_sftp()
        
        path = f"{ARATIO}/index.php"
        with sftp.open(path, 'r') as f:
            content = f.read().decode('utf-8')
        
        if 'fonts.googleapis.com' not in content:
            # Insertar fuente
            content = content.replace(
                '<title>Aratio - Sistema de Gestión Electoral</title>',
                '<title>Aratio - Sistema de Gestión Electoral</title>\n\n    <!-- Google Fonts: Outfit -->\n    <link rel="preconnect" href="https://fonts.googleapis.com">\n    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>\n    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@100..900&display=swap" rel="stylesheet">'
            )
            # Insertar config de tailwind
            content = content.replace(
                "accent: '<?= COLOR_ACCENT ?>',",
                "accent: '<?= COLOR_ACCENT ?>',\n                    },\n                    fontFamily: {\n                        sans: ['Outfit', 'sans-serif'],"
            )
            
            with sftp.open(path, 'w') as f:
                f.write(content)
            print(f"[FONTS] {path} actualizado")
        else:
            print(f"[FONTS] {path} ya tiene la tipografía")

        sftp.close()
        client.close()
        
    except Exception as e:
        print(f"Error: {e}")

if __name__ == "__main__":
    patch_fonts()
