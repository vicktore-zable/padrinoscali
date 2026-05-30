# -*- coding: utf-8 -*-
import paramiko, sys, io, re

# Configuración SSH
HOST = "157.173.208.254"
PORT = 65002
USER = "u577647812"
PASS = 'E=j$`01yHi^?XfpoM@|CD"5H4'
ARATIO = "/home/u577647812/domains/edisongiraldo.com/public_html/aratio"
SUBPATH = "/aratio"

def apply_fixes():
    print(f"--- Iniciando Estabilización del Módulo de Eventos ---")
    
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
                    print(f"[FIXED] {path}")
                else:
                    print(f"[SKIP] {path} - sin cambios necesarios")
            except Exception as e:
                print(f"[ERROR] {path}: {e}")

        # 1. Corregir API de Asistencia (Ruta de Config)
        patch_file(f"{ARATIO}/pages/api/asistencia_eventos.php", [
            ("require_once __DIR__ . '/../config/config.php';", "require_once __DIR__ . '/../../config/config.php';")
        ])
        
        # 2. Corregir Registro Público (Ruta de Config y Fetch)
        patch_file(f"{ARATIO}/pages/registro_asistencia.php", [
            ("require_once 'config/config.php';", "require_once '../config/config.php';"),
            ("fetch('/api/asistencia_eventos.php'", f"fetch('{SUBPATH}/api/asistencia_eventos.php'")
        ])
        
        # 3. Corregir Vista de Eventos (Fetchs y URL de QR)
        patch_file(f"{ARATIO}/pages/eventos.php", [
            ("fetch('/api/", f"fetch('{SUBPATH}/api/"),
            ("fetch(\"/api/", f"fetch(\"{SUBPATH}/api/"),
            ("this.urlRegistro = window.location.origin + '/registro_asistencia.php", f"this.urlRegistro = window.location.origin + '{SUBPATH}/pages/registro_asistencia.php")
        ])

        # 4. Corregir Dashboard de Asistencia (Fetchs)
        patch_file(f"{ARATIO}/pages/dashboard_asistencia.php", [
            ("fetch('/api/", f"fetch('{SUBPATH}/api/"),
            ("fetch(\"/api/", f"fetch(\"{SUBPATH}/api/")
        ])

        # 5. Aplicar Estilos (Azul/Dorado) a botones de eventos si es necesario
        # (Esto es más complejo via replace, pero intentamos con las clases de tailwind si existen)
        
        sftp.close()
        client.close()
        print("\n--- Estabilización Completada ---")
        
    except Exception as e:
        print(f"Error crítico: {e}")

if __name__ == "__main__":
    apply_fixes()
