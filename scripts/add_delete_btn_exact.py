# -*- coding: utf-8 -*-
import paramiko, sys, io
sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8', errors='replace')

HOST="157.173.208.254"; PORT=65002; USER="u577647812"
PASSWORD='E=j$`01yHi^?XfpoM@|CD"5H4'
ARATIO="/home/u577647812/domains/edisongiraldo.com/public_html/aratio"

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(HOST, port=PORT, username=USER, password=PASSWORD, timeout=20)

sftp = client.open_sftp()

with sftp.open(f"{ARATIO}/pages/campanas.php", 'r') as f:
    content = f.read().decode('utf-8')

# The actual button uses PHP json_encode to pass the object
# Pattern found: @click="editar(<?= htmlspecialchars(json_encode($c)) ?>)"
old_edit_btn = '''<button @click="editar(<?= htmlspecialchars(json_encode($c)) ?>)" class="btn-ghost px-3 border border-gray-200"><i
                            data-lucide="edit" class="w-4 h-4"></i></button>'''

new_edit_delete_btns = '''<button @click="editar(<?= htmlspecialchars(json_encode($c)) ?>)" class="btn-ghost px-3 border border-gray-200" title="Editar"><i
                            data-lucide="edit" class="w-4 h-4"></i></button>
                    <button @click="eliminar(<?= $c['id'] ?>, '<?= htmlspecialchars($c['nombre'], ENT_QUOTES) ?>')" class="btn-ghost px-3 border border-red-200 text-red-500 hover:bg-red-50" title="Eliminar"><i
                            data-lucide="trash-2" class="w-4 h-4"></i></button>'''

if old_edit_btn in content:
    content = content.replace(old_edit_btn, new_edit_delete_btns)
    print("✅ Boton eliminar agregado correctamente a la tarjeta.")
else:
    print("❌ Patron no coincide. Mostrando contexto del boton editar:")
    idx = content.find('@click="editar(')
    if idx != -1:
        print(repr(content[idx-50:idx+250]))

with sftp.open(f"{ARATIO}/pages/campanas.php", 'w') as f:
    f.write(content)
print("✅ campanas.php guardado.")

sftp.close()
client.close()
