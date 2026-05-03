# Cómo Habilitar SSH en Hostinger

## ⚠️ SSH Está Disponible Pero Deshabilitado Por Defecto

**Puerto SSH de Hostinger:** `65002` (no el puerto estándar 22)

---

## 📋 Pasos para Habilitar SSH

### 1. Login a hPanel
```
URL: https://hpanel.hostinger.com
Usuario: Tu email de Hostinger
Password: Tu password de Hostinger
```

### 2. Ir a SSH Access
```
hPanel → Websites → Manage (tu dominio) → Advanced → SSH Access
```

O buscar "SSH" en el buscador del panel.

### 3. Habilitar SSH
1. Click en el botón **"Enable SSH Access"**
2. Se generarán las credenciales automáticamente

### 4. Obtener Credenciales
Después de habilitar, verás:
```
IP Address: 212.1.208.241
Port: 65002
Username: u156469157
Password: (se mostrará en el panel)
```

**IMPORTANTE:** El password de SSH puede ser diferente al de FTP

---

## 🔐 Credenciales SSH

Una vez habilitado SSH, las credenciales serán:

```bash
Host: 212.1.208.241
Port: 65002
User: u156469157
Pass: [Obtener desde hPanel → SSH Access]
```

---

## 🧪 Probar Conexión SSH

### Desde Git Bash / Terminal
```bash
ssh u156469157@212.1.208.241 -p 65002
```

### Primer Conexión
La primera vez te preguntará si confías en el host:
```
The authenticity of host '[212.1.208.241]:65002' can't be established.
ECDSA key fingerprint is SHA256:...
Are you sure you want to continue connecting (yes/no)?
```

Responde: `yes`

Luego ingresa el password que aparece en hPanel.

---

## 📝 Comandos Útiles con SSH

### Navegar al Proyecto
```bash
ssh u156469157@212.1.208.241 -p 65002
cd /home/u156469157/domains/aratio.mrmtech.net/public_html/mod_colab
```

### Ver Archivos
```bash
ssh u156469157@212.1.208.241 -p 65002 "ls -la /home/u156469157/domains/aratio.mrmtech.net/public_html/mod_colab"
```

### Ver Logs
```bash
ssh u156469157@212.1.208.241 -p 65002 "tail -50 /home/u156469157/domains/aratio.mrmtech.net/public_html/mod_colab/storage/logs/app.log"
```

### Ejecutar Comando MySQL
```bash
ssh u156469157@212.1.208.241 -p 65002 "mysql -h auth-db690.hstgr.io -u u156469157_aratio -p15zxCeBbvgsR u156469157_aratio -e 'SELECT COUNT(*) FROM colaboradores;'"
```

---

## 🚀 Deployment con SSH (Una vez habilitado)

### Script de Deployment SSH
Una vez SSH esté habilitado, podrás usar:

```bash
bash deploy_ssh.sh
```

El script usará:
- `ssh -p 65002` para conexiones
- `scp -P 65002` para copiar archivos
- `rsync -e "ssh -p 65002"` para sincronizar

---

## 🔧 Ventajas de SSH vs FTP

**SSH habilitado te permite:**
- ✅ Deployment más rápido con rsync
- ✅ Ejecutar comandos remotos
- ✅ Ver logs en tiempo real
- ✅ Configurar permisos desde línea de comandos
- ✅ Importar BD directamente desde el servidor
- ✅ Mejor seguridad (conexión encriptada)

**FTP es suficiente si:**
- ❌ No necesitas ejecutar comandos remotos
- ❌ Prefieres interfaz gráfica (FileZilla)
- ❌ Solo subes/descargas archivos ocasionalmente

---

## 📱 Usando PuTTY (Windows)

Si prefieres PuTTY en lugar de Git Bash:

### 1. Descargar PuTTY
https://www.putty.org/

### 2. Configurar Sesión
```
Host Name: 212.1.208.241
Port: 65002
Connection type: SSH
Saved Sessions: Hostinger-Aratio
```

### 3. Conectar
1. Click "Open"
2. Login as: u156469157
3. Password: [el que aparece en hPanel]

---

## 🐛 Solución de Problemas

### Error: "Connection refused"
**Causa:** SSH no está habilitado en hPanel
**Solución:** Ir a hPanel → SSH Access → Enable

### Error: "Connection timed out"
**Causa:** Puerto incorrecto (usando 22 en lugar de 65002)
**Solución:** Usar `-p 65002` siempre

### Error: "Permission denied"
**Causa:** Password incorrecto
**Solución:** Verificar password en hPanel → SSH Access

### Error: "Host key verification failed"
**Causa:** Cambio en la clave del servidor
**Solución:**
```bash
ssh-keygen -R "[212.1.208.241]:65002"
```

---

## 🔑 SSH Keys (Opcional - Más Seguro)

### Generar SSH Key (si no tienes una)
```bash
ssh-keygen -t ed25519 -C "tu-email@example.com"
```

### Copiar Key al Servidor
```bash
ssh-copy-id -p 65002 u156469157@212.1.208.241
```

### Conectar sin Password
Una vez configurada la key:
```bash
ssh u156469157@212.1.208.241 -p 65002
# No pedirá password
```

---

## ✅ Verificación

Una vez habilitado SSH, verifica que funciona:

```bash
# Test básico
ssh u156469157@212.1.208.241 -p 65002 "echo 'SSH funciona'; pwd; whoami"

# Debe mostrar:
# SSH funciona
# /home/u156469157
# u156469157
```

---

## 📚 Referencias

**Documentación Oficial Hostinger:**
- https://support.hostinger.com/en/articles/1583245-how-to-connect-to-your-account-via-ssh
- https://support.hostinger.com/en/articles/1583645-how-to-enable-ssh-access

**Puerto SSH Hostinger:** 65002 (no usar puerto 22)

---

**Siguiente paso:** Una vez SSH habilitado, actualizar scripts de deployment para usar SSH + rsync (mucho más rápido que FTP)
