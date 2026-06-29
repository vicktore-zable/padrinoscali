# Plan: Sistema de Cumpleaños por WhatsApp

## Objetivo
Enviar mensajes de felicitación por WhatsApp a colaboradores en su cumpleaños, con plantillas según perfil, y panel admin para visualizar cumpleañeros por rango de tiempo y auditoría de envíos.

## Proveedor
**WATI** (whatsappteam.com) — API REST, económico para LatAm, sin aprobación Meta.
- Crear cuenta y obtener API key antes del paso 3.

---

## Archivos del Feature

| Archivo | Tipo | Descripción |
|---------|------|-------------|
| `cron/birthday_check.php` | NUEVO | Script diario (cron 8 AM) |
| `includes/whatsapp.php` | NUEVO | Clase WhatsAppApi |
| `api/whatsapp.php` | NUEVO | Endpoints REST (historial, cumpleaños, reenviar) |
| `pages/whatsapp_log.php` | NUEVO | Panel admin Alpine.js + Tailwind |
| `config/config.php` | MODIFICAR | Agregar WHATSAPP_CONFIG |
| `root_config.php` | MODIFICAR | Constantes WhatsApp |
| BD: `whatsapp_log` | NUEVA | Tabla de auditoría |
| `index.php` | MODIFICAR | Ruta en menú lateral |

---

## Orden de desarrollo

### Paso 1 — BD

```sql
CREATE TABLE whatsapp_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    colaborador_id INT NOT NULL,
    colaborador_nombre VARCHAR(200),
    telefono_whatsapp VARCHAR(20),
    perfil VARCHAR(50),
    template_used VARCHAR(50),
    mensaje_enviado TEXT,
    estado ENUM('enviado','fallido','pendiente','sin_whatsapp') NOT NULL,
    error_msg TEXT NULL,
    sent_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_fecha (sent_at),
    INDEX idx_estado (estado),
    INDEX idx_colaborador (colaborador_id)
);
```

### Paso 2 — Config (`config/config.php` + `root_config.php`)

Agregar siguiendo patrón de `MAIL_CONFIG`:
```php
define('WHATSAPP_PROVIDER', 'wati');
define('WATI_API_URL', 'https://wati.live/api/v1/sendTemplateMessage');
define('WATI_API_KEY', '');
define('WATI_NUMBER', '');
```

### Paso 3 — `includes/whatsapp.php`

Clase `WhatsAppApi`:
- `send(telefono, mensaje)` → POST a WATI
- `formatPhone(numero)` → +57 prefix, quita espacios/guiones
- `getTemplate(perfil)` → selecciona plantilla según perfil
- `fillTemplate(template, nombres)` → reemplaza {nombres}
- `log(db, data, estado, error)` → INSERT en whatsapp_log

### Plantillas por perfil

```php
$templates = [
    'lider' => "¡Feliz cumpleaños, {nombres}! 🎉🌟 Gracias por ser un líder excepcional en nuestra comunidad. Tu compromiso con Padrinos Cali inspira a todos. ¡Que tengas un día lleno de bendiciones! 🎂🎈",
    'simpatizante' => "¡Feliz cumpleaños, {nombres}! 🎂🎈 Te deseamos un día maravilloso lleno de alegría. Gracias por ser parte de esta gran familia Padrinos Cali. ¡Un abrazo enorme! 🤗",
    'movilizador' => "¡Feliz cumpleaños, {nombres}! 🚀💪 Tu energía y compromiso con nuestra causa son admirables. ¡Gracias por movilizar el cambio en Cali! Que este nuevo año esté lleno de logros. 🎉",
    'familia' => "¡Feliz cumpleaños, {nombres}! 🏡💛 Que este día especial esté rodeado del amor de tu familia. Gracias por ser parte de Padrinos Cali. ¡Muchas felicidades! 🎂",
    'default' => "¡Feliz cumpleaños, {nombres}! 🎉 Te deseamos un día maravilloso. Gracias por ser parte de Padrinos Cali. 🎂🎈"
];
```

### Paso 4 — `cron/birthday_check.php`

```php
<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/whatsapp.php';

$db = getDB();
$hoy = date('m-d');

$stmt = $db->prepare("
    SELECT id, nombres, apellidos, telefono_whatsapp, perfil, telefono
    FROM colaboradores
    WHERE DATE_FORMAT(fecha_nacimiento, '%m-%d') = ?
      AND telefono_whatsapp IS NOT NULL AND telefono_whatsapp != ''
      AND (estado IS NULL OR estado NOT IN ('inactivo','Inactivo'))
");
$stmt->execute([$hoy]);
$cumpleaneros = $stmt->fetchAll(PDO::FETCH_ASSOC);

$api = new WhatsAppApi();
foreach ($cumpleaneros as $p) {
    try {
        $template = $api->getTemplate($p['perfil']);
        $mensaje = $api->fillTemplate($template, $p['nombres']);
        $telefono = $api->formatPhone($p['telefono_whatsapp']);
        $api->send($telefono, $mensaje);
        $api->log($db, $p, $template, $mensaje, 'enviado', null);
    } catch (Exception $e) {
        $api->log($db, $p, $template ?? '', $mensaje ?? '', 'fallido', $e->getMessage());
        error_log("Birthday cron error [{$p['id']}]: " . $e->getMessage());
    }
}
```

### Paso 5 — `api/whatsapp.php`

Endpoints:
- `?action=cumpleanos&rango=hoy` — lista cumpleañeros en rango
  - `rango`: hoy / semana / mes / personalizado
  - `desde` / `hasta`: para personalizado
  - Retorna: colaboradores con cumpleaños en rango + estado último envío
- `?action=historial&page=1&estado=&desde=&hasta=` — historial paginado
- `?action=reenviar&id=X` — reenvía mensaje a un colaborador específico

### Paso 6 — `pages/whatsapp_log.php`

Panel Alpine.js con 3 secciones:

**Sección 1: Calendario de Cumpleaños**
- Tabs: Hoy · Esta semana · Este mes · Personalizado
- Cards por día con lista de cumpleañeros
- Cada card: nombre, edad, perfil, teléfono, estado envío (ícono)
- Botón "Enviar ahora" por persona
- Botón "Exportar CSV"

**Sección 2: Historial de Envíos**
- Tabla paginada con filtros (fecha, estado, perfil)
- Columnas: fecha, colaborador, teléfono, template, estado, error

**Sección 3: Estadísticas**
- Cards: enviados hoy, este mes, tasa de éxito, próximos 7 días

### Paso 7 — `index.php`

Agregar en menú lateral (entre "Reportes" y "Configuración"):
```html
<a href="?page=whatsapp_log">
    <i data-lucide="cake" class="w-5 h-5"></i>
    Cumpleaños
</a>
```

### Paso 8 — Hostinger Cron

```
0 8 * * * php /home/u577647812/domains/padrinoscali.org/public_html/aratio/cron/birthday_check.php
```

---

## Flujo Completo

```
8:00 AM (cron)
    → cron/birthday_check.php
        → consulta cumpleañeros HOY con WhatsApp
        → para cada uno: selecciona template → envía WATI → registra log
        → fallos individuales no detienen el batch

Admin (cuando quiera)
    → pages/whatsapp_log.php
        → ve cumpleaños por semana/mes
        → ve historial de envíos
        → reintenta fallidos manualmente
        → exporta CSV
```
