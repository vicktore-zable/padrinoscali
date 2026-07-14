# Documentación Técnica: Flujo de Configuración Inicial Obligatoria
**Proyecto:** Aratio - Sistema de Gestión Electoral
**Fecha:** 17 de Febrero de 2026
**Módulo:** Portal de Líderes (mod_lider)
**Versión:** 1.1.0-gold

## 1. Objetivo
Garantizar que todos los líderes que acceden por primera vez al portal proporcionen un correo electrónico corporativo real y cambien su contraseña temporal (teléfono) por una segura.

## 2. Descripción del Flujo (User Journey)

### Paso 1: Autenticación Inicial
El líder ingresa sus credenciales en `/index.php?page=portal_login`:
- **Usuario:** Número de Documento.
- **Contraseña:** Número de Teléfono (almacenado en la tabla `colaboradores`).

### Paso 2: Detección de Primer Acceso
El controlador `PortalAuthController` verifica si el email del usuario contiene el dominio temporal `@aratio.tmp`. 
- Si es temporal: Se le redirige forzosamente a `?page=portal_setup`.
- Si es real: Se le permite el acceso normal al dashboard.

### Paso 3: Configuración de Datos (Setup)
En la página de configuración, el líder debe completar:
1. **Email Profesional:** Donde recibirá notificaciones e instrucciones.
2. **Nueva Contraseña:** Mínimo 8 caracteres, con validación de seguridad.
3. **Confirmación de Contraseña.**

### Paso 4: Finalización y Notificación
Al guardar, el sistema:
1. Cifra la nueva contraseña con BCRYPT.
2. Actualiza el email y la contraseña en la tabla `usuarios`.
3. Envía un **Email Automático de Bienvenida** con:
   - URL de acceso.
   - Confirmación de usuario.
   - Instrucciones de uso.
4. Redirige al Líder a su **Dashboard Final**.

## 3. Componentes Técnicos Actualizados

### Archivos Nuevos:
- `pages/portal_setup.php`: Punto de entrada (Router).
- `mod_lider/src/Views/portal/auth/setup.php`: Interfaz de usuario premium (Tailwind CSS + Alpine.js).

### Archivos Modificados:
- `mod_lider/src/Controllers/PortalAuthController.php`:
    - `loginUser()`: Lógica de interceptación y redirección.
    - `showSetup()`: Renderizado de la vista de configuración.
    - `processSetup()`: Validación de datos, actualización de DB y envío de emails.
- `index.php`: Inclusión de `portal_setup` en la lista blanca de páginas permitidas.

## 4. Credenciales de Prueba
- **URL Login:** [https://aratio.mrmtech.net/index.php?page=portal_login](https://aratio.mrmtech.net/index.php?page=portal_login)
- **Usuario:** `1130665763`
- **Contraseña Temporal:** `3178386580`

---
*Desarrollado por Antigravity AI para Aratio System.*
