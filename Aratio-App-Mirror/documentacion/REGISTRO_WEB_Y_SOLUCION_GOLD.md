# Documentación de Formularios de Registro Web (GOLD)

Esta documentación explica las soluciones aplicadas a los formularios públicos de registro para que operen sin errores 500 y conserven los campos obligatorios solicitados en Producción.

## Archivos Responsables
La interfaz y la lógica de negocio pública ahora se manejan **exclusivamente** desde los archivos ubicados en la RAÍZ de la app. NO dependen del módulo interno `mod_colab`.

*   `registro-lider.php` (Url: `/registro-lider`)
*   `registro_simpatizante.php` (Url: `/registro-simpatizante`)

## Problemas Solucionados
1.  **Error 500 por violaciones de MySQL (`fecha_nacimiento` y `genero`)**: La base de datos estaba configurada para exigir obligatoriedad (`NOT NULL`). Se modificó el código PHP para que no permitiera valores nulos en el `$data['campo'] ?? null`.
2.  **Archivos Muertos (El Error ".htaccess")**: Las rutas "amigables" en Producción estaban apuntando hacia rutas heredadas e inservibles como `mod_colab/public/index.php`. El `.htaccess` del servidor fue rediseñado (`RewriteRule`) para apuntar directamente a los archivos raíz que hemos estado programando.

## Nuevas Características Actualizadas
*   ✅ **Fecha de nacimiento y Género obligatorios** tanto en el frontend HTML interactivo con Alpine como en el backend.
*   ✅ **Niveles de Participación Listados Completos**: Para el formulario del Lider, se introdujeron de forma enlazada y con carácter de obligatoriedad las opciones:
    *   Simpatizante
    *   Multiplicador
    *   Lider
    *   Coordinador
    *   **Contratista** (Añadido recientemente).

## Restauración de Archivos (Backup y Despliegue)
Si algún día se vuelve a caer el formulario o los scripts en el servidor Hostinger se sobreescriben accidentalmente, el código exacto y funcional ya está protegido de forma local en los archivos `registro-lider.php` y `registro_simpatizante.php` dentro de tu proyecto.

Para subirlos rápidamente al servidor por FTP usando PowerShell:

```powershell
curl.exe -f -T registro-lider.php ftp://212.1.208.241/registro-lider.php --user "u156469157.aratio.mrmtech.net:sthLX6bJPoGh"
curl.exe -f -T registro_simpatizante.php ftp://212.1.208.241/registro_simpatizante.php --user "u156469157.aratio.mrmtech.net:sthLX6bJPoGh"
```
    
También cuentas con la versión del enrutador sin los problemas en el archivo local `.htaccess_fix_remote.txt` que puede subirse como `.htaccess` directamente en tu FTP de Hostinger ante futuros bloqueos de urls.
