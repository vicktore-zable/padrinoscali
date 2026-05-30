# Guía de Mantenimiento y Sincronización

Esta guía explica cómo mantener este desarrollo (`edisongiraldo.com`) actualizado con las mejoras que realices en el software base (`AratioPRO`).

## Sincronización desde el Núcleo (Core)

Si realizas ajustes en el código base de **AratioPRO** (ubicado en `H:\Mi unidad\2025\5d\app\Multi-Campaign Management System`), puedes replicar esos cambios en este entorno local con una sola instrucción.

### Comando de Sincronización
Para actualizar tu entorno local con el núcleo, solo debes pedirme:
> **"Sincroniza desde el núcleo"**

O ejecutar manualmente el script:
```powershell
python "h:\Mi unidad\2026\Cali\Edisongiraldo.com\sync_from_core.py"
```

### ¿Qué sucede durante la sincronización?
El script `sync_from_core.py` realiza lo siguiente:
1.  **Analiza los cambios** en el software base v2025.
2.  **Copia los archivos nuevos o modificados** hacia tu servidor local (`F:aratio`).
3.  **Protege tu configuración:** El script está configurado para **ignorar** archivos específicos de esta instancia, asegurando que NO se sobrescriban:
    *   `config/config.php` (Protección de credenciales)
    *   `root_config.php` (Protección de identidad visual)
    *   `.htaccess` (Protección de rutas del servidor)
    *   Carpeta `uploads/` (Protección de imágenes de usuarios)

---

## Flujo de Trabajo Recomendado

1.  **Actualización Base:** Realizas cambios en `AratioPRO` (v2025).
2.  **Réplica Local:** Ejecutas (o me pides) el comando de sincronización.
3.  **Prueba Local:** Verificas que todo funcione correctamente en `http://localhost/aratio/`.
4.  **Despliegue a Producción:** Una vez validado, subimos los cambios a Hostinger usando el script de despliegue.
5.  **Respaldo en Workspace:** Al finalizar, ejecutamos el mirror para que tu Google Drive quede actualizado.

> [!IMPORTANT]
> Gracias a la refactorización que hicimos ayer, ahora puedes sincronizar el núcleo sin miedo a perder la configuración de base de datos o los colores personalizados de esta campaña.

---

## Otros Comandos Útiles

| Acción | Comando |
| :--- | :--- |
| **Subir a Producción** | `python upload_to_prod.py [local] [remoto]` |
| **Respaldo en Drive** | `python mirror_local_to_workspace.py` |
| **Verificar Errores** | `python check_error_log.py` |
