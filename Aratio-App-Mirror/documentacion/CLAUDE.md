# CLAUDE.md - Multi-Campaign Management System (Aratio)

## 📋 Descripción General

Sistema de gestión electoral **multi-tenant** desarrollado en **PHP puro** con **Tailwind CSS** y **Alpine.js**. Gestiona campañas políticas en Colombia con jerarquía territorial de 5 niveles y metodología de compromisos.

**Estado**: ✅ **SISTEMA INTEGRADO (Aratio + Colaboradores)**
**Versión**: 2.0.0 (Integración Colaboradores)
**Última Actualización**: 27 de Enero de 2026

---

## 🏗️ Estructura del Proyecto

```
Multi-Campaign Management System/
├── api/                 # APIs REST (GET, POST, PUT, DELETE)
│   ├── campanas.php
│   ├── colaboradores.php    # ✅ V2.0 Integrado
│   └── ...
│
├── config/
│   └── config.php       # Configuración + detección auto de entorno
│
├── includes/
│   └── Auth.php         # Autenticación y autorización
│
├── pages/               # MÓDULOS WEB
│   ├── dashboard.php
│   ├── colaboradores.php
│   ├── colaboradores_red.php    # ✅ V2.0 Grafo Vis.js
│   ├── registro_asistencia.php  # ✅ Acceso público
│   └── ...
│
├── database/
│   ├── schema.sql
│   └── migrations/
│
├── index.php            # Entry point principal
├── login.php            # Autenticación
├── logout.php
├── migrate_colaboradores.php # 🔄 Script migración
└── .htaccess            # Seguridad Apache
```

---

## 🌍 Entornos y Credenciales

### Producción (Hostinger)

**Dominio**: `edisongiraldo.com/aratio`
**IP**: `157.173.208.254`

| Recurso | Host | Usuario | Password | Base de Datos |
|---------|------|---------|----------|---------------|
| **Aratio Core** | `157.173.208.254` | `u577647812_aratio` | `E=j$`01yHi^?XfpoM@|CD"5H4` | `u577647812_aratio` |

**Acceso SSH / SFTP**:
- **Puerto**: `65002`
- **Usuario**: `u577647812`
- **Host**: `157.173.208.254`
- **Comando**: `ssh -p 65002 u577647812@157.173.208.254`

### Desarrollo Local
- **URL**: `http://localhost/aratio`
- **DB Local**: `u577647812_aratio` (root/sin pass)
- **Simulación**: Autenticación puenteada para desarrollo rápido.

### Credenciales Admin Sistema
- **Email**: `admin@edisongiraldo.com`
- **Password**: `Admin123!`
- **Rol**: `super-admin`

---

## 🔄 Integración Colaboradores (V2.0.0)

El sistema ha evolucionado de tener dos aplicaciones separadas a una **plataforma unificada**.

### Componentes Integrados:
1.  **Red Jerárquica (Vis.js)**: Visualización de nodos (líderes-seguidores) interactiva.
2.  **Reportes Avanzados**: KPIs de crecimiento, distribución territorial y demográfica.
3.  **Perfil 360**: Pestañas de Info, Curriculum, Historial y Seguidores.
4.  **Migración de Datos**: Script `migrate_colaboradores.php` para importar desde la DB antigua (`_aratio`) a la nueva (`_top_v1`).

### Estructura de Datos Nueva:
- **`curriculum`**: JSONs para experiencia laboral, académica y política.
- **`historial_cambios_lider`**: Auditoría de movimientos en la jerarquía.
- **`historial_estados`**: Trazabilidad de crecimiento (Nuevo -> Creció -> Decrece).

**Nota Técnica**: El campo `estado` NO existe en BD, es calculado en realtime (PHP) comparando `dato_potencial` vs `dato_historico`.

---

## 🔐 Autenticación y Seguridad

### Estado Actual:
- **Público**: `registro_asistencia.php` (QR) - Acceso libre para ciudadanos.
- **Privado (PHP)**: Panel administrativo requiere sesión (`Auth.php`).
- **Desarrollo (React)**: Login simulado para agilidad.

### Roles:
1.  **super-admin**: Acceso total y migración de datos.
2.  **admin-campana**: Gestión completa de SU campaña.
3.  **coordinador**: Gestión operativa sin eliminación.
4.  **colaborador**: Registro básico.
5.  **veedor**: Solo lectura y reportes.

---

## 🚀 Deployment y Mantenimiento

### Actualización Reciente (26 Ene 2026)
Se desplegaron los módulos de colaboradores mediante curl SFTP al puerto 65002.

**Scripts de Utilería (Eliminar post-uso):**
- `migrate_colaboradores.php`: Migración entre bases de datos.
- `run_migration.php`: Actualización de esquema SQL.
- `check_*.php`: Diagnósticos.

### Comandos Útiles
```bash
# Conexión SSH Producción
ssh -p 65002 u577647812@157.173.208.254

# Subir archivo rápido (curl)
curl -k -T archivo.php "sftp://u577647812:E=j$`01yHi^?XfpoM@|CD\"5H4@157.173.208.254:65002/home/u577647812/domains/edisongiraldo.com/public_html/aratio/"
```

---

## 📚 Documentación Referencia
- `src/php-export/DOCUMENTACION_INTEGRACION_COLABORADORES.md` (Detalle técnico integración)
- `src/php-export/DEPLOYMENT_COLABORADORES_2026-01-26.md` (Log de cambios v2.0)

