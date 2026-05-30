# -*- coding: utf-8 -*-
import paramiko

HOST = "157.173.208.254"
PORT = 65002
USER = "u577647812"
PASS = 'E=j$`01yHi^?XfpoM@|CD"5H4'
ARATIO = "/home/u577647812/domains/edisongiraldo.com/public_html/aratio"

def move_app_data():
    print(f"--- Moviendo appData() al head en index.php ---")
    
    try:
        client = paramiko.SSHClient()
        client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
        client.connect(HOST, port=PORT, username=USER, password=PASS, timeout=20)
        sftp = client.open_sftp()
        
        path = f"{ARATIO}/index.php"
        with sftp.open(path, 'r') as f:
            content = f.read().decode('utf-8')
        
        # Extraer la función appData
        script_pattern = """    <script>
        function appData() {
            return {
                sidebarOpen: false,
                
                init() {
                    // Inicializar Lucide icons
                    lucide.createIcons();
                    
                    // Cerrar sidebar al hacer clic en enlaces en móvil
                    document.querySelectorAll('aside a').forEach(link => {
                        link.addEventListener('click', () => {
                            if (window.innerWidth < 1024) {
                                this.sidebarOpen = false;
                            }
                        });
                    });
                }
            }
        }
    </script>"""
        
        if script_pattern in content:
            # Eliminar del final
            content = content.replace(script_pattern, "")
            # Insertar en el head (después de tailwind config)
            content = content.replace(
                "        }\n    </script>",
                "        }\n    </script>\n\n    <script>\n        function appData() {\n            return {\n                sidebarOpen: false,\n                init() {\n                    lucide.createIcons();\n                    document.querySelectorAll('aside a').forEach(link => {\n                        link.addEventListener('click', () => {\n                            if (window.innerWidth < 1024) {\n                                this.sidebarOpen = false;\n                            }\n                        });\n                    });\n                }\n            }\n        }\n    </script>"
            )
            
            with sftp.open(path, 'w') as f:
                f.write(content)
            print(f"[MOVED] appData() movido al head en {path}")
        else:
            print("[SKIP] No se encontró el script original o ya fue movido.")

        sftp.close()
        client.close()
        
    except Exception as e:
        print(f"Error: {e}")

if __name__ == "__main__":
    move_app_data()
