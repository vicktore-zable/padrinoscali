# Sistema de Gestión de Colaboradores - A Ratio

Sistema integral para gestión de colaboradores políticos con relaciones jerárquicas, reportes avanzados y visualización de redes.

**Versión:** 1.0.1  
**Última actualización:** 15 de Noviembre 2025

## ✨ Lo Nuevo en Esta Versión (1.0.1 - 2025-11-15)

### Correcciones Críticas
- ✅ **Login funcionando perfectamente** - Corregidos errores de namespace y campos 2FA
- ✅ **Código limpio** - Eliminados 27+ archivos temporales y de debug
- ✅ **Listo para producción** - Sistema depurado y documentado

### Versión Anterior (1.0.0)
- ✅ Campo **tipo_territorio** (Urbano/Rural) en colaboradores
- ✅ Dropdown de **municipios** (Cali, La Cumbre, Palmira, Tuluá, Vijes, Yumbo)
- ✅ Campos adicionales: **barrio**, **detalle_ubicacion**, **telefono_whatsapp**
- ✅ **Reporte por territorios** mejorado con tableros consolidados interactivos
- ✅ **Ranking de líderes** con podio visual (oro/plata/bronce)
- ✅ **Sistema de curriculum** completo con modales Alpine.js
- ✅ **Auto-registro** de usuarios y recuperación de contraseña
- ✅ Todas las vistas actualizadas y funcionales

## 🚀 Características

### Gestión de Colaboradores
- CRUD completo con validación
- 12 perfiles políticos
- 5 niveles de participación  
- Ubicación detallada (departamento, municipio, barrio, tipo territorio)
- Contacto completo (email, teléfono, WhatsApp)
- Relaciones jerárquicas líder-seguidor

### Visualización de Redes
- Gráfico jerárquico interactivo (Vis.js 9.1.9)
- 3 niveles de profundidad
- Navegación por clics
- Estadísticas de red en tiempo real

### Reportes Avanzados
1. **Por Territorios**: Consolidado por municipios con 4 desgloses detallados
2. **Ranking Líderes**: Top 50 con podio visual
3. Por Perfiles (en desarrollo)
4. Crecimiento (en desarrollo)

### Sistema de Usuarios
- 3 tipos: Admin, Líder, Consulta
- Auto-registro con validación
- Recuperación de contraseña segura
- RBAC completo

## 📋 Instalación Rápida

```bash
# 1. Clonar
git clone https://github.com/vicktore/colaboradores-aratio.git
cd colaboradores-aratio

# 2. BD
mysql -u root -p -e "CREATE DATABASE aratio;"
mysql -u root -p aratio < database/schema.sql
mysql -u root -p aratio < database/seeds.sql  # Opcional

# 3. Configurar
cp .env.example .env
# Editar .env con tus credenciales

# 4. Iniciar
php -S localhost:8000 -t public
```

Acceder: **http://localhost:8000**

## 👤 Usuarios de Prueba

| Usuario | Contraseña | Tipo |
|---------|-----------|------|
| admin | Admin123! | Administrador |
| mgarcia | Admin123! | Líder |
| consulta | Admin123! | Consulta |

## 🛠️ Stack

- PHP 8.2+
- MySQL 8.0+
- Tailwind CSS 3.x
- Alpine.js 3.x
- Vis.js Network 9.1.9

## 📁 Estructura

```
colaboradores/
├── config/          # Configuración
├── database/        # SQL (schema, seeds)
├── public/          # Entry point
├── src/
│   ├── Controllers/
│   ├── Models/
│   ├── Views/
│   ├── Core/
│   ├── Middleware/
│   └── Utils/
└── .env            # Configuración de entorno
```

## 🔒 Seguridad

- ✅ CSRF Protection
- ✅ SQL Injection prevention (prepared statements)
- ✅ XSS Protection
- ✅ Rate Limiting
- ✅ Password hashing (bcrypt)
- ✅ Security headers
- ✅ Audit logs automáticos

## 📝 Licencia

Proyecto privado - © A Ratio 2025

---

**Desarrollado con ❤️ por el equipo A Ratio**
