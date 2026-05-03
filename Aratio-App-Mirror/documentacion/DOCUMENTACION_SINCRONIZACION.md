# Documentación de Sincronización de Bases de Datos

Este documento detalla los procesos y herramientas disponibles para la sincronización de colaboradores en el sistema Aratio / Multi-Campaign Management System.

## Archivos de Sincronización

Actualmente existen tres scripts principales para la importación y sincronización de datos:

1.  **`import_colaboradores.php`** (API Importer)
    *   **Función:** Importa colaboradores desde una API Externa (`colaboradores.aratio.mrmtech.net`) hacia la base de datos local del sistema.
    *   **Uso:** Se ejecuta manualmente desde el navegador por un administrador.
    *   **Autenticación:** Requiere sesión de administrador iniciada.

2.  **`import mysql.py`** (DB-to-DB Sync - Python)
    *   **Función:** Conecta directamente dos bases de datos MySQL (Origen `u156469157_aratio` -> Destino `u156469157_aratio_v1`) y copia los datos.
    *   **Uso:** Ideal para ejecución manual/local rápida si se tiene Python instalado.
    *   **Requisitos:** Python 3, librería `mysql-connector-python`.

3.  **`sincronizar_bases_datos.php`** (DB-to-DB Sync - PHP) **[NUEVO]**
    *   **Función:** Misma funcionalidad que el script de Python, pero escrito en PHP nativo para facilitar su automatización en servidores de hosting (como Hostinger).
    *   **Uso:** Diseñado para **Cron Jobs** (Tareas Programadas).

---

## Automatización: Ejecutar cada hora

Para mantener las bases de datos sincronizadas automáticamente cada hora, se recomienda usar el método **B** (Cron Job en Hostinger) si el archivo está alojado en el servidor. Si deseas ejecutarlo desde tu computador local, usa el método **A**.

### Opción A: Automatización Local (Windows Task Scheduler)
*Requiere que tu computador esté encendido y conectado a internet.*

1.  Abre el "Programador de Tareas" de Windows.
2.  Crea una nueva tarea básica: "Sincronizar Aratio DB".
3.  Desencadenador: "Diariamente" -> Repetir cada 1 hora (en configuración avanzada del desencadenador).
4.  Acción: "Iniciar un programa".
5.  Programa/Script: `python`
6.  Argumentos: `"h:\Mi unidad\2025\5d\app\Multi-Campaign Management System\import mysql.py"`
7.  Guardar.

### Opción B: Automatización en Servidor (Hostinger Cron Jobs) - **RECOMENDADO**
*Funciona 24/7 sin depender de tu computador.*

1.  Sube el archivo **`sincronizar_bases_datos.php`** a tu hosting (carpeta `public_html` o una carpeta privada segura).
2.  Inicia sesión en tu panel de **Hostinger**.
3.  Busca la sección **"Cron Jobs"** (Tareas Programadas).
4.  Crea una nueva tarea cron:
    *   **Tipo:** Personalizado (Custom)
    *   **Comando:** `php /home/u156469157/domains/aratio.mrmtech.net/public_html/sincronizar_bases_datos.php`
    *   *(Nota: Verifica la ruta absoluta correcta en tu administrador de archivos)*
    *   **Frecuencia:** `0 * * * *` (Esto significa "En el minuto 0 de cada hora").
5.  Guarda la tarea.

### Opción C: Ejecución Web (No recomendada para Cron, pero útil manual)
Puedes visitar la URL:
`https://aratio.mrmtech.net/sincronizar_bases_datos.php?key=sincronizacion_segura_123`
*(Asegúrate de haber subido el archivo).*

---

## Detalles Técnicos
Ambos scripts de sincronización DB-DB realizan una operación **UPSERT**:
*   **INSERT:** Si el documento (cédula) no existe, crea un nuevo registro.
*   **UPDATE:** Si el documento ya existe, actualiza todos los campos (nombres, teléfonos, ubicación, etc.) y actualiza la fecha `updated_at`.

### Credenciales
Las credenciales están configuradas dentro de los scripts para apuntar a los servidores de base de datos de Hostinger (`auth-db690.hstgr.io`).
