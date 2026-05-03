# 🚀 Deployment Quick Start

## Información Esencial

**URL:** https://colaboradores.aratio.mrmtech.net/
**Status:** ✅ Credenciales verificadas
**Método:** SSH (puerto 65002) o FTP

---

## Pasos Rápidos

### 0. Habilitar SSH (Opcional pero Recomendado)
```
1. Login a https://hpanel.hostinger.com
2. Ir a: SSH Access
3. Click: Enable SSH Access
4. Anotar password SSH que aparece

Ver: ENABLE_SSH.md para detalles
```

### 1. Subir Archivos (5-10 minutos)

**Opción A - Con SSH (más rápido):**
```bash
cd "H:/Mi unidad/2025/5d/app/colaboradores"
bash deploy_ssh.sh
```

**Opción B - Con FTP (funciona siempre):**
```bash
cd "H:/Mi unidad/2025/5d/app/colaboradores"
bash deploy_ftp_improved.sh
```

**Opción C - Script interactivo:**
```bash
bash deploy.sh
# Seleccionar opción 1 (SSH) o 2 (FTP)
```

### 2. Importar Base de Datos (2-5 minutos)

**Opción A - MySQL CLI (recomendado):**
```bash
mysql -h auth-db690.hstgr.io \
      -u u156469157_aratio \
      -p15zxCeBbvgsR \
      u156469157_aratio < database/seeds.sql
```

**Opción B - phpMyAdmin:**
1. Login a https://hpanel.hostinger.com
2. Database → phpMyAdmin
3. Seleccionar `u156469157_aratio`
4. Import → `database/seeds.sql`
5. Ejecutar

### 3. Configurar Permisos (1-2 minutos)

Desde hPanel → File Manager:
- `mod_colab/storage/` → 755
- `mod_colab/storage/logs/` → 755
- `mod_colab/storage/cache/` → 755
- `mod_colab/public/uploads/` → 755

### 4. Verificar (1 minuto)
1. Abrir: https://colaboradores.aratio.mrmtech.net/
2. Debe redirigir a `/login`
3. Login: admin / Admin123!
4. Debe cargar dashboard

---

## Si algo falla

### Error de conexión a BD
```bash
# Probar conexión
mysql -h auth-db690.hstgr.io -u u156469157_aratio -p15zxCeBbvgsR u156469157_aratio -e "SELECT 1;"
```

### 403 Forbidden persistente
- Verificar que `index.php` existe en `/mod_colab/public/`
- Verificar permisos de `public/` (755)

### 500 Internal Server Error
- Verificar versión PHP en hPanel (debe ser 8.0+)
- Revisar Error Logs en hPanel

---

## Credenciales

### SSH (Puerto 65002)
```
Host: 212.1.208.241
Port: 65002
User: u156469157
Pass: [Ver en hPanel → SSH Access]
```

### FTP
```
Host: 212.1.208.241
User: u156469157.aratio.mrmtech.net
Pass: sthLX6bJPoGh
Dir:  /mod_colab/
```

### MySQL
```
Host: auth-db690.hstgr.io
User: u156469157_aratio
Pass: 15zxCeBbvgsR
DB:   u156469157_aratio
```

### Login Sistema
```
admin / Admin123!
mgarcia / Admin123!
consulta / Admin123!
```

---

## Documentación Completa

- **DEPLOYMENT_INFO.md** - Información detallada y pruebas
- **DEPLOYMENT_GUIDE.md** - Guía paso a paso completa
- **DEPLOYMENT_CHECKLIST.txt** - Checklist imprimible
- **DEPLOYMENT_COMMANDS.md** - Referencia de comandos

---

**Tiempo total estimado:** 10-20 minutos
