# -*- coding: utf-8 -*-
import paramiko

HOST = "157.173.208.254"
PORT = 65002
USER = "u577647812"
PASS = 'E=j$`01yHi^?XfpoM@|CD"5H4'
ARATIO = "/home/u577647812/domains/edisongiraldo.com/public_html/aratio"

def fix_syntax():
    print(f"--- Corrigiendo Syntax Error en eventos.php ---")
    
    try:
        client = paramiko.SSHClient()
        client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
        client.connect(HOST, port=PORT, username=USER, password=PASS, timeout=20)
        sftp = client.open_sftp()
        
        path = f"{ARATIO}/pages/eventos.php"
        with sftp.open(path, 'r') as f:
            content = f.read().decode('utf-8')
        
        # El error está en el bloque editar(id)
        # Buscamos la secuencia específica que identificamos
        bad_code = """                this.form.barrio = evento.barrio || '';
            }
                        this.modalNuevo = true;
                    } else {
                        alert('Error al cargar el evento');
                    }
                } catch (error) {"""
        
        good_code = """                this.form.barrio = evento.barrio || '';
                
                this.modalNuevo = true;
            } else {
                alert('Error al cargar el evento');
            }
        } catch (error) {"""
        
        if bad_code in content:
            content = content.replace(bad_code, good_code)
            with sftp.open(path, 'w') as f:
                f.write(content)
            print(f"[FIXED] Syntax error corregido en {path}")
        else:
            # Intento con una búsqueda más flexible por si los espacios varían
            print("[WARN] No se encontró el bloque exacto, intentando búsqueda flexible...")
            pattern = r"this\.form\.barrio = evento\.barrio \|\| '';\s*}\s*this\.modalNuevo = true;\s*}\s*else\s*{"
            if re.search(pattern, content):
                content = re.sub(pattern, "this.form.barrio = evento.barrio || '';\n                this.modalNuevo = true;\n            } else {", content)
                with sftp.open(path, 'w') as f:
                    f.write(content)
                print(f"[FIXED] Syntax error corregido (flexible) en {path}")
            else:
                print("[ERROR] No se pudo localizar el error de sintaxis en el servidor.")

        sftp.close()
        client.close()
        
    except Exception as e:
        print(f"Error: {e}")

if __name__ == "__main__":
    import re
    fix_syntax()
