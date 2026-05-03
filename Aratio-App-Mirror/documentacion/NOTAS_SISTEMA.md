# 📝 NOTAS DEL SISTEMA ARATIO

**Fecha**: 29 de Enero de 2026  
**Sistema**: Aratio - Multi-Campaign Management System

---

## 🎭 Campañas en el Sistema

### Campaña Demo
**Nombre**: Jaimito el Cartero  
**Tipo**: Campaña de demostración  
**Propósito**: Mostrar funcionalidades del sistema sin afectar datos reales

**Características:**
- Datos de prueba para demostraciones
- No afecta estadísticas de producción
- Puede ser utilizada para capacitación de usuarios

---

## 🗄️ Base de Datos

### Base de Datos Principal
**Nombre**: u156469157_aratio_v1  
**Host**: auth-db690.hstgr.io  
**Uso**: Todos los registros de inscripción y colaboradores

**Tablas principales:**
- `campanas` - Campañas políticas (incluye "Jaimito el Cartero")
- `colaboradores` - Registros de colaboradores
- `territorios` - Datos geográficos

---

## 🌐 URLs del Sistema

### Producción
- **Sitio principal**: https://aratio.mrmtech.net
- **Formulario de inscripción**: https://aratio.mrmtech.net/inscripcion
- **Panel de administración**: https://aratio.mrmtech.net/login

### Desarrollo/Demo
- **Colaboradores (legacy)**: https://colaboradores.aratio.mrmtech.net
  - **Nota**: Este sitio está separado y tiene su propia base de datos
  - **Recomendación**: Actualizar enlaces para usar aratio.mrmtech.net

---

## 👤 Usuarios del Sistema

### Administrador Principal
```
Email: admin@aratio.mrmtech.net
Password: Admin123!
Rol: super-admin
```

---

## 📊 Estado Actual del Sistema

### ✅ Funcionalidades Operativas
- [x] Formulario de inscripción
- [x] Desplegables geográficos (cascada completa)
- [x] API de territorios
- [x] Registro de colaboradores
- [x] Panel de administración
- [x] Gestión de campañas

### 🎯 Campaña Demo
- [x] "Jaimito el Cartero" configurada como campaña de demostración
- [x] Puede ser usada para pruebas sin afectar datos reales

---

## 🔧 Mantenimiento

### Archivos Temporales (Pueden eliminarse)
Los siguientes archivos fueron creados para diagnóstico y pueden ser eliminados del servidor:

```
/clear_cache.php
/check_layout.php
/explore_server.php
/explore2.php
/test_subdomain.php
/simple_check.php
/find_layout.php
```

### Scripts de Despliegue (Mantener)
```
/deploy_htaccess.ps1
/deploy_inscripcion.ps1
```

---

## 📚 Documentación Disponible

1. **DOCUMENTACION_FINAL_CORRECCION_INSCRIPCION.md** - Documentación completa de correcciones
2. **FIX_DESPLEGABLES_GEOGRAFICOS_2026-01-29.md** - Detalle técnico de la corrección
3. **CREDENCIALES_ACCESO.md** - Credenciales de acceso al sistema
4. **REDIRECCION_COLABORADORES_A_ARATIO.md** - Guía para redirección
5. **NOTAS_SISTEMA.md** - Este archivo (notas generales)

---

## 🎯 Próximos Pasos Sugeridos

### Corto Plazo
1. ✅ Sistema funcionando correctamente
2. ⏳ Actualizar enlaces externos que apunten a colaboradores.aratio.mrmtech.net
3. ⏳ Limpiar archivos temporales del servidor

### Mediano Plazo
1. Monitorear registros de inscripción
2. Verificar que todos los datos lleguen a la campaña correcta
3. Capacitar usuarios usando la campaña "Jaimito el Cartero"

### Largo Plazo
1. Considerar migrar datos de colaboradores.aratio.mrmtech.net si es necesario
2. Optimizar base de datos según crecimiento
3. Implementar backups automáticos

---

**Última actualización**: 2026-01-29 20:52  
**Estado del sistema**: ✅ Operativo y funcional
