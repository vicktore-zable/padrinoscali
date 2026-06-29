# PLAN FASE 1: Comunicaciones Inteligentes + Workflows

**Proyecto:** Aratio — Padrinos Cali
**Versión plan:** v1.0 (2026-06-29)
**Meta:** Implementar mensajería bidireccional, automatización de seguimiento y timeline unificado de actividades.

---

## Tabla de Contenidos

1. [Contexto y motivación](#1-contexto-y-motivación)
2. [Arquitectura general](#2-arquitectura-general)
3. [Módulo 1: WhatsApp Bidireccional (Cloud API)](#3-módulo-1-whatsapp-bidireccional-cloud-api)
4. [Módulo 2: Workflow Engine](#4-módulo-2-workflow-engine)
5. [Módulo 3: Timeline Unificado](#5-módulo-3-timeline-unificado)
6. [Cronograma](#6-cronograma)
7. [Costo](#7-costo)
8. [Prerrequisitos](#8-prerrequisitos)

---

## 1. Contexto y motivación

### ¿Qué es Solidarity Tech?

Solidarity Tech es la plataforma CRM de organización política usada por las campañas ganadoras de Zohran Mamdani (NYC) y Catherine Connolly (Irlanda). Su diferencial no es una característica aislada, sino el **embudo automatizado multicanal**: atención pública → registro → bienvenida → recordatorio → acción → seguimiento.

### ¿Qué nos falta?

| Capacidad | Solidarity Tech | Aratio | Impacto |
|-----------|:---------------:|:------:|:-------:|
| Mensajería bidireccional (WhatsApp/SMS) | ✅ | Solo cumpleaños | 🔴 Alto |
| Automatización multicanal (triggers) | ✅ | ❌ | 🔴 Alto |
| Timeline unificado de actividades | ✅ | ❌ | 🔴 Alto |
| Phone banking | ✅ | ❌ | 🔴 Medio |
| Email campaigns | ✅ | ❌ | 🟡 Medio |
| App móvil offline | ✅ | ❌ | 🟡 Futuro |

### Ventaja estratégica

Colombia funciona con **WhatsApp**, no SMS. Solidarity Tech no tiene integración nativa con WhatsApp. Aratio puede superarlos si logra una integración WhatsApp-first con automatización de seguimiento territorial.

---

## 2. Arquitectura general

```
┌───────────────────────────────────────────────────────────┐
│                    Aratio (PHP 8+)                         │
├───────────────────────────────────────────────────────────┤
│                                                             │
│  ┌─────────────────┐  ┌──────────────┐  ┌──────────────┐  │
│  │ WhatsApp Cloud   │  │ Workflow      │  │ Timeline      │  │
│  │ API (META $0)    │  │ Engine        │  │ Unificado     │  │
│  │                  │  │              │  │              │  │
│  │ • Enviar msgs    │  │ • 5 triggers  │  │ • 7 tipos     │  │
│  │ • Webhook inbox  │  │ • 5 acciones  │  │ • Pestaña     │  │
│  │ • Broadcast      │  │ • Cron 5min   │  │ • Filtros     │  │
│  │ • Plantillas     │  │ • Logging     │  │ • Paginación  │  │
│  └────────┬─────────┘  └──────┬───────┘  └──────┬───────┘  │
│           │                   │                  │          │
│           └───────────────────┴──────────────────┘          │
│                               │                              │
│              ┌────────────────┴────────────────┐            │
│              │      CRM Colaboradores (existe)  │            │
│              │      Eventos, Donaciones, etc.   │            │
│              └─────────────────────────────────┘            │
│                                                             │
└───────────────────────────────────────────────────────────┘
                          │
                          ▼
              ┌───────────────────────┐
              │  WhatsApp Cloud API   │
              │  api.meta.com         │
              │  (webhook callback)   │
              └───────────────────────┘
```

### Stack técnico

| Componente | Tecnología |
|------------|-----------|
| Backend | PHP 8+ (clases nativas, PDO, cURL) |
| Frontend | Alpine.js 3.x + Tailwind CSS |
| DB | MySQL 8 (Hostinger) |
| Mensajería | WhatsApp Cloud API (Meta, REST directo) |
| Automatización | Workflow Engine propio (Chain of Responsibility) |
| Cache | CacheManager existente (archivos planos) |
| Auth | Sistema existente (Auth.php + Portal Líder) |

---

## 3. Módulo 1: WhatsApp Bidireccional (Cloud API)

### 3.1 Tablas nuevas

#### `whatsapp_conversaciones`

```sql
CREATE TABLE whatsapp_conversaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    colaborador_id INT NOT NULL,
    wa_phone VARCHAR(20) NOT NULL COMMENT 'Número WhatsApp del colaborador',
    ultimo_mensaje TEXT,
    ultimo_tipo ENUM('enviado','recibido') DEFAULT NULL,
    ultimo_timestamp DATETIME DEFAULT NULL,
    unread INT DEFAULT 0,
    estado ENUM('activa','archivada','bloqueada') DEFAULT 'activa',
    metadata JSON DEFAULT NULL,
    creado_en DATETIME DEFAULT CURRENT_TIMESTAMP,
    actualizado_en DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (colaborador_id) REFERENCES colaboradores(id),
    UNIQUE KEY uk_colaborador (colaborador_id)
);
```

#### `whatsapp_mensajes`

```sql
CREATE TABLE whatsapp_mensajes (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    conversacion_id INT NOT NULL,
    wa_message_id VARCHAR(100) DEFAULT NULL COMMENT 'ID del mensaje en WhatsApp',
    direccion ENUM('enviado','recibido') NOT NULL,
    tipo ENUM('texto','imagen','template','interactivo') DEFAULT 'texto',
    contenido TEXT NOT NULL,
    metadata JSON DEFAULT NULL COMMENT 'Datos adicionales (template usado, botón presionado, etc.)',
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    leido BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (conversacion_id) REFERENCES whatsapp_conversaciones(id) ON DELETE CASCADE,
    INDEX idx_timestamp (timestamp),
    INDEX idx_colaborador_direccion (conversacion_id, direccion)
);
```

#### `whatsapp_plantillas`

```sql
CREATE TABLE whatsapp_plantillas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL COMMENT 'Nombre registrado en Meta',
    categoria ENUM('MARKETING','UTILITY','AUTHENTICATION') DEFAULT 'UTILITY',
    cuerpo TEXT NOT NULL COMMENT 'Template con {{variables}}',
    variables JSON DEFAULT NULL COMMENT 'Lista de nombres de variables',
    estado ENUM('pending','approved','rejected','paused') DEFAULT 'pending',
    meta_template_id VARCHAR(100) DEFAULT NULL,
    creado_en DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_nombre (nombre)
);
```

#### `whatsapp_broadcast_queue`

```sql
CREATE TABLE whatsapp_broadcast_queue (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    plantilla_id INT NOT NULL,
    colaborador_id INT NOT NULL,
    variables_json JSON DEFAULT NULL,
    estado ENUM('pending','sent','failed','cancelled') DEFAULT 'pending',
    error TEXT DEFAULT NULL,
    wa_message_id VARCHAR(100) DEFAULT NULL,
    programado_para DATETIME DEFAULT NULL,
    enviado_en DATETIME DEFAULT NULL,
    creado_en DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (plantilla_id) REFERENCES whatsapp_plantillas(id),
    FOREIGN KEY (colaborador_id) REFERENCES colaboradores(id),
    INDEX idx_estado_fecha (estado, programado_para),
    INDEX idx_programado (programado_para)
);
```

### 3.2 Archivos a crear

#### `includes/WhatsAppCloudApi.php` — Clase principal

```
Propósito:
  - Enviar mensajes de texto y templates vía WhatsApp Cloud API
  - Verificar webhook (handshake GET)
  - Procesar payload entrante (POST webhook)
  - Gestionar tokens de acceso (token exchange)
  - Formatear números Colombia (+57)

Métodos principales:
  - sendText(phone, text) → message_id
  - sendTemplate(phone, templateName, variables) → message_id
  - verifyWebhook(mode, token, challenge) → challenge | false
  - processWebhook(payload) → array of messages
  - refreshToken() → void (cuando expire)
  - formatPhone(number) → +57XXXXXXXXX

Constantes (desde root_config.php):
  - META_WHATSAPP_TOKEN (Bearer token de acceso)
  - META_WHATSAPP_PHONE_ID (ID del número telefónico)
  - META_WEBHOOK_VERIFY_TOKEN (token secreto de verificación)
  - META_API_VERSION (v22.0)
```

#### `api/whatsapp_webhook.php` — Endpoint webhook público

```
GET  /api/whatsapp_webhook.php?hub.mode=subscribe&hub.verify_token=TOKEN&hub.challenge=CHALLENGE
  → Verificación inicial (handshake Meta)
  → Return 200 con challenge si token coincide

POST /api/whatsapp_webhook.php
  → Recibe payload JSON de mensajes entrantes
  → Valida firma HMAC
  → Procesa cada mensaje:
      1. Busca o crea conversación (por wa_phone)
      2. Inserta mensaje en whatsapp_mensajes
      3. Actualiza ultimo_mensaje en conversación
      4. Si colaborador_id existe, registra en ActivityLogger
      5. Si es template reply, procesa respuesta
  → Return 200 (siempre, incluso si error)
```

#### `api/whatsapp_messages.php` — CRUD mensajería

```
GET    ?action=conversaciones
  → Lista conversaciones con último mensaje, unread, colaborador info
  → Filtros: estado, search (nombre/doc), page

GET    ?action=conversacion&id=ID&page=N
  → Mensajes de una conversación (paginación descendente)

POST   ?action=enviar
  Body: { conversacion_id, texto }
  → Envía mensaje vía Cloud API
  → Inserta en whatsapp_mensajes
  → Registra en ActivityLogger

POST   ?action=broadcast
  Body: { plantilla_id, colaborador_ids[], variables{}, programado_para? }
  → Crea registros en whatsapp_broadcast_queue
  → Si no programado, procesa inmediatamente

POST   ?action=archivar
  Body: { conversacion_id }
  → Cambia estado a 'archivada'

POST   ?action=marcar_leido
  Body: { conversacion_id }
  → Marca todos los mensajes como leídos
```

#### `pages/whatsapp_messages.php` — Panel Alpine.js

```
Secciones:
  1. INBOX — Lista de conversaciones
     - Avatar + nombre colaborador
     - Último mensaje (truncado)
     - Timestamp relativo ("hace 2h")
     - Badge no leídos
     - Indicador de tipo (texto/imagen/template)
     - Búsqueda por nombre/documento
     - Filtro: todas/activas/archivadas

  2. CHAT — Conversación individual
     - Burbujas de mensaje (enviado=verde, recibido=gris)
     - Timestamps
     - Input + botón enviar (Enter para enviar)
     - Scroll infinito hacia arriba
     - Header con nombre del colaborador + perfil rápido

  3. BROADCAST — Envío masivo
     - Selector de plantilla (dropdown + preview)
     - Editor de variables
     - Segmentación: por territorio, perfil, estado, campaña
     - Programar fecha/hora
     - Vista previa del mensaje renderizado
     - Resumen: "Se enviará a XXX colaboradores"

  4. PLANTILLAS — Gestión de templates
     - Lista con estado (aprobada/rechazada/pendiente)
     - Vista previa con variables resaltadas
     - Botón "Probar envío" a un colaborador
```

#### `cron/whatsapp_broadcast.php` — Procesador de cola

```
Ejecución: cada 1 minuto (vía cron Hostinger)
Lógica:
  1. SELECT * FROM whatsapp_broadcast_queue
     WHERE estado='pending' AND (programado_para IS NULL OR programado_para <= NOW())
     LIMIT 50
  2. Para cada registro:
     - Renderizar template con variables
     - Enviar vía WhatsAppCloudApi::sendTemplate()
     - Si éxito: estado='sent', guardar wa_message_id
     - Si falla: estado='failed', guardar error, 3 reintentos
  3. Log en workflow_log si aplica
```

### 3.3 Archivos a modificar

#### `root_config.php`

```php
// WhatsApp Cloud API (Meta) — Provider primario
define('WHATSAPP_PROVIDER', 'meta'); // 'meta' | 'wati'
define('META_API_VERSION', 'v22.0');
define('META_WHATSAPP_TOKEN', '');       // Pendiente configurar
define('META_WHATSAPP_PHONE_ID', '');    // Pendiente configurar
define('META_WEBHOOK_VERIFY_TOKEN', ''); // Pendiente configurar

// WATI (secundario, solo broadcast si superamos tier gratis)
define('WATI_API_URL', 'https://wati.live/api/v1/sendTemplateMessage');
define('WATI_API_KEY', '');  // Pendiente configurar
define('WATI_NUMBER', '');   // Pendiente configurar
```

#### `index.php`

- Agregar ruta: `'whatsapp_messages' => 'pages/whatsapp_messages.php'`
- Agregar enlace en menú lateral (icono: `message-circle` o `message-square` de Lucide)
- Verificar permiso: `canManageCampanas()` para secciones broadcast y plantillas

#### `pages/colaborador_detalle.php`

- Agregar pestaña "Chat" con iframe o inline de la conversación
- Mostrar última interacción y acceso directo al inbox
- Botón "Enviar WhatsApp" que abre modal rápido

#### `includes/WhatsAppApi.php` (existente)

- Agregar `sendViaCloudApi()` como método alternativo
- `send()` detecta `WHATSAPP_PROVIDER` y usa Cloud API o WATI según constante
- Mantener métodos de cumpleaños intactos

---

## 4. Módulo 2: Workflow Engine

### 4.1 Tablas nuevas

#### `workflow_reglas`

```sql
CREATE TABLE workflow_reglas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    trigger_evento VARCHAR(50) NOT NULL COMMENT 'colaborador.registrado, evento.proximo, etc.',
    condiciones_json JSON DEFAULT NULL COMMENT 'Condiciones adicionales (ej: {"dias_antes":1,"perfiles":["lider"]})',
    acciones_json JSON NOT NULL COMMENT 'Array de acciones a ejecutar (ej: [{"tipo":"whatsapp","plantilla":"bienvenida"}])',
    activo BOOLEAN DEFAULT TRUE,
    prioridad INT DEFAULT 0,
    ejecuciones_total INT DEFAULT 0,
    ejecuciones_exitosas INT DEFAULT 0,
    ejecuciones_fallidas INT DEFAULT 0,
    creado_por INT DEFAULT NULL,
    creado_en DATETIME DEFAULT CURRENT_TIMESTAMP,
    actualizado_en DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_trigger_activo (trigger_evento, activo)
);
```

#### `workflow_log`

```sql
CREATE TABLE workflow_log (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    regla_id INT NOT NULL,
    colaborador_id INT DEFAULT NULL,
    trigger_evento VARCHAR(50) NOT NULL,
    resultado ENUM('exitoso','fallido','pendiente') DEFAULT 'pendiente',
    acciones_ejecutadas INT DEFAULT 0,
    acciones_fallidas INT DEFAULT 0,
    error TEXT DEFAULT NULL,
    ejecutado_en DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_regla (regla_id),
    INDEX idx_colaborador (colaborador_id),
    INDEX idx_fecha (ejecutado_en),
    FOREIGN KEY (regla_id) REFERENCES workflow_reglas(id)
);
```

#### `workflow_acciones_pendientes`

```sql
CREATE TABLE workflow_acciones_pendientes (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    regla_id INT NOT NULL,
    workflow_log_id BIGINT DEFAULT NULL,
    colaborador_id INT DEFAULT NULL,
    datos_json JSON NOT NULL COMMENT 'Datos del trigger en el momento de ejecución',
    accion_tipo VARCHAR(50) NOT NULL COMMENT 'whatsapp, email, notificar, asignar_lider, cambiar_estado',
    accion_params JSON DEFAULT NULL,
    programado_para DATETIME DEFAULT NULL,
    estado ENUM('pending','processing','completed','failed','cancelled') DEFAULT 'pending',
    error TEXT DEFAULT NULL,
    procesado_en DATETIME DEFAULT NULL,
    reintentos INT DEFAULT 0,
    creado_en DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_programado (programado_para, estado),
    FOREIGN KEY (regla_id) REFERENCES workflow_reglas(id),
    FOREIGN KEY (colaborador_id) REFERENCES colaboradores(id)
);
```

### 4.2 Archivos a crear

#### `includes/WorkflowEngine.php` — Clase principal

```
Propósito:
  - Registrar reglas predefinidas en el arranque
  - Evaluar condiciones al recibir un trigger
  - Ejecutar acciones inmediatas o encolar para después
  - Logging de cada ejecución

Constantes de triggers:
  TRIGGER_COLABORADOR_REGISTRADO  = 'colaborador.registrado'
  TRIGGER_EVENTO_PROXIMO          = 'evento.proximo'
  TRIGGER_EVENTO_FINALIZADO       = 'evento.finalizado'
  TRIGGER_DONACION_RECIBIDA       = 'donacion.recibida'
  TRIGGER_COLABORADOR_INACTIVO    = 'colaborador.inactivo_30d'

Métodos:
  static trigger(evento, datos, colaborador_id=null) → void
    - Busca reglas activas para el trigger
    - Evalúa condiciones
    - Para cada acción:
        if inmediata → ejecutar()
        if programada → crear registro en workflow_acciones_pendientes
    - Inserta en workflow_log

  static ejecutarAccion(tipo, params, colaborador) → bool
    - whatsapp: llama a WhatsAppCloudApi::sendTemplate()
    - asignar_lider: actualiza colaborador.lider_directo
    - cambiar_estado: actualiza colaborador.estado
    - notificar_lider: envía notificación al líder directo

  static procesarPendientes() → int (cuántas procesó)
    - SELECT pendientes programadas
    - ejecutarAccion() para cada una
    - Marcar como completed/failed

  static init() → void
    - Inserta reglas predefinidas si no existen
    - Ejecuta al cargar WorkflowEngine.php

Reglas predefinidas (se insertan en init() si no existen):

  1. Bienvenida al registrarse
     trigger: colaborador.registrado
     condiciones: {}
     acciones:
       - whatsapp (plantilla: bienvenida, vars: [nombre])
       - asignar_lider (criterio: territorio_sin_asignar)

  2. Recordatorio de evento (24h antes)
     trigger: evento.proximo
     condiciones: {"dias_antes": 1}
     acciones:
       - whatsapp (plantilla: recordatorio_evento, vars: [nombre, evento, hora, lugar])

  3. Agradecimiento post-evento
     trigger: evento.finalizado
     condiciones: {}
     acciones:
       - whatsapp (plantilla: gracias_asistencia, vars: [nombre, evento])

  4. Agradecimiento donación
     trigger: donacion.recibida
     condiciones: {}
     acciones:
       - whatsapp (plantilla: gracias_donacion, vars: [nombre, monto])

  5. Re-enganche por inactividad (30 días sin actividad)
     trigger: colaborador.inactivo_30d
     condiciones: {"dias": 30}
     acciones:
       - whatsapp (plantilla: reactivacion, vars: [nombre])
       - cambiar_estado (estado: "Decrecio", motivo: "inactividad_30d")
```

#### `api/workflows.php` — Endpoints

```
GET    ?action=reglas
  → Lista reglas con estadísticas (ejecuciones totales, tasa éxito)

GET    ?action=regla&id=ID
  → Detalle de regla con log reciente

GET    ?action=log&regla_id=N&page=N
  → Log de ejecuciones de una regla (paginado)

GET    ?action=stats
  → Estadísticas globales: triggers más activos, tasa éxito, últimas 24h

POST   ?action=toggle&id=ID
  → Activar/desactivar una regla

POST   ?action=ejecutar_ahora&id=ID&colaborador_id=ID
  → Ejecución manual de una regla para un colaborador específico (debug)

GET    ?action=pendientes
  → Acciones pendientes de procesar (monitoreo)
```

#### `cron/workflow_processor.php` — Procesador de acciones pendientes

```
Ejecución: cada 5 minutos (vía cron Hostinger)
Lógica:
  1. $pendientes = WorkflowEngine::procesarPendientes()
  2. Log de cuántas procesó
  3. Si hay broadcast pendientes, delegar a cron/whatsapp_broadcast.php

Log:
  - Escribir en workflow_log el resultado del batch
```

#### `pages/workflows.php` — Panel de monitoreo

```
Secciones:
  1. REGLAS — Tabla de reglas (nombre, trigger, activo, ejecuciones, tasa éxito)
     - Toggle activar/desactivar
     - Botón "Probar" para ejecución manual

  2. LOG — Log de ejecuciones (filtro por regla, fecha, resultado)
     - Tabla con: fecha, regla, colaborador, resultado, acciones
     - Tooltip con detalle del error si falló

  3. PENDIENTES — Acciones en cola (fecha programada, tipo, estado)
     - Solo lectura (monitoreo)

  4. ESTADÍSTICAS — KPIs:
     - Total ejecuciones hoy/semana/mes
     - Tasa de éxito general
     - Regla más ejecutada
     - Acciones pendientes actuales
```

### 4.3 Archivos a modificar

#### `api/colaboradores.php`

En `handlePost()` (crear colaborador), al final:
```php
WorkflowEngine::trigger('colaborador.registrado', [
    'colaborador' => $colaborador,
    'campana_id' => $data['campana_id'] ?? null,
], $nuevoId);
```

#### `api/eventos.php`

En `handlePost()` (crear evento):
```php
// Programar recordatorio 24h antes
WorkflowEngine::trigger('evento.proximo', [
    'evento' => $evento,
    'fecha_evento' => $data['fecha'],
    'dias_antes' => 1,
], null);
```

En `handleAsistencia()` (marcar asistencia), cuando se completa el evento:
```php
WorkflowEngine::trigger('evento.finalizado', [
    'evento' => $evento,
    'asistentes' => $asistentes_ids,
], null);
```

#### `api/donaciones.php`

En `handlePost()` (crear donación):
```php
WorkflowEngine::trigger('donacion.recibida', [
    'donacion' => $donacion,
    'monto' => $data['monto'],
    'tipo' => $data['tipo'],
], $data['colaborador_id']);
```

#### `cron/birthday_check.php`

Refactorizar para usar WorkflowEngine en vez de lógica directa:
```php
// En lugar de enviar directo, disparar trigger
WorkflowEngine::trigger('colaborador.cumpleaños', [
    'colaborador' => $colaborador,
    'perfil' => $colaborador['perfil'],
], $colabId);
// WorkflowEngine ejecutará la acción según la regla configurada
```

---

## 5. Módulo 3: Timeline Unificado

### 5.1 Tabla nueva

#### `actividad_colaborador`

```sql
CREATE TABLE actividad_colaborador (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    colaborador_id INT NOT NULL,
    tipo VARCHAR(50) NOT NULL COMMENT 'Tipo de actividad: evento_asistio, donacion_hizo, etc.',
    descripcion VARCHAR(255) NOT NULL,
    metadata_json JSON DEFAULT NULL COMMENT 'Datos adicionales (id evento, monto donacion, etc.)',
    referencia_id INT DEFAULT NULL COMMENT 'ID del registro origen (opcional)',
    referencia_tabla VARCHAR(50) DEFAULT NULL COMMENT 'Tabla origen (opcional)',
    creado_por INT DEFAULT NULL COMMENT 'Usuario/admin que registró',
    creado_en DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (colaborador_id) REFERENCES colaboradores(id) ON DELETE CASCADE,
    INDEX idx_colaborador_tipo (colaborador_id, tipo),
    INDEX idx_colaborador_fecha (colaborador_id, creado_en DESC),
    INDEX idx_tipo_fecha (tipo, creado_en),
    INDEX idx_referencia (referencia_tabla, referencia_id)
);
```

**Tipos de actividad registrados automáticamente:**

| Tipo | Descripción | Metadata |
|------|-------------|----------|
| `registro` | Colaborador se registró en el sistema | `{campana_id, metodo_registro}` |
| `evento_asistio` | Asistió a un evento | `{evento_id, nombre_evento, fecha}` |
| `compromiso_creado` | Se creó un compromiso | `{compromiso_id, descripcion}` |
| `donacion_hizo` | Realizó una donación | `{donacion_id, monto, tipo}` |
| `whatsapp_enviado` | Se le envió un WhatsApp | `{plantilla, contenido_resumen}` |
| `whatsapp_recibido` | Respondió un WhatsApp | `{contenido_resumen}` |
| `estado_cambio` | Cambió su estado de evaluación | `{estado_anterior, estado_nuevo, motivo}` |
| `evaluacion` | Fue evaluado (dato_potencial/historico) | `{potencial_antes, potencial_despues, historico_antes, historico_despues}` |
| `lider_cambio` | Cambió de líder asignado | `{lider_anterior_id, lider_nuevo_id}` |
| `cumpleaños` | Se le envió felicitación | `{whatsapp_log_id}` |
| `telefonazo` | Se registró llamada telefónica | `{duracion, resultado, notas}` |
| `simpatizante_registro` | Registró un simpatizante | `{simpatizante_id, nombre}` |

### 5.2 Archivos a crear

#### `includes/ActivityLogger.php` — Clase estática

```
Propósito:
  - Registrar cualquier tipo de actividad con una línea de código
  - API unificada para todos los módulos

Métodos:
  static log(colaborador_id, tipo, descripcion, metadata=[], referencia_tabla=null, referencia_id=null, creado_por=null) → int
    - Inserta en actividad_colaborador
    - Return id insertado

  static getByColaborador(colaborador_id, tipos=[], page=1, perPage=20) → array
    - SELECT paginado, ordenado por fecha DESC
    - Filtro por tipos opcional

  static getTipos() → array
    - Lista de tipos disponibles (para filtros UI)

  static countByColaborador(colaborador_id, tipo=null) → int
    - Conteo total (para badges)
```

#### `api/timeline.php` — Endpoint

```
GET  ?action=list&colaborador_id=ID&tipos[]=X&page=1&perPage=20
  → Actividades paginadas de un colaborador
  → Orden: fecha DESC
  → Filtro por tipos (array opcional)
  → Response: { data: [...], pagination: { page, perPage, total, totalPages } }

GET  ?action=stats&colaborador_id=ID
  → Resumen de actividades por tipo (conteos)
  → Response: { total_actividades, por_tipo: { tipo: count } }
```

### 5.3 Archivos a modificar

#### `pages/colaborador_detalle.php`

Agregar pestaña "Actividad" (al lado de Información, Curriculum, Seguidores, etc.):

```
PESTAÑA ACTIVIDAD:
  - Timeline cronológico descendente (stream estilo feed)
  - Cada entrada muestra:
    - Icono según tipo (evento=🎫, donacion=💰, whatsapp=💬, etc.)
    - Descripción: "Asistió a 'Feria de Salud Comuna 14'"
    - Timestamp relativo: "hace 3 días"
    - Badge de tipo
  - Filtros por tipo de actividad (checkboxes)
  - Scroll infinito (carga más al hacer scroll)
  - Badge en el tab con total de actividades
```

#### Disparar `ActivityLogger::log()` en cada módulo:

```php
// api/eventos.php — al registrar asistencia
ActivityLogger::log(
    colaborador_id: $asistente['colaborador_id'],
    tipo: 'evento_asistio',
    descripcion: "Asistió a '{$evento['nombre']}'",
    metadata: ['evento_id' => $eventoId, 'nombre_evento' => $evento['nombre'], 'fecha' => $evento['fecha']],
    referencia_tabla: 'asistencia_eventos',
    referencia_id: $asistenciaId,
    creado_por: $userId
);

// api/compromisos.php — al crear
ActivityLogger::log(
    colaborador_id: $data['colaborador_id'],
    tipo: 'compromiso_creado',
    descripcion: "Compromiso: " . substr($data['descripcion'], 0, 100),
    metadata: ['compromiso_id' => $nuevoId, 'descripcion' => $data['descripcion']],
    referencia_tabla: 'compromisos',
    referencia_id: $nuevoId
);

// api/donaciones.php — al crear
ActivityLogger::log(
    colaborador_id: $data['colaborador_id'],
    tipo: 'donacion_hizo',
    descripcion: "Donación $" . number_format($data['monto']) . " ({$data['tipo']})",
    metadata: ['donacion_id' => $nuevoId, 'monto' => $data['monto'], 'tipo' => $data['tipo']],
    referencia_tabla: 'donaciones',
    referencia_id: $nuevoId
);

// api/whatsapp_messages.php — al enviar
ActivityLogger::log(
    colaborador_id: $colaboradorId,
    tipo: 'whatsapp_enviado',
    descripcion: "WhatsApp: " . substr($contenido, 0, 100),
    metadata: ['conversacion_id' => $convId, 'plantilla' => $plantilla ?? null],
    referencia_tabla: 'whatsapp_mensajes',
    referencia_id: $msgId
);

// api/colaboradores.php — al cambiar estado
ActivityLogger::log(
    colaborador_id: $id,
    tipo: 'estado_cambio',
    descripcion: "Estado: {$anterior} → {$nuevo}",
    metadata: ['estado_anterior' => $anterior, 'estado_nuevo' => $nuevo, 'motivo' => $motivo],
    referencia_tabla: 'historial_estados',
    referencia_id: $historialId
);
```

---

## 6. Cronograma

### Semana 1: WhatsApp Bidireccional (5-7 días)

| Día | Tarea | Archivos |
|:---:|-------|----------|
| 1 | Setup cuenta Facebook Business + WhatsApp Business API | — |
| 2 | `WhatsAppCloudApi.php` — clase base + sendText | includes/ |
| 3 | `whatsapp_webhook.php` — webhook + procesar mensajes | api/ |
| 4 | Migración SQL + `api/whatsapp_messages.php` | database/migrations/, api/ |
| 5 | `pages/whatsapp_messages.php` — inbox + chat | pages/ |
| 6 | Broadcast queue + `cron/whatsapp_broadcast.php` | cron/ |
| 7 | Integración en perfil colaborador + test | pages/colaborador_detalle.php |

### Semana 2: Workflow Engine (4-5 días)

| Día | Tarea | Archivos |
|:---:|-------|----------|
| 8 | Migración SQL + `WorkflowEngine.php` — base + reglas | database/, includes/ |
| 9 | `WorkflowEngine.php` — triggers + acciones completas | includes/ |
| 10 | Disparar triggers en módulos existentes | api/colaboradores, eventos, donaciones |
| 11 | `cron/workflow_processor.php` + `api/workflows.php` | cron/, api/ |
| 12 | `pages/workflows.php` — panel monitoreo | pages/ |

### Semana 3: Timeline + Integración (3-4 días)

| Día | Tarea | Archivos |
|:---:|-------|----------|
| 13 | Migración SQL + `ActivityLogger.php` | database/, includes/ |
| 14 | `api/timeline.php` + pestaña en detalle colaborador | api/, pages/ |
| 15 | Disparar ActivityLogger en todos los módulos existentes | api/eventos, compromisos, donaciones, etc. |
| 16 | Test integral + fix de edge cases | — |

**Total estimado: 16 días hábiles (~3 semanas)**

---

## 7. Costo

### Desarrollo (una sola vez)

| Recurso | Costo |
|---------|:-----:|
| Ingeniería (3 semanas) | Interno |
| **Total desarrollo** | **$0** |

### Operación mensual

| Concepto | Costo | Notas |
|----------|:-----:|-------|
| WhatsApp Cloud API (<1,000 convs/mes) | $0/mes | Primeras 1,000 conversaciones gratuitas |
| WhatsApp Cloud API (exceso) | ~$0.005/conv | Si superamos 1,000/mes → ~$5/adicional |
| Hostinger (ya pagado) | $0/mes | Incluye cron jobs, MySQL, PHP |
| Dominio (ya pagado) | $0/mes | — |
| WATI (respaldo, inactivo) | $0/mes | Solo si necesitamos broadcast masivo |
| **Total mensual** | **$0–5/mes** | Bien dentro del presupuesto de $10-30 |

---

## 8. Prerrequisitos

### Para empezar (Semana 1, Día 1)

- [ ] Crear **Cuenta Facebook Business** en https://business.facebook.com
- [ ] Crear **WhatsApp Business Account** dentro de Meta Business Suite
- [ ] Obtener o asignar **número telefónico** (puede ser uno nuevo)
- [ ] Generar **token de acceso permanente** (Settings > WhatsApp > Access Token)
- [ ] Obtener **Phone Number ID** (ID del número en WhatsApp Manager)
- [ ] Definir **Webhook Verify Token** (string secreto arbitrario)
- [ ] Configurar webhook en Meta para apuntar a `https://padrinoscali.org/aratio/api/whatsapp_webhook.php`
- [ ] Crear y aprobar **plantillas de mensaje** en Meta:
  - `bienvenida` — UTILITY: "Hola {{nombre}}, bienvenido a Padrinos Cali..."
  - `recordatorio_evento` — UTILITY: "Hola {{nombre}}, te recordamos el evento {{evento}}..."
  - `gracias_asistencia` — MARKETING: "Gracias {{nombre}} por asistir a {{evento}}..."
  - `gracias_donacion` — UTILITY: "Gracias {{nombre}} por tu donación de ${{monto}}..."
  - `reactivacion` — MARKETING: "Hola {{nombre}}, hacía tiempo que no sabíamos de ti..."

### Configuración en servidor (Día 1)

- [ ] Verificar que PHP tiene `curl` y `json` habilitados (ya debería)
- [ ] Configurar cron jobs en Hostinger:
  - `* * * * * php /home/u577647812/domains/padrinoscali.org/public_html/aratio/cron/whatsapp_broadcast.php`
  - `*/5 * * * * php /home/u577647812/domains/padrinoscali.org/public_html/aratio/cron/workflow_processor.php`
  - Mantener cron existente: `0 8 * * * php /home/u577647812/.../cron/birthday_check.php`

---

## Resumen de archivos

### Nuevos (13)

| Archivo | Líneas (est.) | Módulo |
|---------|:------------:|:------:|
| `includes/WhatsAppCloudApi.php` | ~200 | WhatsApp |
| `api/whatsapp_webhook.php` | ~120 | WhatsApp |
| `api/whatsapp_messages.php` | ~350 | WhatsApp |
| `pages/whatsapp_messages.php` | ~600 | WhatsApp |
| `database/migrations/20260701_whatsapp_messages.sql` | ~80 | WhatsApp |
| `cron/whatsapp_broadcast.php` | ~100 | WhatsApp |
| `includes/WorkflowEngine.php` | ~350 | Workflow |
| `api/workflows.php` | ~200 | Workflow |
| `pages/workflows.php` | ~400 | Workflow |
| `database/migrations/20260702_workflows.sql` | ~70 | Workflow |
| `cron/workflow_processor.php` | ~50 | Workflow |
| `includes/ActivityLogger.php` | ~100 | Timeline |
| `api/timeline.php` | ~80 | Timeline |
| **Total** | **~2,700** | |

### Modificados (12)

| Archivo | Cambio |
|---------|--------|
| `root_config.php` | Constantes Meta WhatsApp |
| `index.php` | Rutas + menú |
| `pages/colaborador_detalle.php` | Pestañas Chat + Actividad |
| `api/colaboradores.php` | Trigger registro + ActivityLogger |
| `api/eventos.php` | Trigger evento.proximo/finalizado + ActivityLogger |
| `api/donaciones.php` | Trigger donacion + ActivityLogger |
| `api/compromisos.php` | ActivityLogger |
| `api/whatsapp.php` | ActivityLogger + refactor provider |
| `includes/WhatsAppApi.php` | Refactor provider dual |
| `cron/birthday_check.php` | Delegar a WorkflowEngine |

---

*Fin del plan — v1.0 — 2026-06-29*
