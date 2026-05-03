import ftplib
import os

FTP_HOST = "212.1.208.241"
FTP_USER = "u156469157.aratio.mrmtech.net"
FTP_PASS = "sthLX6bJPoGh"

LOCAL_BASE = r"H:\Mi unidad\2025\5d\app\Multi-Campaign Management System"

FILES_TO_DEPLOY = [
    # Root
    ("index.php", "index.php"),
    
    # API
    (r"api\organizaciones.php", "api/organizaciones.php"),
    (r"api\colaboradores.php", "api/colaboradores.php"),
    
    # Pages
    (r"pages\organizaciones.php", "pages/organizaciones.php"),
    (r"pages\dashboard.php", "pages/dashboard.php"),
    (r"pages\colaboradores.php", "pages/colaboradores.php"),
    
    # Mod Organizaciones
    (r"mod_organizaciones\index.php", "mod_organizaciones/index.php"),
    (r"mod_organizaciones\dashboard.php", "mod_organizaciones/dashboard.php"),
    (r"mod_organizaciones\grupos_de_interes.php", "mod_organizaciones/grupos_de_interes.php"),
    
    # Mod Día D
    (r"mod_diaD\index.php", "mod_diaD/index.php"),
    (r"mod_diaD\dashboard.php", "mod_diaD/dashboard.php"),
    
    # Mod Colab (Constants/Config)
    (r"mod_colab\config\constants.php", "mod_colab/config/constants.php"),
    (r"config\config.php", "config/config.php"),
]

# Add all files from includes/
INCLUDES_PATH = os.path.join(LOCAL_BASE, "includes")
for f in os.listdir(INCLUDES_PATH):
    if f.endswith(".php"):
        FILES_TO_DEPLOY.append((os.path.join("includes", f), f"includes/{f}"))

def ensure_remote_dir(ftp, remote_path):
    parts = remote_path.split("/")
    if len(parts) <= 1:
        return
    
    orig_dir = ftp.pwd()
    ftp.cwd("/") # Start from root
    
    for part in parts[:-1]:
        try:
            ftp.mkd(part)
            # print(f"Created directory: {part}")
        except:
            pass # Directory likely exists
        try:
            ftp.cwd(part)
        except:
            print(f"Failed to enter directory: {part}")
            break
            
    ftp.cwd(orig_dir)

def deploy():
    try:
        ftp = ftplib.FTP(FTP_HOST)
        ftp.login(FTP_USER, FTP_PASS)
        print(f"Connected to {FTP_HOST}")
        
        # 1. CLEANUP (Optional)
        # legacy_files = ["api/jac.php", "pages/jac.php"]
        # for f in legacy_files:
        #     try: ftp.delete(f); print(f"Deleted legacy file: {f}")
        #     except: pass

        # 2. UPLOAD NEW FILES
        for local_rel, remote_path in FILES_TO_DEPLOY:
            local_full = os.path.join(LOCAL_BASE, local_rel)
            
            if not os.path.exists(local_full):
                print(f"SKIPPING: Local file not found: {local_full}")
                continue
                
            ensure_remote_dir(ftp, remote_path)
            
            with open(local_full, "rb") as f:
                ftp.storbinary(f"STOR {remote_path}", f)
                print(f"DEPLOYED: {remote_path}")
                
        ftp.quit()
        print("Deployment finished successfully.")
        
    except Exception as e:
        print(f"ERROR: {e}")

if __name__ == "__main__":
    deploy()
