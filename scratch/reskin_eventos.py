# -*- coding: utf-8 -*-
import paramiko, sys, io, re

# Configuración SSH
HOST = "157.173.208.254"
PORT = 65002
USER = "u577647812"
PASS = 'E=j$`01yHi^?XfpoM@|CD"5H4'
ARATIO = "/home/u577647812/domains/edisongiraldo.com/public_html/aratio"

def apply_gold_reskin():
    print(f"--- Aplicando Rediseño Aratio Premium GOLD ---")
    
    try:
        client = paramiko.SSHClient()
        client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
        client.connect(HOST, port=PORT, username=USER, password=PASS, timeout=20)
        sftp = client.open_sftp()
        
        def patch_file(path, replacements):
            try:
                with sftp.open(path, 'r') as f:
                    content = f.read().decode('utf-8')
                
                original = content
                for old, new in replacements:
                    content = content.replace(old, new)
                
                if content != original:
                    with sftp.open(path, 'w') as f:
                        f.write(content)
                    print(f"[GOLD] {path}")
                else:
                    print(f"[OK] {path}")
            except Exception as e:
                print(f"[ERROR] {path}: {e}")

        # 1. ACTUALIZAR LAYOUT GLOBAL (index.php)
        index_replacements = [
            # Cambiar redondeado global a 3xl
            (".rounded-xl", ".rounded-3xl"),
            # Sidebar Premium (Navy Profundo)
            ('class="sidebar fixed top-0 left-0 h-full bg-white border-r border-gray-200 z-50 pt-20"', 
             'class="sidebar fixed top-0 left-0 h-full bg-[#0f172a] border-r border-white/5 z-50 pt-20 shadow-2xl"'),
            # Texto del sidebar
            ('text-gray-600 hover:bg-gray-50', 'text-gray-400 hover:bg-white/5 hover:text-white transition-all'),
            ('text-gray-500 mb-3 px-4', 'text-gray-500/80 mb-3 px-4 text-[10px] tracking-widest uppercase font-bold'),
            # Estilo de botones 3D
            ('.btn-primary {', '.btn-primary { border-bottom: 4px solid rgba(0,0,0,0.3) !important; text-transform: uppercase; letter-spacing: 0.1em; font-weight: 900;'),
            # Navegación Activa Gold
            ('bg-primary/10 text-primary', 'bg-gradient-to-r from-primary/30 to-secondary/10 text-white border-l-4 border-secondary font-bold'),
            # Logo Gradient
            ('gradient-bg rounded-lg', 'bg-gradient-to-br from-primary via-primary to-secondary rounded-2xl shadow-lg shadow-primary/20')
        ]
        patch_file(f"{ARATIO}/index.php", index_replacements)

        # 2. ACTUALIZAR MÓDULO DE EVENTOS (pages/eventos.php)
        eventos_replacements = [
            # Mapa Oscuro
            ("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", "https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png"),
            # Bordes Ultra Redondos
            ("border-radius: 8px;", "border-radius: 32px; border: 4px solid white; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1);"),
            # KPI Cards Estilo Referencia
            ("stat-card", "stat-card rounded-3xl border border-gray-100 hover:-translate-y-1 transition-transform duration-300"),
            ("text-gray-900 font-bold", "text-[#0f172a] font-black text-3xl"),
            # Botones de Acción
            ("btn-primary", "btn-primary rounded-2xl shadow-lg hover:shadow-primary/30"),
            # Titulos
            ("text-2xl font-bold", "text-3xl font-black tracking-tight text-[#0f172a]")
        ]
        patch_file(f"{ARATIO}/pages/eventos.php", eventos_replacements)

        sftp.close()
        client.close()
        print("\n--- Rediseño Aratio Premium GOLD Finalizado ---")
        
    except Exception as e:
        print(f"Error crítico: {e}")

if __name__ == "__main__":
    apply_gold_reskin()
