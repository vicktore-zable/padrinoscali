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

# -----------------------------------------------------------------------
# 1. ADD the delete button to the card HTML
#    Current card footer has: Seleccionar | Colabs | edit-icon button
#    We add a trash icon button after the edit button
# -----------------------------------------------------------------------
old_card_footer = '''                    <button type="button" @click="editar(campana)" class="p-2 rounded hover:bg-gray-100" title="Editar campaña">
                            <i data-lucide="edit" class="w-4 h-4"></i>
                        </button>'''

new_card_footer = '''                    <button type="button" @click="editar(campana)" class="p-2 rounded hover:bg-gray-100" title="Editar campaña">
                            <i data-lucide="edit" class="w-4 h-4"></i>
                        </button>
                        <button type="button" @click="eliminar(campana.id, campana.nombre)" class="p-2 rounded hover:bg-red-100 text-red-500" title="Eliminar campaña">
                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                        </button>'''

if old_card_footer in content:
    content = content.replace(old_card_footer, new_card_footer)
    print("✅ Boton eliminar agregado a la tarjeta.")
else:
    # Try a broader search to understand what's in the card footer
    import re
    editar_match = re.search(r'@click="editar\(campana\)".*?</button>', content, re.DOTALL)
    if editar_match:
        print("Encontrado pero diferente formato. Contexto:")
        print(repr(editar_match.group(0)))
    else:
        print("❌ No se encontro el boton editar en la tarjeta. Buscando alternativas...")
        idx = content.find('@click="editar')
        if idx != -1:
            print(repr(content[idx-100:idx+300]))

# -----------------------------------------------------------------------
# 2. ADD the eliminar() function to the Alpine.js data object
#    Insert before cerrarModal()
# -----------------------------------------------------------------------
old_cerrar = '''            cerrarModal() {
                this.modalNuevo = false;
                this.modalEditar = false;'''

new_with_eliminar = '''            async eliminar(id, nombre) {
                if (!confirm(`¿Estás seguro de eliminar la campaña "${nombre}"? Esta acción no se puede deshacer.`)) return;
                try {
                    const response = await fetch(`/aratio/api/campanas.php?id=${id}`, {
                        method: 'DELETE',
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    });
                    const result = await response.json();
                    if (result.success) {
                        alert(result.message);
                        window.location.reload();
                    } else {
                        alert('Error: ' + result.message);
                    }
                } catch (error) {
                    alert('Error de conexión: ' + error.message);
                }
            },
            cerrarModal() {
                this.modalNuevo = false;
                this.modalEditar = false;'''

if old_cerrar in content:
    content = content.replace(old_cerrar, new_with_eliminar)
    print("✅ Funcion eliminar() agregada al objeto Alpine.")
else:
    print("❌ No se encontro cerrarModal() para insertar eliminar() antes.")

# -----------------------------------------------------------------------
# 3. Write back
# -----------------------------------------------------------------------
with sftp.open(f"{ARATIO}/pages/campanas.php", 'w') as f:
    f.write(content)
print("✅ campanas.php guardado.")

sftp.close()
client.close()
