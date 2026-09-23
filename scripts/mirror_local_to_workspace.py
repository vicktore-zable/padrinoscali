import os
import shutil
import datetime

# CONFIGURATION
# =============================================
SOURCE_DIR = r"F:\xampp2\htdocs\aratio"
TARGET_DIR = r"H:\Mi unidad\2026\Cali\Edisongiraldo.com\Aratio-App-Mirror"

# Folders and files to EXCLUDE from mirror
EXCLUDE_LIST = [
    'cache',
    'logs',
    'uploads',
    '.git',
    '.ruff_cache',
    '.claude',
    '.agent',
    '.agents',
    'tmp',
    'backups'
]

def mirror_projects():
    print(f"--- Creating/Updating Aratio Workspace Mirror ---")
    print(f"Source: {SOURCE_DIR}")
    print(f"Target: {TARGET_DIR}")
    
    if not os.path.exists(SOURCE_DIR):
        print("ERROR: Source directory not found.")
        return

    if not os.path.exists(TARGET_DIR):
        print(f"Creating target directory: {TARGET_DIR}")
        os.makedirs(TARGET_DIR)

    files_synced = 0
    folders_created = 0

    for root, dirs, files in os.walk(SOURCE_DIR):
        # Calculate relative path
        rel_path = os.path.relpath(root, SOURCE_DIR)
        
        # Check if path is in excluded list
        is_excluded = False
        parts = rel_path.split(os.sep)
        for part in parts:
            if part in EXCLUDE_LIST:
                is_excluded = True
                break
        
        if is_excluded:
            continue

        # Ensure destination directory exists
        dest_root = os.path.join(TARGET_DIR, rel_path)
        if not os.path.exists(dest_root):
            os.makedirs(dest_root)
            folders_created += 1

        for file in files:
            source_file = os.path.join(root, file)
            rel_file_path = os.path.join(rel_path, file)
            dest_file = os.path.join(TARGET_DIR, rel_file_path)
            
            # Efficient check: Copy only if different or missing
            copy_needed = False
            if not os.path.exists(dest_file):
                copy_needed = True
            else:
                s_stat = os.stat(source_file)
                d_stat = os.stat(dest_file)
                if s_stat.st_mtime > d_stat.st_mtime or s_stat.st_size != d_stat.st_size:
                    copy_needed = True

            if copy_needed:
                try:
                    shutil.copy2(source_file, dest_file)
                    files_synced += 1
                except Exception as e:
                    print(f"Error copying {rel_file_path}: {e}")

    print(f"\n--- Mirroring Completed ---")
    print(f"New folders created: {folders_created}")
    print(f"Files synchronized: {files_synced}")
    print(f"Timestamp: {datetime.datetime.now().strftime('%Y-%m-%d %H:%M:%S')}")

if __name__ == "__main__":
    mirror_projects()
