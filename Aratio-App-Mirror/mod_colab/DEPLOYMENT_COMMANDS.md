# Comandos Útiles para Deployment

## Referencia Rápida de Comandos

---

## Pre-Deployment

### Verificar estado local
```bash
cd "H:/Mi unidad/2025/5d/app/colaboradores"

# Ver rama actual
git branch

# Ver estado
git status

# Ver último commit
git log --oneline -1

# Ver cambios desde último commit
git diff
```

### Crear backup local
```bash
# Backup de base de datos
mysql -u root aratio > database/backup_local_$(date +%Y%m%d_%H%M%S).sql

# Comprimir proyecto completo
tar -czf ../colaboradores_backup_$(date +%Y%m%d).tar.gz .
```

---

## Deployment

### Conexión SSH
```bash
# Conectar al servidor
ssh u156469157@212.1.208.241

# Con password prompt
ssh -o StrictHostKeyChecking=no u156469157@212.1.208.241

# Navegar al directorio web
cd /home/domains/aratio.mrmtech.net/public_html
```

### Desconectar SSH
```bash
exit
```

### Ejecutar Deployment
```bash
cd "H:/Mi unidad/2025/5d/app/colaboradores"

# Método 1: SSH + rsync (recomendado)
bash deploy.sh

# Método 2: FTP (alternativo)
bash deploy_ftp.sh
```

### Copiar archivos manualmente
```bash
# Copiar archivo específico
scp archivo.php u156469157@212.1.208.241:/home/domains/aratio.mrmtech.net/public_html/mod_colab/

# Copiar directorio completo
scp -r src/ u156469157@212.1.208.241:/home/domains/aratio.mrmtech.net/public_html/mod_colab/

# Copiar .env de producción
scp .env.production u156469157@212.1.208.241:/home/domains/aratio.mrmtech.net/public_html/mod_colab/.env
```

---

## Base de Datos

### Conexión a MySQL
```bash
# Desde local (Git Bash)
mysql -h auth-db690.hstgr.io -u u156469157_aratio -p15zxCeBbvgsR u156469157_aratio

# Desde SSH
ssh u156469157@212.1.208.241
mysql -h auth-db690.hstgr.io -u u156469157_aratio -p15zxCeBbvgsR u156469157_aratio
```

### Importar Base de Datos
```bash
# Método 1: Desde local
mysql -h auth-db690.hstgr.io -u u156469157_aratio -p15zxCeBbvgsR u156469157_aratio < database/seeds.sql

# Método 2: Copiar y luego importar vía SSH
scp database/seeds.sql u156469157@212.1.208.241:/tmp/
ssh u156469157@212.1.208.241
mysql -h auth-db690.hstgr.io -u u156469157_aratio -p15zxCeBbvgsR u156469157_aratio < /tmp/seeds.sql
exit
```

### Exportar Base de Datos
```bash
# Backup completo
mysqldump -h auth-db690.hstgr.io -u u156469157_aratio -p15zxCeBbvgsR u156469157_aratio > backup_prod_$(date +%Y%m%d).sql

# Solo estructura (sin datos)
mysqldump --no-data -h auth-db690.hstgr.io -u u156469157_aratio -p15zxCeBbvgsR u156469157_aratio > schema_only.sql

# Solo datos (sin estructura)
mysqldump --no-create-info -h auth-db690.hstgr.io -u u156469157_aratio -p15zxCeBbvgsR u156469157_aratio > data_only.sql
```

### Queries útiles
```bash
# Contar colaboradores
mysql -h auth-db690.hstgr.io -u u156469157_aratio -p15zxCeBbvgsR u156469157_aratio \
  -e "SELECT COUNT(*) as total FROM colaboradores;"

# Contar usuarios
mysql -h auth-db690.hstgr.io -u u156469157_aratio -p15zxCeBbvgsR u156469157_aratio \
  -e "SELECT COUNT(*) as total FROM usuarios;"

# Ver tablas
mysql -h auth-db690.hstgr.io -u u156469157_aratio -p15zxCeBbvgsR u156469157_aratio \
  -e "SHOW TABLES;"

# Ver estructura de tabla
mysql -h auth-db690.hstgr.io -u u156469157_aratio -p15zxCeBbvgsR u156469157_aratio \
  -e "DESCRIBE colaboradores;"

# Limpiar sesiones expiradas
mysql -h auth-db690.hstgr.io -u u156469157_aratio -p15zxCeBbvgsR u156469157_aratio \
  -e "DELETE FROM sesiones WHERE expires_at < NOW();"

# Ver usuarios del sistema
mysql -h auth-db690.hstgr.io -u u156469157_aratio -p15zxCeBbvgsR u156469157_aratio \
  -e "SELECT id, usuario, email, tipo_usuario, activo FROM usuarios;"
```

---

## Gestión de Archivos en Servidor

### Listar archivos
```bash
ssh u156469157@212.1.208.241 "ls -la /home/domains/aratio.mrmtech.net/public_html/mod_colab"
```

