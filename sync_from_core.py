import os
import shutil
import datetime

# CONFIGURACIÓN DE RUTAS
# =============================================
CORE_DIR = r"H:\Mi unidad\2025\5d\app\Multi-Campaign Management System"
TARGET_DIR = r"F:\xampp2\htdocs\aratio"

# Archivos y carpetas que NO deben ser sobrescritos
# =============================================
EXCLUDE_LIST = [
    'config/config.php',
    'root_config.php',
    '.htaccess',
    'uploads',
    'cache',
    'logs',
    '.git',
    '.vscode',
    'node_modules'
]

def sync_projects():
    print(f"--- Iniciando Sincronización AratioPRO v2.1.0 ---")
    print(f"Fuente: {CORE_DIR}")
    print(f"Destino: {TARGET_DIR}")
    
    if not os.path.exists(CORE_DIR):
        print("ERROR: La carpeta fuente no existe.")
        return

    files_synced = 0
    folders_created = 0

    for root, dirs, files in os.walk(CORE_DIR):
        # Calcular ruta relativa
        rel_path = os.path.relpath(root, CORE_DIR)
        
        # Omitir si la ruta está en la lista de excluidos
        skip_folder = False
        if rel_path != '.':
            for exclude in EXCLUDE_LIST:
                if rel_path.startswith(exclude):
                    skip_folder = True
                    break
        
        if skip_folder:
            continue

        # Asegurar que el directorio de destino existe
        dest_root = os.path.join(TARGET_DIR, rel_path)
        if not os.path.exists(dest_root):
            os.makedirs(dest_root)
            folders_created += 1

        for file in files:
            source_file = os.path.join(root, file)
            rel_file_path = os.path.normpath(os.path.join(rel_path, file))
            
            # Omitir archivos específicos
            if rel_file_path in EXCLUDE_LIST:
                continue
            
            dest_file = os.path.join(TARGET_DIR, rel_file_path)
            
            # Copiar archivo
            try:
                shutil.copy2(source_file, dest_file)
                files_synced += 1
            except Exception as e:
                print(f"Error copiando {rel_file_path}: {e}")

    print(f"\n--- Sincronización Finalizada ---")
    print(f"Carpetas creadas: {folders_created}")
    print(f"Archivos sincronizados: {files_synced}")
    print(f"Fecha: {datetime.datetime.now().strftime('%Y-%m-%d %H:%M:%S')}")

if __name__ == "__main__":
    sync_projects()
