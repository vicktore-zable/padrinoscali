# Guía de Recuperación de Acceso

Si en algún momento no puedes ingresar al sistema, sigue estos pasos:

## 1. Restablecer mediante Script (Recomendado)
Ejecuta el siguiente comando en la terminal desde la raíz del proyecto:

```bash
php reset_passwords.php
```

Esto restablecerá la contraseña de **todos** los usuarios (incluyendo `admin`) a:
**Contraseña:** `Admin123!`

También desbloqueará cuentas por intentos fallidos y cerrará sesiones activas.

## 2. Restablecer Manualmente (Base de Datos)
Si no tienes acceso a la terminal, ingresa a tu gestor de base de datos (phpMyAdmin) y ejecuta el siguiente SQL:

```sql
UPDATE usuarios SET 
    password = '$2y$12$jJUpKd3XORjihRd64FZjeOjdo4V7WgjOVVFCCgmBIE1Ol8Yu0N85kye', 
    activo = 1, 
    intentos_fallidos = 0, 
    bloqueado_hasta = NULL 
WHERE usuario = 'admin';
```

## 3. Prevención
- Asegúrate de que el archivo `.env` tenga la URL correcta (`APP_URL`).
- Si usas el sistema en local y producción simultáneamente, verifica que no haya conflictos de cookies (usa navegadores diferentes o modo incógnito).