### Ver contenido de archivo
```bash
ssh u156469157@212.1.208.241 "cat /home/domains/aratio.mrmtech.net/public_html/mod_colab/.env"
```

### Editar archivo (con nano)
```bash
ssh u156469157@212.1.208.241
nano /home/domains/aratio.mrmtech.net/public_html/mod_colab/.env
# Ctrl+O para guardar, Ctrl+X para salir
exit
```

### Permisos
```bash
ssh u156469157@212.1.208.241 << 'ENDSSH'
cd /home/domains/aratio.mrmtech.net/public_html/mod_colab

# Ver permisos
ls -la

# Cambiar permisos de directorios
chmod -R 755 .
chmod -R 775 storage
chmod -R 775 storage/logs
chmod -R 775 storage/cache
chmod -R 775 cache
chmod -R 775 public/uploads

# Cambiar permisos de .env
chmod 600 .env

# Verificar permisos críticos
ls -ld storage
ls -ld cache
ls -l .env

ENDSSH
```

### Crear directorios
```bash
ssh u156469157@212.1.208.241 << 'ENDSSH'
cd /home/domains/aratio.mrmtech.net/public_html/mod_colab
mkdir -p storage/logs
mkdir -p storage/cache
mkdir -p cache
mkdir -p public/uploads
ENDSSH
```

### Eliminar archivos/directorios
```bash
ssh u156469157@212.1.208.241 << 'ENDSSH'
cd /home/domains/aratio.mrmtech.net/public_html/mod_colab

# Eliminar archivo
rm archivo.txt

# Eliminar directorio vacío
rmdir directorio

# Eliminar directorio con contenido
rm -rf directorio

# Limpiar cache
rm -rf storage/cache/*
rm -rf cache/*

ENDSSH
```

---

## Logs y Debugging

### Ver logs en tiempo real
```bash
ssh u156469157@212.1.208.241 << 'ENDSSH'
cd /home/domains/aratio.mrmtech.net/public_html/mod_colab

# Ver logs de aplicación
tail -f storage/logs/app.log

# Ver logs de seguridad
tail -f storage/logs/security.log

# Ctrl+C para salir

ENDSSH
```

### Ver últimas líneas de logs
```bash
ssh u156469157@212.1.208.241 \
  "tail -50 /home/domains/aratio.mrmtech.net/public_html/mod_colab/storage/logs/app.log"

ssh u156469157@212.1.208.241 \
  "tail -50 /home/domains/aratio.mrmtech.net/public_html/mod_colab/storage/logs/security.log"
```

### Buscar en logs
```bash
ssh u156469157@212.1.208.241 << 'ENDSSH'
cd /home/domains/aratio.mrmtech.net/public_html/mod_colab

# Buscar errores
grep -i error storage/logs/app.log

# Buscar palabra específica
grep -i "database" storage/logs/app.log

# Buscar en últimas 100 líneas
tail -100 storage/logs/app.log | grep -i error

ENDSSH
```

### Limpiar logs
```bash
ssh u156469157@212.1.208.241 << 'ENDSSH'
cd /home/domains/aratio.mrmtech.net/public_html/mod_colab

# Vaciar log pero mantener archivo
> storage/logs/app.log
> storage/logs/security.log

# O eliminar completamente
rm storage/logs/app.log
rm storage/logs/security.log

ENDSSH
```

---

## Verificación y Testing

### Ejecutar script de verificación
```bash
cd "H:/Mi unidad/2025/5d/app/colaboradores"
bash verify_deployment.sh
```

### Test manual de URLs
```bash
# Verificar URL principal
curl -I https://aratio.mrmtech.net/mod_colab

# Verificar login
curl -I https://aratio.mrmtech.net/mod_colab/login

# Verificar API (debe redirigir si no autenticado)
curl -I https://aratio.mrmtech.net/mod_colab/territorios/departamentos

# Ver contenido de página
curl -s https://aratio.mrmtech.net/mod_colab/login | grep csrf_token
```

### Verificar HTTPS
```bash
# Ver certificado SSL
echo | openssl s_client -connect aratio.mrmtech.net:443 2>/dev/null | openssl x509 -noout -dates

# Ver headers de seguridad
curl -I https://aratio.mrmtech.net/mod_colab/login | grep -i "x-frame\|x-content\|x-xss"
```

---

## Monitoreo

### Ver espacio en disco
```bash
ssh u156469157@212.1.208.241 << 'ENDSSH'
# Espacio total
df -h

# Espacio de mod_colab
cd /home/domains/aratio.mrmtech.net/public_html
du -sh mod_colab

# Espacio por directorio
cd mod_colab
du -sh */

ENDSSH
```

### Ver procesos
```bash
ssh u156469157@212.1.208.241 << 'ENDSSH'
# Ver procesos PHP
ps aux | grep php

# Ver uso de memoria
free -h

ENDSSH
```

---

## Backup y Restore

