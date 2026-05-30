# -*- coding: utf-8 -*-
import paramiko, sys, io

# Configuración SSH
HOST = "157.173.208.254"
PORT = 65002
USER = "u577647812"
PASS = 'E=j$`01yHi^?XfpoM@|CD"5H4'
ARATIO = "/home/u577647812/domains/edisongiraldo.com/public_html/aratio"

def run_diagnostic():
    print(f"--- Iniciando Diagnóstico de Módulo de Eventos en Remoto ---")
    
    try:
        client = paramiko.SSHClient()
        client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
        client.connect(HOST, port=PORT, username=USER, password=PASS, timeout=20)
        
        def check_file(path, label):
            print(f"\n[{label}] Revisando: {path}")
            stdin, stdout, stderr = client.exec_command(f"cat {path}")
            content = stdout.read().decode('utf-8', errors='replace')
            err = stderr.read().decode('utf-8', errors='replace')
            
            if err:
                print(f"  [ERROR] No se pudo leer el archivo: {err.strip()}")
                return None
            
            # 1. Buscar rutas de fetch
            fetch_paths = [line.strip() for line in content.split('\n') if 'fetch(' in line]
            if fetch_paths:
                print(f"  - Rutas de fetch encontradas ({len(fetch_paths)}):")
                for path in fetch_paths[:5]:
                    print(f"    {path}")
                
                # Verificar si falta /aratio/
                broken = [p for p in fetch_paths if "fetch('/api/" in p or 'fetch("/api/' in p]
                if broken:
                    print(f"  - [ALERTA] Se encontraron {len(broken)} rutas que probablemente necesitan /aratio/")
            
            # 2. Buscar requires
            requires = [line.strip() for line in content.split('\n') if 'require' in line or 'include' in line]
            if requires:
                print(f"  - Includes/Requires encontrados:")
                for r in requires[:5]:
                    print(f"    {r}")
            
            return content

        # Archivos a revisar
        check_file(f"{ARATIO}/pages/eventos.php", "Vista Eventos")
        check_file(f"{ARATIO}/pages/api/asistencia_eventos.php", "API Asistencia")
        check_file(f"{ARATIO}/pages/registro_asistencia.php", "Registro Público")
        
        client.close()
        
    except Exception as e:
        print(f"Error en la conexión SSH: {e}")

if __name__ == "__main__":
    run_diagnostic()