### Backup completo del proyecto
```bash
# Desde servidor
ssh u156469157@212.1.208.241 << 'ENDSSH'
cd /home/domains/aratio.mrmtech.net/public_html
tar -czf mod_colab_backup_$(date +%Y%m%d_%H%M%S).tar.gz mod_colab/
ls -lh mod_colab_backup_*.tar.gz
ENDSSH

# Descargar backup
scp u156469157@212.1.208.241:/home/domains/aratio.mrmtech.net/public_html/mod_colab_backup_*.tar.gz ./
```

### Restore desde backup
```bash
# Subir backup
scp mod_colab_backup_YYYYMMDD.tar.gz u156469157@212.1.208.241:/tmp/

# Restaurar
ssh u156469157@212.1.208.241 << 'ENDSSH'
cd /home/domains/aratio.mrmtech.net/public_html

# Backup del actual (por si acaso)
mv mod_colab mod_colab_old

# Restaurar
tar -xzf /tmp/mod_colab_backup_YYYYMMDD.tar.gz

# Verificar
ls -la mod_colab

ENDSSH
```

---

## Rollback

### Rollback rápido
```bash
ssh u156469157@212.1.208.241 << 'ENDSSH'
cd /home/domains/aratio.mrmtech.net/public_html

# Renombrar versión problemática
mv mod_colab mod_colab_broken

# Restaurar backup
mv mod_colab_backup mod_colab

# O desde tar.gz
tar -xzf mod_colab_backup_YYYYMMDD.tar.gz

ENDSSH
```

---

## Comandos de Emergencia

### Deshabilitar sitio temporalmente
```bash
ssh u156469157@212.1.208.241 << 'ENDSSH'
cd /home/domains/aratio.mrmtech.net/public_html

# Renombrar
mv mod_colab mod_colab_disabled

# Crear página de mantenimiento
mkdir mod_colab
echo "<h1>Sitio en mantenimiento</h1><p>Volveremos pronto.</p>" > mod_colab/index.html

ENDSSH
```

### Habilitar debug temporalmente
```bash
ssh u156469157@212.1.208.241 << 'ENDSSH'
cd /home/domains/aratio.mrmtech.net/public_html/mod_colab

# Backup de .env
cp .env .env.backup

# Habilitar debug
sed -i 's/APP_DEBUG=false/APP_DEBUG=true/' .env

# Verificar cambio
grep APP_DEBUG .env

ENDSSH

# IMPORTANTE: Deshabilitar cuando termines
ssh u156469157@212.1.208.241 << 'ENDSSH'
cd /home/domains/aratio.mrmtech.net/public_html/mod_colab
sed -i 's/APP_DEBUG=true/APP_DEBUG=false/' .env
ENDSSH
```

### Limpiar todo el cache
```bash
ssh u156469157@212.1.208.241 << 'ENDSSH'
cd /home/domains/aratio.mrmtech.net/public_html/mod_colab

# Limpiar cache de aplicación
rm -rf storage/cache/*
rm -rf cache/*

# Limpiar sesiones expiradas (vía BD)
mysql -h auth-db690.hstgr.io -u u156469157_aratio -p15zxCeBbvgsR u156469157_aratio \
  -e "DELETE FROM sesiones WHERE expires_at < NOW();"

echo "Cache limpiado"

ENDSSH
```

---

## Útiles para Git Bash en Windows

### Variables de entorno
```bash
# Definir variables para uso rápido
export SSH_HOST="u156469157@212.1.208.241"
export REMOTE_DIR="/home/domains/aratio.mrmtech.net/public_html/mod_colab"
export DB_HOST="auth-db690.hstgr.io"
export DB_USER="u156469157_aratio"
export DB_PASS="15zxCeBbvgsR"
export DB_NAME="u156469157_aratio"

# Ahora puedes usar:
ssh $SSH_HOST
mysql -h $DB_HOST -u $DB_USER -p$DB_PASS $DB_NAME
```

### Alias útiles
```bash
# Agregar al ~/.bashrc
alias ssh-prod='ssh u156469157@212.1.208.241'
alias mysql-prod='mysql -h auth-db690.hstgr.io -u u156469157_aratio -p15zxCeBbvgsR u156469157_aratio'
alias deploy='bash deploy.sh'
alias verify='bash verify_deployment.sh'

# Recargar
source ~/.bashrc

# Ahora puedes usar:
ssh-prod
mysql-prod
deploy
verify
```

---

## Notas Importantes

### Caracteres Especiales en Passwords

- SSH password: `sthLX6bJPoGh$` (tiene $, usar comillas simples en scripts)
- MySQL password: `15zxCeBbvgsR` (sin caracteres especiales)

### Rutas Importantes

- Directorio web: `/home/domains/aratio.mrmtech.net/public_html/mod_colab`
- Logs: `/home/domains/aratio.mrmtech.net/public_html/mod_colab/storage/logs`
- Public: `/home/domains/aratio.mrmtech.net/public_html/mod_colab/public`

### URLs Importantes

- Aplicación: https://aratio.mrmtech.net/mod_colab
- Login: https://aratio.mrmtech.net/mod_colab/login
- Dashboard: https://aratio.mrmtech.net/mod_colab/dashboard
- hPanel: https://hpanel.hostinger.com

---

**Última actualización:** 2025-11-15
