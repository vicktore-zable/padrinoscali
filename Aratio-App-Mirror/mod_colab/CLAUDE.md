# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

**Sistema de Gestión Integral de Colaboradores** - A comprehensive PHP-based system for managing political collaborators with hierarchical leader-follower relationships, network visualization, and advanced security features.

## Tech Stack

- **Backend:** PHP 8.0+ (pure, no frameworks) with MVC architecture
- **Database:** MySQL 8.0+ with triggers, stored procedures, and views
- **Frontend:** Tailwind CSS 3.x + DaisyUI/Flowbite
- **JavaScript:** Alpine.js for reactivity
- **Charts:** ApexCharts for data visualization
- **Network Graphs:** Vis.js Network for hierarchical visualization
- **Tables:** DataTables with Tailwind styling
- **Forms:** Choices.js, Flatpickr, Cleave.js
- **Notifications:** Toastify.js, SweetAlert2

## Database Configuration

Configured via environment variables in `.env`.

```php
// Check .env for production/local values
DB_HOST=...
DB_NAME=...
DB_USER=...
DB_PASS=...
```

## arrancar servidor

cd colaboradores && php -S localhost:8000 -t public

## Key Architecture Patterns

### MVC Structure

```
src/
├── Controllers/  # Handle HTTP requests, call models, return views
├── Models/       # Database operations, business logic
├── Middleware/   # Authentication, authorization, CSRF, rate limiting
└── Utils/        # Helper functions, security, validation, logging
```

### Database Layer

**Class:** `Database` (config/database.php)

- Singleton pattern for single connection instance
- PDO with prepared statements (SQL injection prevention)
- Automatic audit logging via `setUserContext()`
- Transaction support
- Stored procedure wrappers

**Key Methods:**

```php
Database::getInstance()                    // Get singleton instance
->setUserContext($userId, $ip, $ua)       // Set audit context (REQUIRED before operations)
->query($sql, $params)                     // Execute prepared statement
->fetchAll($sql, $params)                  // Get all results
->fetchOne($sql, $params)                  // Get single result
->insert($table, $data)                    // Insert with auto-audit
->update($table, $data, $where, $params)   // Update with auto-audit
->delete($table, $where, $params)          // Delete with auto-audit
->beginTransaction() / ->commit() / ->rollback()
->getRedJerarquica($documento)             // Get hierarchical network
->cambiarLider($doc, $newLider, $motivo, $userId)
```

### Configuration System

**Files:**

- `config/config.php` - Main configuration with env() helper
- `config/constants.php` - Enums, colors, permissions
- `config/database.php` - Database singleton class
- `.env` - Environment variables (copy from .env.example)

**Key Constants:**

- `PERFILES` - Collaborator profiles (12 types)
- `NIVELES_PARTICIPACION` - Participation levels (5 types)
- `AREAS_INTERES` - Areas of interest (13 types)
- `PERMISOS` - Role-based permissions array
- `COLORES_PERFIL` / `COLORES_ESTADO` - Visualization colors

## Database Schema

### Core Tables

**colaboradores** - Main table with hierarchical relationships

- Calculated columns: `estado`, `grupo_etareo` (generated)
- Self-referencing FK: `lider_directo` → `documento`
- JSON field: `areas_interes`

**usuarios** - Authentication and access control

- Types: admin, lider, consulta
- Fields: password (bcrypt), intentos_fallidos, bloqueado_hasta, token_2fa

**curriculum** - Professional background (JSON fields)

**sesiones** - Active session management

**historial_cambios_lider** - Leader change audit trail

**importaciones_excel** - Import logs

**logs_auditoria** - Complete system audit log

### Triggers

All INSERT/UPDATE/DELETE operations on `colaboradores` and `usuarios` are automatically logged via triggers. The triggers use session variables set by `Database::setUserContext()`:

```php
// ALWAYS set context before database operations
$db = Database::getInstance();
$db->setUserContext($userId, $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT']);
// Now perform operations - triggers will log with this context
```

### Stored Procedures

```sql
CALL sp_obtener_red_jerarquica('1000000001')     -- Get full network
CALL sp_cambiar_lider('doc', 'newLeader', 'reason', userId)
CALL sp_estadisticas_generales()                  -- Get dashboard stats
CALL sp_limpiar_sesiones_expiradas()              -- Cleanup sessions
```

### Views

- `v_colaboradores_completo` - Collaborators with follower count
- `v_estadisticas_perfil` - Aggregate stats by profile
- `v_estadisticas_territorio` - Stats by territory
- `v_lideres_metricas` - Leaders with network metrics

## Security Implementation

### CRITICAL Security Rules

1. **Always use prepared statements** - Never concatenate user input into SQL
2. **Set audit context** - Call `setUserContext()` before any DB operation
3. **Validate all inputs** - Use Validator class
4. **Escape all outputs** - Use `e()` or `htmlspecialchars()`
5. **CSRF tokens** - Include in all forms
6. **Rate limiting** - Implement on login and API endpoints
7. **Password hashing** - Use `password_hash()` with bcrypt/Argon2id

### Authentication Flow

1. User submits login form with CSRF token
2. Validate credentials (check `intentos_fallidos`, `bloqueado_hasta`)
3. Verify password with `password_verify()`
4. If 2FA enabled, verify TOTP code
5. Create session record in `sesiones` table
6. Set session variables and regenerate session ID
7. Log successful login to `logs_auditoria`

### Self-Registration Flow (NEW - 2025-10-21)

Users can now register themselves from the login page without admin intervention.

**Features:**

- Accessible via "Regístrese aquí" link on login page
- Auto-assigns 'consulta' role (read-only) for security
- Optional association with existing colaborador via document number
- Strong password requirements (8+ chars, uppercase, number, special char)
- Rate limiting: 3 registration attempts per hour per IP
- Email and username uniqueness validation
- Terms and conditions acceptance required

**Flow:**

1. User clicks "Regístrese aquí" on `/login`
2. Fills registration form at `/register`
3. System validates:
   - Username uniqueness (4-50 chars, alphanumeric + underscore)
   - Email uniqueness
   - Password strength (uppercase, number, special char)
   - Optional: colaborador document exists
   - Terms acceptance
4. Creates user with tipo_usuario='consulta' and activo=1
5. Logs registration to audit trail
6. Redirects to login with success message

**Routes:**

- GET `/register` - Show registration form
- POST `/register` - Process registration (rate-limited)

**Files:**

- `src/Views/auth/register.php` - Registration form
- `src/Controllers/AuthController.php` - showRegister() and register() methods
- `routes/web.php` - Lines 42-46

### Password Recovery Flow (Enhanced - 2025-10-21)

**Features:**

- Accessible via "¿Olvidó su contraseña?" link on login page
- Secure token-based reset (1-hour expiration)
- Rate limiting: 3 attempts per 10 minutes
- Always shows success message (security: no user enumeration)
- Interactive password strength indicator

**Flow:**

1. User clicks "¿Olvidó su contraseña?" on `/login`
2. Enters email at `/forgot-password`
3. System generates secure token and stores in `password_reset_tokens`
4. Email sent with reset link (TODO: configure PHPMailer)
5. User clicks link: `/reset-password/{token}`
6. Enters new password with real-time validation
7. System verifies token, updates password, marks token as used
8. Redirects to login

**Database:**

- Table: `password_reset_tokens` (migration file created)
- Fields: id, usuario_id, token (unique 64 chars), expires_at, used, created_at
- Stored procedure: `sp_limpiar_tokens_expirados()` - cleanup job

**Routes:**

- GET `/forgot-password` - Request password reset
- POST `/forgot-password` - Send reset email (rate-limited)
- GET `/reset-password/{token}` - Show password reset form
- POST `/reset-password` - Process password change

**Files:**

- `src/Views/auth/forgot-password.php` - Email request form
- `src/Views/auth/reset-password.php` - Password reset form with Alpine.js validation
- `src/Controllers/AuthController.php` - All password recovery methods
- `src/Models/Usuario.php` - createPasswordResetToken(), verifyPasswordResetToken(), invalidatePasswordResetToken()
- `database/migrations/create_password_reset_tokens.sql` - Database migration

**Note:** Email sending requires PHPMailer configuration (currently TODO)

### Authorization

Check permissions using:

```php
has_permission($tipoUsuario, $modulo, $accion)
// Example: has_permission('lider', 'colaboradores', 'edit')
```

Permission structure defined in `PERMISOS` constant.

### Security Headers

Automatically set in `config.php` via `SECURITY_HEADERS` constant:

- X-Frame-Options: DENY
- X-Content-Type-Options: nosniff
- X-XSS-Protection: 1; mode=block
- Content-Security-Policy
- Referrer-Policy
- Permissions-Policy

## Data Flow Patterns

### Creating a Collaborator

```php
// 1. Validate input
$validator = new Validator($_POST);
$validator->required(['nombres', 'apellidos', 'documento'])
          ->email('email')
          ->date('fecha_nacimiento');

if (!$validator->passes()) {
    // Return errors
}

// 2. Set audit context
$db = Database::getInstance();
$db->setUserContext($_SESSION['user_id']);

// 3. Insert data
$colaboradorId = $db->insert('colaboradores', [
    'nombres' => $_POST['nombres'],
    'apellidos' => $_POST['apellidos'],
    'documento' => $_POST['documento'],
    'fecha_nacimiento' => $_POST['fecha_nacimiento'],
    'perfil' => $_POST['perfil'],
    'nivel_participacion' => $_POST['nivel_participacion'],
    'areas_interes' => json_encode($_POST['areas_interes']),
    'genero' => $_POST['genero'],
    'lider_directo' => $_POST['lider_directo'] ?? null
]);

// estado and grupo_etareo are calculated automatically by DB
```

### Changing a Leader

```php
$db = Database::getInstance();
$db->setUserContext($_SESSION['user_id']);

$success = $db->cambiarLider(
    $documentoColaborador,
    $documentoNuevoLider,
    $motivo,
    $_SESSION['user_id']
);

// This calls sp_cambiar_lider which:
// 1. Updates lider_directo
// 2. Logs to historial_cambios_lider
// 3. Triggers audit log
```

## Frontend Architecture

### View Rendering

Views are located in `views/` with this structure:

```
views/
├── layouts/       # Main page templates (admin.php, lider.php, landing.php)
├── components/    # Reusable UI components (header, sidebar, modals)
└── pages/         # Page content for each module
```

**Rendering pattern:**

```php
// In controller
include view_path('layouts/admin.php');

// In layout file
include view_path('components/header.php');
include view_path('pages/colaboradores/index.php');
include view_path('components/footer.php');
```

### Alpine.js Patterns

Use Alpine.js for reactive UI components:

```html
<!-- Dropdown example -->
<div x-data="{ open: false }">
    <button @click="open = !open">Menu</button>
    <div x-show="open" @click.away="open = false">
        <!-- menu items -->
    </div>
</div>

<!-- Form validation example -->
<div x-data="{
    form: { nombre: '', email: '' },
    errors: {},
    submit() {
        // AJAX submit with fetch
    }
}">
    <input x-model="form.nombre" type="text">
    <span x-show="errors.nombre" x-text="errors.nombre"></span>
</div>
```

### Network Visualization (Vis.js)

Configuration in `public/assets/js/modules/grafo.js`:

```javascript
const options = {
    nodes: {
        shape: 'dot',  // Changes based on nivel_participacion
        size: 25,      // Scales with follower count
        color: {
            background: '#3b82f6',  // Based on perfil
            border: '#1e40af'
        }
    },
    edges: {
        arrows: 'to',   // seguidor → líder
        color: '#10b981', // Based on estado
        width: 2        // Based on dato_potencial
    },
    layout: {
        hierarchical: {
            direction: 'UD',  // Up-Down
            sortMethod: 'directed'
        }
    },
    physics: {
        enabled: true,
        hierarchicalRepulsion: {
            nodeDistance: 150
        }
    }
};
```

## Common Development Tasks

### Adding a New Controller

1. Create `src/Controllers/ExampleController.php`
2. Extend base controller (if exists) or implement directly
3. Add route in `public/index.php` router
4. Create corresponding view in `views/pages/example/`

### Adding a New Model

1. Create `src/Models/Example.php`
2. Add table name and primary key properties
3. Implement CRUD methods using `Database` singleton
4. Add validation rules
5. Include audit logging via `setUserContext()`

### Adding a New Middleware

1. Create `src/Middleware/ExampleMiddleware.php`
2. Implement `handle()` method
3. Register in router pipeline
4. Apply to routes that need it

### Creating an Excel Import

1. Use PHPSpreadsheet library (install via Composer)
2. Validate file type and size in `UPLOAD_CONFIG`
3. Parse rows and validate each
4. Use transaction for bulk insert
5. Log to `importaciones_excel` table
6. Return success/error report

## API Endpoints

RESTful API in `api/v1/`:

```
GET    /api/v1/colaboradores              List all
GET    /api/v1/colaboradores/{documento}  Get one
POST   /api/v1/colaboradores              Create
PUT    /api/v1/colaboradores/{documento}  Update
DELETE /api/v1/colaboradores/{documento}  Delete

GET    /api/v1/red/grafo/{documento}      Get network graph data
GET    /api/v1/red/analisis/{documento}   Get network metrics
POST   /api/v1/red/expandir                Load additional nodes

GET    /api/v1/estadisticas                Dashboard stats
```

**Authentication:** Session-based or token-based (to be implemented)
**Rate Limiting:** Defined in `RATE_LIMIT_CONFIG`
**Response Format:** JSON with structure:

```json
{
    "success": true|false,
    "data": {...},
    "message": "Success message",
    "errors": []
}
```

## Testing Database Setup

```bash
# Import schema
mysql -u root -p < database/schema.sql

# Import triggers
mysql -u root -p aratio < database/triggers.sql

# Import test data (106 collaborators + 7 users)
mysql -u root -p aratio < database/seeds.sql
```

**Test Users:**

- admin / Admin123! (type: admin)
- mgarcia / Admin123! (type: lider, documento: 1000000002)
- consulta / Admin123! (type: consulta)

## Running the Application

### Development Server

```bash
# Start PHP built-in server
cd public
php -S localhost:8000

# Or with specific host
php -S 0.0.0.0:8000
```

### Environment Setup

```bash
# 1. Copy environment file
cp .env.example .env

# 2. Configure database credentials in .env

# 3. Generate security key
# Use: bin2hex(random_bytes(32))

# 4. Install Composer dependencies
composer install

# 5. Install NPM dependencies (for Tailwind)
npm install

# 6. Build Tailwind CSS
npm run build
```

## Development Guidelines

### Code Style

- **PSR-12** compliant PHP code
- **Meaningful variable names** - no single letters except loop counters
- **PHPDoc comments** for all public methods
- **Consistent indentation** - 4 spaces
- **Namespace pattern:** `App\Controllers`, `App\Models`, etc.

### Database Operations

**Always:**

```php
$db = Database::getInstance();
$db->setUserContext($userId);  // BEFORE any operation
```

**Never:**

```php
$sql = "SELECT * FROM usuarios WHERE id = " . $_GET['id'];  // SQL INJECTION!
```

### Error Handling

```php
try {
    $db->beginTransaction();

    // operations

    $db->commit();
    return ['success' => true];
} catch (Exception $e) {
    $db->rollback();
    Logger::error($e->getMessage());
    return ['success' => false, 'message' => MENSAJES['error']['generic']];
}
```

### Input Validation

```php
$validator = new Validator($data);
$validator->required(['field1', 'field2'])
          ->email('email')
          ->min('password', 8)
          ->custom('documento', function($value) {
              return validate_pattern('documento_cc', $value);
          });

if ($validator->fails()) {
    return $validator->errors();
}
```

## Performance Considerations

- Use **prepared statements** - they're cached by MySQL
- Leverage **views** for complex queries used multiple times
- Cache frequently accessed data in `storage/cache/`
- Use **pagination** - default 25 items per page
- For large networks (>500 nodes), implement lazy loading
- Index all foreign keys and frequently queried columns

## Troubleshooting

### "Cannot connect to database"

- Check MySQL service is running
- Verify credentials in `.env`
- Test connection: `mysql -u root -p aratio`

### "CSRF token mismatch"

- Session may have expired
- Check session configuration in php.ini
- Verify session cookie settings

### "Trigger error" during insert/update

- Ensure validation passes before DB operation
- Check trigger logic in `database/triggers.sql`
- Verify all required fields are provided

### "Permission denied"

- Check `PERMISOS` constant for role permissions
- Verify user type in session
- Ensure middleware is applied to route

## Important Files Reference

- `config/config.php` - Main configuration, autoloader, global helpers
- `config/database.php` - Database singleton with all DB operations
- `config/constants.php` - All enums, colors, permissions
- `database/schema.sql` - Complete database structure
- `database/triggers.sql` - Audit and validation triggers
- `database/seeds.sql` - 100+ test data records
- `public/index.php` - Router and entry point
- `.env.example` - Environment variables template

## Multi-Level Network Graph Visualization

### Overview

The system implements an interactive hierarchical network graph using **Vis.js Network 9.1.9** that visualizes up to 3 levels of follower relationships. The graph displays on the colaborador detail page under the "Red de Seguidores" tab.

### Key Features

**Visual Hierarchy:**

- **Level 0 (Root)**: Blue square - the selected colaborador
- **Level 1**: Green ellipses - direct followers
- **Level 2**: Orange ellipses - second-level followers
- **Level 3**: Purple ellipses - third-level followers
- **Leaf nodes**: Gray dots - colaboradores with no followers

**Node Information:**

- Two-line labels: Name on first line, document number on second
- Hover tooltips show: Level, Document, Profile, Follower count
- Node size scales with number of followers (larger = more followers)
- Click any node to navigate to that colaborador's detail page

**Statistics Dashboard:**

- Seguidores Directos: Count of level 1 followers
- Red Completa: Total network size across all levels
- Niveles de Profundidad: Maximum hierarchy depth
- Factor de Crecimiento: Network multiplication factor

### Implementation Architecture

**File:** `src/Views/colaboradores/show.php`

**Lazy Initialization Pattern:**

```javascript
// Graph only initializes when user clicks the "Red de Seguidores" tab
// Uses Alpine.js custom event dispatching
<div x-data="{ tab: 'general', graphInitialized: false }"
     @tab-changed.window="if ($event.detail === 'seguidores' && !graphInitialized) {
         setTimeout(() => { initializeNetwork(); graphInitialized = true; }, 100);
     }">
```

**Why Lazy Loading?**

- Vis.js cannot render in hidden containers (Alpine.js `x-show`)
- Prevents initialization errors and improves performance
- Ensures library loads before graph creation

**Library Loading with Retry:**

```javascript
// CDN: jsDelivr (more reliable than unpkg)
// Retry up to 10 times with 500ms intervals
if (typeof vis === 'undefined') {
    initRetries++;
    if (initRetries >= MAX_RETRIES) {
        alert('Error: No se pudo cargar la librería...');
        return;
    }
    setTimeout(initializeNetwork, 500);
    return;
}
```

**Recursive Data Structure:**

```php
// Controller loads 3 levels deep
foreach ($seguidores as &$seguidor) {
    $subSeguidores = $this->colaboradorModel->getSeguidoresDirectos($seguidor['id']);
    $seguidor['subseguidores'] = $subSeguidores;

    foreach ($seguidor['subseguidores'] as &$subSeguidor) {
        $subSubSeguidores = $this->colaboradorModel->getSeguidoresDirectos($subSeguidor['id']);
        $subSeguidor['subseguidores'] = $subSubSeguidores;
    }
}
```

**Graph Configuration:**

```javascript
const options = {
    nodes: {
        shadow: true,
        font: { multi: 'html', align: 'center' },
        margin: { top: 10, bottom: 10, left: 15, right: 15 }
    },
    layout: {
        hierarchical: {
            direction: 'UD',           // Up-Down
            sortMethod: 'directed',
            nodeSpacing: 180,          // Horizontal spacing
            levelSeparation: 250,      // Vertical spacing
            treeSpacing: 220
        }
    },
    physics: { enabled: false },       // Stable positioning
    interaction: {
        hover: true,
        navigationButtons: true,
        keyboard: true
    }
};
```

### Controller Methods

**File:** `src/Controllers/ColaboradorController.php`

**show() method** - Lines 96-163:

- Loads colaborador data
- Recursively loads 3 levels of followers
- Calculates hierarchy depth
- Passes data to view

**calcularNivelesJerarquia()** - Private method:

```php
private function calcularNivelesJerarquia(array $seguidores): int {
    if (empty($seguidores)) return 0;
    $maxNivel = 1;
    foreach ($seguidores as $seguidor) {
        if (!empty($seguidor['subseguidores'])) {
            $nivelSub = 1 + $this->calcularNivelesJerarquia($seguidor['subseguidores']);
            $maxNivel = max($maxNivel, $nivelSub);
        }
    }
    return $maxNivel;
}
```

### Troubleshooting Network Graph

**Graph not displaying:**

1. Check browser console (F12) for JavaScript errors
2. Verify Vis.js loaded: Look for "Vis.js loaded successfully! Version: OK"
3. Check Network tab: `vis-network.min.js` should return 200
4. Ensure internet connection (CDN dependency)
5. Try hard refresh: Ctrl+Shift+R

**Library loading errors:**

- Console shows retry attempts: "Retrying in 500ms... (X/10)"
- If reaches 10 attempts, alert appears with error message
- Check internet connection or try different CDN

**Empty graph container:**

- Verify colaborador has followers (check `$totalSeguidores`)
- Check console for "Container #network-graph not found!" error
- Ensure you clicked the "Red de Seguidores" tab

**Performance issues (>100 nodes):**

- Consider implementing pagination or lazy loading
- Reduce depth from 3 to 2 levels
- Enable physics for better distribution (may be slower)

### CDN Dependencies

```html
<!-- Vis.js Network 9.1.9 -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/vis-network@9.1.9/styles/vis-network.min.css" />
<script src="https://cdn.jsdelivr.net/npm/vis-network@9.1.9/standalone/umd/vis-network.min.js"></script>
```

**Why jsDelivr instead of unpkg?**

- More reliable uptime
- Better performance globally
- Automatic minification
- SRI hash support

### Future Enhancements

- [ ] Export graph as PNG/SVG image
- [ ] Filter nodes by profile or status
- [ ] Dynamic level expansion (load on demand)
- [ ] Timeline view showing network evolution
- [ ] Network metrics: centrality, density, clustering
- [ ] Compare two networks side-by-side
- [ ] Search and highlight nodes in graph
- [ ] Custom color schemes per profile type

## Recent Bug Fixes (2025-10-15)

### Critical Issues Resolved

1. **Database Schema Mismatches**
   
   - Fixed `seguidores_directos` → `total_seguidores_directos` in `v_lideres_metricas` view queries
   - Fixed `colaborador_id` → `colaborador_documento` in `historial_cambios_lider` joins
   - Fixed `celular` → `telefono` field reference in views

2. **Role/Permission System**
   
   - Changed all references from `rol` to `tipo_usuario` (correct DB field)
   - Updated role values from `'Administrador'` to `'admin'` in middleware
   - Fixed `has_permission()` to accept 3 parameters: `($tipo_usuario, $modulo, $accion)`
   - Updated Controller::hasPermission() to parse permission strings properly

3. **Network Graph Visualization** (2025-10-15)
   
   - Fixed Vis.js library not loading (CDN URL issue)
   - Implemented lazy initialization on tab click (Alpine.js event)
   - Added retry mechanism for library loading (up to 10 attempts)
   - Enabled multi-line labels (name + document)
   - Improved spacing and margins for better readability
   - Added comprehensive console logging for debugging

4. **Function Redeclaration Errors**
   
   - Removed duplicate `formatFullName()` from Helpers.php (kept first version)
   - Removed duplicate `pagination()` from Helpers.php (kept improved version)
   - Converted global `isActive()` function to anonymous function in sidebar.php

5. **PHP 8.2 Compatibility**
   
   - Added null coalescing operators (`?? ''`) to all `htmlspecialchars()` calls
   - Fixed deprecated warnings for null values in string parameters

6. **Routing Issues**
   
   - Reordered routes so specific paths (like `/create`, `/search`) come before generic `/{id}` patterns
   - Prevents 404 errors on create/import/export routes

7. **Missing Controllers**
   
   - Created stub implementations for: ReportController, CurriculumController, UsuarioController, ProfileController, LogController, SettingsController, ApiController
   - Created corresponding view stubs to prevent 500 errors

### Known Working State

- ✅ Login system functional
- ✅ Dashboard displays with stats and top leaders
- ✅ Colaboradores list page works
- ✅ Navigation between modules works
- ✅ Sidebar permissions work correctly
- ✅ No critical errors preventing system use

### OPcache Considerations

When making code changes, PHP's OPcache may prevent updates from being visible. If changes don't appear:

```bash
# Kill all PHP processes
taskkill /F /IM php.exe

# Restart server
cd public
php -S localhost:8000
```

## Next Steps for Development

Following the specification in `PROMPT_SISTEMA_GESTION_COLABORADORES.md`, the priority order is:

1. ✅ Database structure (completed)
2. ✅ Configuration files (completed)
3. ✅ Models (completed - Colaborador, Usuario, Curriculum)
4. ✅ Utilities (completed - Security, Validator, Logger, Helpers)
5. ✅ Authentication system (completed)
6. ✅ Router and base controllers (completed)
7. ✅ Landing page (completed)
8. ✅ Admin dashboard (completed)
9. ✅ Collaborator CRUD (completed - list/view/edit/create)
10. ✅ Network visualization (completed - Vis.js graph in colaborador detail)
11. ✅ Curriculum management (completed - view/edit with modal forms)
12. 📋 Leader dashboard
13. 📋 Reports and exports (routes exist, need implementation)
14. 📋 User management (admin only)
15. 📋 Excel import/export

## Curriculum Management System (2025-10-20/21)

### Overview

Complete implementation of curriculum vitae management for collaborators with professional summary, work experience, academic education, and enhanced political/social participation tracking.

### Enhanced Features

**11-Field Social Participation System:**

- tipo, cargo, organizacion, ambito, poblacion_beneficiada
- fecha_inicio, fecha_fin, logros, redes_alianzas, descripcion, actual

**Auto-creation Pattern:**

- Curriculum automatically created when accessing /curriculum/{id} or /curriculum/{id}/edit if it doesnt exist

**Interactive Modals with Alpine.js:**

- Add/edit work experience, academic education, political participation
- Real-time form validation and reactive updates

### Critical Fixes Applied

**1. Alpine.js Function Scope (2025-10-20)**

- ISSUE: curriculumManager is not defined errors
- ROOT CAUSE: Function defined AFTER Alpine.js loaded or INSIDE x-data div
- FIX: Moved function definition to <head> BEFORE Alpine.js script tag
- FILE: src/Views/curriculum/edit_v2.php

**2. CSRF Token in AJAX Headers (2025-10-21)**

- ISSUE: 419 errors on POST /curriculum/{id}/participacion
- ROOT CAUSE: Security::checkCsrf() only checked $_POST, not HTTP headers
- FIX: Added $_SERVER[HTTP_X_CSRF_TOKEN] and getallheaders()[X-CSRF-Token] fallbacks
- FILE: src/Utils/Security.php

**3. JSON Body Parsing (2025-10-21)**

- ISSUE: Empty data in controller when sending application/json
- ROOT CAUSE: Controller::post() only returned $_POST (empty for JSON requests)
- FIX: Parse php://input when Content-Type is application/json
- FILE: src/Core/Controller.php

**4. Missing jsonResponse Method (2025-10-21)**

- ISSUE: Call to undefined method CurriculumController::jsonResponse()
- ROOT CAUSE: Method existed in Helpers but not wrapped in Controller base
- FIX: Added protected jsonResponse() wrapper method
- FILE: src/Core/Controller.php

**5. Date Formatting PHP 8.2 Deprecation (2025-10-20)**

- ISSUE: strtotime(null) deprecated, showing 12/1969 for null dates
- FIX: Created formatDate() helper with null checking
- FILE: src/Views/curriculum/show.php

**6. Content Security Policy Blocking (2025-10-21)**

- ISSUE: CSP blocked fonts and source maps
- FIX: Added font-src and connect-src directives
- FILE: config/config.php

**7. app-bundle.js Module Error (2025-10-21)**

- ISSUE: Cannot use import statement outside a module
- ROOT CAUSE: ES6 imports in unbundled file
- FIX: Commented out app-bundle.js, using Alpine.js from CDN instead
- FILES: src/Views/layouts/default.php, src/Views/curriculum/edit_v2.php

### Database Migration

Added 6 TEXT fields to curriculum table:

- resumen_profesional, habilidades, idiomas
- reconocimientos, referencias, observaciones

Script: migrate_add_text_fields.php

### Testing Curriculum

1. Login: http://localhost:8000/login (admin / Admin123!)
2. View curriculum: http://localhost:8000/curriculum/1
3. Edit curriculum: http://localhost:8000/curriculum/1/edit
4. Add entries using modal buttons
5. Save general info using main form

All AJAX operations validated with CSRF tokens in X-CSRF-Token header.

## User Management System - Complete and Functional

See USER_MANAGEMENT_GUIDE.md for comprehensive usage guide.

Sistema completo de gestión de usuarios con RBAC, 2FA, auditoría y control de sesiones.
Controller, routes y views ya implementados. Solo admin puede acceder.

Access: http://localhost:8000/usuarios (login as admin/Admin123!)

## Development Environment Setup (2025-10-29)

### XAMPP Configuration

**Installation Path:** `F:/xampp2/`

**MySQL Data Directory:** `F:/xampp2/mysql/data/`
**MySQL Configuration:** `F:/xampp2/mysql/bin/my.ini` (main config)

**Important:** Ensure `my.ini` files have correct paths:

- `F:/xampp2/mysql/bin/my.ini` - Main configuration (correct)
- `F:/xampp2/mysql/data/my.ini` - Must have `datadir=F:/xampp2/mysql/data` (not c:/xampp)

### Starting the Development Server

```bash
# From project root
cd colaboradores && php -S localhost:8000 -t public

# Or from colaboradores directory
php -S localhost:8000 -t public
```

Access the application at: http://localhost:8000

### MySQL Troubleshooting

#### Common Issue: MySQL Won't Start

**Symptoms:**

- XAMPP shows "MySQL shutdown unexpectedly"
- Error log shows: "Aria recovery failed" or "Could not open mysql.plugin table"

**Solution Steps:**

1. **Check Aria Log Files**
   
   ```bash
   cd F:/xampp2/mysql/data
   # Backup and remove corrupted logs
   mv aria_log.00000001 aria_log.00000001.bak
   mv aria_log_control aria_log_control.bak
   ```

2. **Restore System Database from Backup**
   
   ```bash
   cd F:/xampp2/mysql/data
   mv mysql mysql_corrupted
   cp -r ../backup/mysql .
   cp ../backup/aria_log.00000001 .
   cp ../backup/aria_log_control .
   ```

3. **Verify Configuration Paths**
   Check `F:/xampp2/mysql/data/my.ini` has correct datadir:
   
   ```ini
   [mysqld]
   datadir=F:/xampp2/mysql/data
   ```

4. **Restart MySQL** from XAMPP Control Panel

#### Checking MySQL Status

```bash
# Check if MySQL is running
tasklist | findstr mysqld

# Check port 3306
netstat -ano | findstr 3306

# View error log
tail -50 F:/xampp2/mysql/data/mysql_error.log

# Test connection
F:/xampp2/mysql/bin/mysql.exe -u root -e "SELECT VERSION();"
```

#### Database Files Location

- **Application Database:** `F:/xampp2/mysql/data/aratio/`
- **System Database:** `F:/xampp2/mysql/data/mysql/`
- **Backup:** `F:/xampp2/mysql/backup/`
- **Error Log:** `F:/xampp2/mysql/data/mysql_error.log`

### GitHub Repository Configuration

**Repository:** https://github.com/vicktore/colaboradores-aratio
**Branch:** main

**Setup Commands:**

```bash
cd "H:/Mi unidad/2025/5d/app"

# Add remote
git remote add origin https://github.com/vicktore/colaboradores-aratio.git

# Verify
git remote -v

# Push (requires GitHub Personal Access Token)
git push -u origin main
```

**Authentication:**

- Username: `vicktore`
- Password: Use GitHub Personal Access Token (not GitHub password)
- Create token at: https://github.com/settings/tokens
- Required scope: `repo` (full repository access)

**Files Ignored by Git:**

- `.env` (contains sensitive config)
- `cache/*` (temporary rate limit files)
- `.claude/settings.local.json` (local Claude Code settings)
- `storage/logs/*` (log files)
- `vendor/` (Composer dependencies)
- `node_modules/` (npm dependencies)

### Common Development Commands

```bash
# Start PHP server
cd colaboradores && php -S localhost:8000 -t public

# Check git status
git status

# Stage changes
git add .

# Commit
git commit -m "Description of changes"

# Push to GitHub (requires token)
git push origin main

# Check MySQL connection
mysql -u root -e "SHOW DATABASES;"

# Import database schema
mysql -u root aratio < database/schema.sql

# Import seed data
mysql -u root aratio < database/seeds.sql
```

### Troubleshooting PHP Server

**Issue: Maximum execution time exceeded**

- Cause: MySQL connection timeout
- Solution: Ensure MySQL is running before starting PHP server

**Issue: Error de conexión a la base de datos**

- Check MySQL is running in XAMPP
- Verify `.env` configuration matches database credentials
- Test MySQL connection directly

**Issue: Changes not visible after edit**

- OPcache may be caching old code
- Solution: Kill PHP process and restart server
  
  ```bash
  taskkill /F /IM php.exe
  cd public && php -S localhost:8000
  ```

### Project File Locations

**Main Project:** `H:/Mi unidad/2025/5d/app/colaboradores/`
**Git Repository:** `H:/Mi unidad/2025/5d/app/` (contains multiple projects)
**MySQL Data:** `F:/xampp2/mysql/data/aratio/`
**Server Access:** http://localhost:8000

**Test Credentials:**

- Admin: `admin` / `Admin123!`
- Leader: `mgarcia` / `Admin123!`
- Consulta: `consulta` / `Admin123!`

## Bug Fixes and Improvements (2025-11-15)

### Critical Issues Resolved

1. **Logger Namespace Error in Usuario.php**
   
   - **Issue:** `Class "App\Models\Logger" not found` fatal error
   - **Root Cause:** Missing `use App\Utils\Logger;` import statement
   - **Fix:** Added proper namespace import at line 13 of src/Models/Usuario.php
   - **Impact:** All session creation and verification operations now work correctly

2. **Two-Factor Authentication Field Names**
   
   - **Issue:** `Undefined array key "two_factor_enabled"` warning in AuthController.php:101
   - **Root Cause:** Code used incorrect field names from outdated schema
   - **Fix:** Updated field references:
     - `$user['two_factor_enabled']` → `!empty($user['require_2fa'])`
     - `$user['two_factor_secret']` → `$user['token_2fa']`
   - **Files Modified:** src/Controllers/AuthController.php (lines 101, 112)
   - **Impact:** Login process now works without warnings or errors

3. **Project Cleanup**
   
   - **Removed 27 temporary/debug files:**
     - Debug scripts: clear_rate_limit.php, debug-login.php, fix-pwd-standalone.php, test-simple.php
     - Temporary SQL: fix_*.sql, add_curriculum_text_fields.sql
     - Test files: public/test-*.php, public/fix_encoding*.php
     - Deployment artifacts: mod_colab_deploy.tar.gz, INSTRUCCIONES_DEPLOYMENT.txt
     - Backup files: database/production_dump.sql, .env.fixed, server.log
   - **Removed directories:** deploy_temp/ (with all contents)
   - **Impact:** Cleaner repository, ready for production deployment

### System Status After Fixes

- ✅ **Login System:** Fully functional with correct field mappings
- ✅ **Session Management:** Working correctly with proper Logger integration  
- ✅ **2FA Support:** Ready to use when enabled (require_2fa field)
- ✅ **Codebase:** Clean, no temporary or debug files
- ✅ **Local Development:** Server running on http://localhost:8000
- ✅ **Remote Server:** Cleaned and ready for fresh deployment

### Testing Performed

1. **Local Login Test:**
   
   - Username: admin
   - Password: Admin123!
   - Result: ✅ Successful login without errors

2. **Session Creation:**
   
   - Verified session tokens are created correctly
   - Logger messages appear in logs without errors
   - Session data stored properly in PHP $_SESSION

3. **Database Integration:**
   
   - Confirmed usuarios table structure matches code expectations
   - Fields: require_2fa (tinyint), token_2fa (varchar)
   - All CRUD operations working correctly

### Next Steps for Production Deployment

When ready to deploy to production (colaboradores.aratio.mrmtech.net):

1. **Database Setup:**
   
   ```bash
   # Import schema to remote database
   mysql -h auth-db690.hstgr.io -u u156469157_aratio -p15zxCeBbvgsR u156469157_aratio < database/schema.sql
   
   # Import seed data (optional)
   mysql -h auth-db690.hstgr.io -u u156469157_aratio -p15zxCeBbvgsR u156469157_aratio < database/seeds.sql
   ```

2. **Code Deployment:**
   
   ```bash
   # Option 1: Using FTP script
   bash deploy_ftp.sh
   
   # Option 2: Manual upload via FTP client to /mod_colab/
   ```

3. **Configuration:**
   
   - Upload .env.production as .env
   - Verify file permissions (644 for files, 755 for directories)
   - Test URL: https://colaboradores.aratio.mrmtech.net

4. **Verification:**
   
   - Test login with admin credentials
   - Verify dashboard loads correctly
   - Check all modules are accessible
   - Review error logs for any issues

### Files Modified in This Session

- ✅ src/Models/Usuario.php (added Logger import)
- ✅ src/Controllers/AuthController.php (fixed 2FA field names)
- ✅ Deleted 27+ temporary files and directories
- ✅ Updated CLAUDE.md (this documentation)

## Production Deployment - Hostinger (2025-11-16)

### Deployment Status: ✅ OPERATIONAL

**Production URL:** https://colaboradores.aratio.mrmtech.net/

**Server:** Hostinger
**Document Root:** `/public_html/mod_colab/public/`

### Critical Issues Resolved During Deployment

#### 1. Error 500 - Incorrect .htaccess RewriteBase

**Symptom:** HTTP ERROR 500 when accessing main URL

**Root Cause:**

```apache
# WRONG - Caused Error 500
RewriteBase /mod_colab/public/

# CORRECT - Fixed the issue
RewriteBase /
```

**Why:** The subdomain `colaboradores.aratio.mrmtech.net` points directly to `/public_html/mod_colab/public/`, so RewriteBase must be `/` not the full path.

**File Fixed:** `public/.htaccess` (line 11)

#### 2. Incorrect Log/Cache Paths in index.php

**Root Cause:**

```php
// WRONG
define('CACHE_PATH', ROOT_PATH . '/cache');
define('LOGS_PATH', ROOT_PATH . '/logs');

// CORRECT
define('CACHE_PATH', ROOT_PATH . '/storage/cache');
define('LOGS_PATH', ROOT_PATH . '/storage/logs');
```

**File Fixed:** `public/index.php` (lines 31-32)

Also added `@mkdir()` to suppress permission errors.

#### 3. Duplicated Variables in .env

**Root Cause:** Remote `.env` had:

```
APP_ENV=APP_ENV=production  ❌
APP_DEBUG=APP_DEBUG=false   ❌
```

**Solution:** Uploaded correct `.env.production` file with proper format.

#### 4. Missing `territorios` Table in Remote Database

**Issue:** Table `territorios` was not imported to production database

**Solution:**

```bash
# Export from local
mysqldump -h localhost -u root aratio territorios > territorios.sql

# Import to remote
mysql -h auth-db690.hstgr.io -u u156469157_aratio -p u156469157_aratio < territorios.sql
```

**Result:** ✅ 643 territory records imported

#### 5. Missing `src/Views/home/` Directory on Server

**Symptom:** Error 500 when accessing `/` route

**Root Cause:** FTP deployment script didn't include `home/` directory

**Solution:** Manually created directory and uploaded files via FTP:

```php
ftp_mkdir($ftp, 'home');
ftp_put($ftp, 'home/index.php', 'src/Views/home/index.php', FTP_BINARY);
```

**Files Added:**

- `/public_html/mod_colab/src/Views/home/index.php`

### Production Database Configuration

```
Host: auth-db690.hstgr.io
Port: 3306
Database: u156469157_aratio
User: u156469157_aratio
Password: 15zxCeBbvgsR
```

**Current State:**

- ✅ 8 tables (colaboradores, usuarios, territorios, curriculum, sesiones, historial_cambios_lider, importaciones_excel, logs_auditoria)
- ✅ 4 views (v_colaboradores_completo, v_estadisticas_perfil, v_estadisticas_territorio, v_lideres_metricas)
- ✅ 106 colaboradores
- ✅ 7 usuarios
- ✅ 643 territorios
- ✅ Stored procedures and triggers

### FTP Credentials (Hostinger)

```
Host: ftp://212.1.208.241 (port 21)
User: u156469157.aratio.mrmtech.net
Password: sthLX6bJPoGh
Remote Directory: /public_html/mod_colab/
```

### Deployment Scripts

**Complete FTP Deployment:**

```bash
bash deploy_ftp_improved.sh
```

**SSH Deployment (requires SSH enabled):**

```bash
bash deploy_ssh.sh
# SSH: ssh -p 65002 u156469157@212.1.208.241
```

**Database Sync:**

```bash
bash sync_databases.sh
```

**Upload Specific Files:**

```bash
php upload_ftp.php
```

### Diagnostic Files Created (Remove in Production)

Created for troubleshooting (should be removed for security):

- `public/diagnostico.php` - Full system diagnostic
- `public/info.php` - phpinfo()
- `public/test-simple.php` - Basic PHP test
- `public/test-load.php` - Step-by-step file loading test  
- `public/index-debug.php` - Index with detailed debugging

**Remove these files:**

```bash
rm public/diagnostico.php public/info.php public/test-*.php public/index-debug.php
```

### Production Access Credentials

**Admin Account:**

- Username: `admin`
- Password: `Admin123!`

**Test Accounts:**

- Leader: `mgarcia` / `Admin123!`
- Read-only: `consulta` / `Admin123!`

### Verification Checklist

- [x] Site accessible at https://colaboradores.aratio.mrmtech.net/
- [x] Login works correctly
- [x] Dashboard loads with statistics
- [x] Database connected and operational
- [x] 106 collaborators available
- [x] 643 territories available
- [x] All views and layouts loading
- [x] No 500 errors
- [x] .htaccess working correctly

### Deployment Documentation

Full deployment report available in: `DEPLOYMENT_SUCCESS.md`

### Next Steps for Production

1. **Security:**
   
   - Remove diagnostic files
   - Change default passwords
   - Review security headers
   - Enable HTTPS redirect

2. **Monitoring:**
   
   - Setup error log monitoring
   - Configure backup schedule
   - Monitor disk space
   - Review access logs

3. **Optimization:**
   
   - Enable OPcache
   - Configure CDN if needed
   - Optimize database queries
   - Minify assets

### Troubleshooting Production Issues

**View Application Logs:**

```bash
tail -50 /public_html/mod_colab/storage/logs/app.log
```

**View PHP Error Logs:**

```bash
tail -50 /home/u156469157/domains/aratio.mrmtech.net/logs/error_log
```

**Connect to Database:**

```bash
mysql -h auth-db690.hstgr.io -u u156469157_aratio -p u156469157_aratio
```

**SSH Access (if enabled):**

```bash
ssh -p 65002 u156469157@212.1.208.241
```

### Files Modified in Deployment Session (2025-11-16)

- ✅ public/.htaccess (corrected RewriteBase)
- ✅ public/index.php (corrected storage paths, added @mkdir)
- ✅ .env (uploaded correct production version)
- ✅ database/territorios.sql (imported 643 records)
- ✅ src/Views/home/index.php (uploaded missing file)
- ✅ Created DEPLOYMENT_SUCCESS.md (deployment documentation)
- ✅ Updated CLAUDE.md (this file)

## Geographic Cascade Fix (2025-11-20)

### Issue: Dropdown Hierarchy Not Working in Production

**Problem:** The geographic cascade (departamento → municipio → tipo_territorio → territorio → barrio) was not functioning when creating collaborators on the remote server.

**Root Cause:** Alpine.js was loaded **twice**:
1. Once in the layout: `src/Views/layouts/default.php` line 298
2. Again in individual views:
   - `src/Views/colaboradores/create.php` line 431 ❌
   - `src/Views/colaboradores/edit.php` line 423 ❌

This duplication prevented Alpine.js from initializing correctly, causing `init()` to never execute and `loadDepartamentos()` to never be called.

### Solution Applied

**Files Modified:**

1. **src/Views/colaboradores/create.php**
   - Removed duplicate Alpine.js script tag (line 431)
   - Alpine.js now loads only from layout

2. **src/Views/colaboradores/edit.php**
   - Removed duplicate Alpine.js script tag (line 423)
   - Alpine.js now loads only from layout

**Files Created:**

1. **public/test-territorios-api.php**
   - Diagnostic script to test all 5 territory API endpoints
   - Interactive UI showing test results (PASS/FAIL)
   - Validates JSON response structure and data
   - Access: https://colaboradores.aratio.mrmtech.net/test-territorios-api.php
   - **Should be deleted after verification**

2. **upload_fix_territorios.php**
   - FTP upload script for deploying fixes
   - Uploads corrected views and diagnostic script
   - Usage: `php upload_fix_territorios.php`

3. **FIX_TERRITORIOS_2025-11-20.md**
   - Complete documentation of the issue and solution
   - Architecture diagrams and data flow
   - Step-by-step verification guide
   - Troubleshooting tips

### How It Works

**Geographic Cascade Flow:**

```
1. Page Load → Alpine.js init() → loadDepartamentos()
2. User selects department → @change → loadMunicipios()
3. User selects municipality → @change → loadTiposTerritorio()
4. User selects territory type → @change → loadTerritorios()
5. User selects territory → @change → loadBarrios()
```

**API Endpoints:**

- GET `/territorios/departamentos` → All unique departments
- GET `/territorios/municipios-cascada?departamento=X` → Municipalities for department
- GET `/territorios/tipos?departamento=X&municipio=Y` → Territory types
- GET `/territorios/territorios?departamento=X&municipio=Y&tipo=Z` → Territories (numeric sort for "Comuna 1", "Comuna 2")
- GET `/territorios/barrios?departamento=X&municipio=Y&tipo=Z&territorio=W` → Neighborhoods

**Backend Implementation:**

- **Routes:** `routes/web.php` lines 220-224
- **Controller:** `ColaboradorController` lines 671-779 (getDepartamentos, getMunicipios, getTiposTerritor, getTerritorios, getBarrios)
- **Model:** `src/Models/Territorio.php` (SQL queries with prepared statements)
- **Database:** Table `territorios` (643 records)

### Verification Steps

1. **Run Diagnostics:**
   ```
   https://colaboradores.aratio.mrmtech.net/test-territorios-api.php
   ```
   Expected: 5/5 tests PASS ✅

2. **Test Form:**
   - Navigate to: https://colaboradores.aratio.mrmtech.net/colaboradores/create
   - Verify department dropdown loads automatically
   - Select department → municipalities load
   - Select municipality → types load
   - Select type → territories load
   - Select territory → neighborhoods load

3. **Check Browser Console:**
   - Open DevTools (F12)
   - Should NOT see: `Alpine.js is not defined` or `colaboradorForm is not a function`
   - Should see fetch requests to `/territorios/*` endpoints

4. **Clean Up:**
   ```bash
   # After verification, delete diagnostic script
   rm public/test-territorios-api.php
   ```

### Key Lesson

**Never duplicate JavaScript libraries that are already loaded in the layout.** Always check `src/Views/layouts/default.php` before including CDN scripts in individual views.

Alpine.js should only be loaded once, in the layout at line 298:
```html
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
```

### Files Modified (2025-11-20)

- ✅ src/Views/colaboradores/create.php (removed duplicate Alpine.js)
- ✅ src/Views/colaboradores/edit.php (removed duplicate Alpine.js)
- ✅ Created public/test-territorios-api.php (diagnostic tool)
- ✅ Created upload_fix_territorios.php (FTP deployment script)
- ✅ Created FIX_TERRITORIOS_2025-11-20.md (complete documentation)
- ✅ Updated CLAUDE.md (this file)

## Sistema de Trazabilidad Histórica (2025-11-22)

### Descripción General

Sistema completo de trazabilidad para monitorear la evolución del `dato_potencial` y `estado` de los colaboradores a lo largo del tiempo. Permite registrar cada cambio, visualizar tendencias y realizar reevaluaciones manuales con motivo.

### Componentes Implementados

#### 1. Base de Datos

**Tabla `historial_estados`:**
```sql
CREATE TABLE historial_estados (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    colaborador_id INT UNSIGNED NOT NULL,
    fecha_registro DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    dato_potencial INT NOT NULL DEFAULT 0,
    dato_historico INT NOT NULL DEFAULT 0,
    estado VARCHAR(20) NOT NULL,
    usuario_id INT UNSIGNED NULL,
    motivo VARCHAR(255) NULL,
    tipo_cambio ENUM('inicial', 'actualizacion', 'reevaluacion') DEFAULT 'actualizacion',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_colaborador (colaborador_id),
    INDEX idx_fecha (fecha_registro),
    INDEX idx_estado (estado)
);
```

**Triggers (ejecutar manualmente en phpMyAdmin):**
- `trg_historial_estados_insert` - Registra estado inicial al crear colaborador
- `trg_historial_estados_update` - Registra cambios cuando se modifica dato_potencial o estado

**Archivo de migración:** `database/migrations/create_historial_estados.sql`

#### 2. Modelo Colaborador - Nuevos Métodos

```php
// Obtener historial de estados de un colaborador
$historial = $colaboradorModel->getHistorialEstados($colaboradorId);

// Registrar reevaluación manual con motivo
$success = $colaboradorModel->registrarReevaluacion(
    $colaboradorId,
    $nuevoDatoPotencial,
    $motivo,
    $usuarioId
);

// Obtener tendencia general para dashboard (últimos N días)
$tendencia = $colaboradorModel->getTendenciaGeneralPotencial(60);

// Obtener evolución de estados por fecha
$evolucion = $colaboradorModel->getEvolucionEstados(30);
```

#### 3. Vista del Colaborador - Pestaña "Trazabilidad"

**Ubicación:** `src/Views/colaboradores/show.php`

**Características:**
- Tarjetas de estadísticas: Dato Potencial, Dato Histórico, Estado, Total Cambios
- Gráfico de líneas (ApexCharts) mostrando evolución temporal
- Tabla detallada del historial con variaciones (+/-) entre registros
- Indicadores de tipo de cambio: inicial, actualización, reevaluación

#### 4. Perfil Político Mejorado

En la vista de detalle del colaborador, la sección "Perfil Político" ahora incluye:
- **Dato Potencial** - Valor numérico destacado en azul
- **Dato Histórico** - Valor numérico destacado en verde
- **Estado** - Badge con color según estado (Nuevo, Creció, Igual, Decrece, Desvinculado)
- **Botón "Reevaluar"** - Abre modal para cambiar dato_potencial con motivo

#### 5. Modal de Reevaluación

**Funcionalidad:**
- Muestra información actual del colaborador
- Campo para nuevo dato potencial
- Selector de motivo predefinido:
  - Reevaluación periódica
  - Actualización de datos
  - Cambio de actividad
  - Verificación en campo
  - Corrección de error
  - Otro
- Campo para detalle adicional (opcional)
- Guarda con tipo_cambio = 'reevaluacion' en historial

**Endpoint:** `POST /colaboradores/{id}/reevaluar`

#### 6. Dashboard - Evolución de Estados

**Ubicación:** `src/Views/dashboard/index.php`

**Nuevo gráfico:** "Evolución de Estados en el Tiempo"
- Tipo: Área apilada (stacked area chart)
- Muestra tendencia de los últimos 60 días
- Series: Crecieron, Se mantienen, Decrecen, Nuevos, Desvinculados

### Flujo de Captura de Cambios

1. **Automático (via Triggers):**
   - Al crear colaborador → INSERT trigger registra estado inicial
   - Al editar colaborador → UPDATE trigger registra si cambió dato_potencial o estado

2. **Manual (via Reevaluación):**
   - Usuario hace clic en "Reevaluar" en el perfil del colaborador
   - Ingresa nuevo dato_potencial y selecciona motivo
   - Sistema guarda con tipo_cambio = 'reevaluacion' y el usuario_id

### Archivos Modificados/Creados (2025-11-22)

**Base de Datos:**
- ✅ `database/migrations/create_historial_estados.sql` - Script de migración completo

**Modelo:**
- ✅ `src/Models/Colaborador.php` - Nuevos métodos de trazabilidad

**Controladores:**
- ✅ `src/Controllers/ColaboradorController.php` - Método `reevaluar()` y carga de historialEstados
- ✅ `src/Controllers/DashboardController.php` - Carga de tendencia de estados

**Vistas:**
- ✅ `src/Views/colaboradores/show.php` - Pestaña Trazabilidad, Perfil Político mejorado, Modal Reevaluar
- ✅ `src/Views/dashboard/index.php` - Gráfico de evolución de estados

**Rutas:**
- ✅ `routes/web.php` - Ruta POST `/{id}/reevaluar`

### Verificación

1. **Ver trazabilidad:** https://colaboradores.aratio.mrmtech.net/colaboradores/1 → Pestaña "Trazabilidad"
2. **Reevaluar:** Clic en botón "Reevaluar" en sección Perfil Político
3. **Dashboard:** https://colaboradores.aratio.mrmtech.net/dashboard → Gráfico "Evolución de Estados"

### Notas Importantes

- Los triggers deben ejecutarse manualmente desde phpMyAdmin usando el archivo de migración
- El campo `usuario` en tabla `usuarios` se usa para mostrar quién hizo el cambio (no `username`)
- Los cambios se registran automáticamente cuando se edita un colaborador desde el formulario de edición

## Dashboard Staging con Gráficos Avanzados D3.js (2025-11-23)

### Descripción General

Dashboard de pruebas con visualizaciones avanzadas usando D3.js v7, incluyendo gráficos de chord, sunburst, treemap, radar y force-directed graphs.

**URL:** https://colaboradores.aratio.mrmtech.net/dashboard-staging

### Gráficos Implementados

#### 1. Red de Fuerza Principal (Force Network)
- Visualiza hasta 80 colaboradores con sus relaciones líder-seguidor
- Tamaño de nodos con escala exponencial (0.6) basada en cantidad de seguidores
- Colores por cantidad de seguidores:
  - Azul oscuro (#1e40af): >10 seguidores
  - Azul (#3b82f6): 6-10 seguidores
  - Verde (#10b981): 1-5 seguidores
  - Gris (#94a3b8): Sin seguidores
- Flechas direccionales en enlaces
- Arrastre interactivo y zoom
- Tooltip con información detallada
- Altura: 600px

#### 2. Chord Diagram (Perfil ↔ Municipio)
- Muestra relaciones bidireccionales entre perfiles y municipios
- Colores automáticos por categoría
- Tooltip interactivo con conteo

#### 3. Sunburst Chart (Geografía Jerárquica)
- Visualiza: Departamento → Municipio → Territorio
- Navegación drill-down al hacer clic
- Colores por nivel jerárquico

#### 4. Treemap (Distribución por Perfil)
- Muestra proporción de colaboradores por perfil
- Tamaño = cantidad de colaboradores
- Colores distintivos por perfil

#### 5. Force Graph Secundario (Top Líderes)
- Red simplificada de los principales líderes
- Tamaño acorde a seguidores

#### 6. Radar Chart (Métricas de Red)
- Compara múltiples métricas simultáneamente
- Ejes: Colaboradores, Líderes, Seguidores, Perfiles, Territorios

#### 7. Donut Chart (Estados)
- Distribución de colaboradores por estado
- Colores semafóricos

### Arquitectura

**Controlador:** `src/Controllers/DashboardController.php`

```php
// Método principal
public function staging() {
    $chordData = $this->prepareChordData();
    $sankeyData = $this->prepareSankeyData();
    $sunburstData = $this->prepareSunburstData();
    $forceNetworkData = $this->prepareForceNetworkData();
    // ... render view
}

// Preparación de datos para Force Network
private function prepareForceNetworkData(): array {
    $sql = "SELECT id, documento, CONCAT(nombres, ' ', apellidos) as nombre,
            perfil, municipio, lider_directo,
            COALESCE(total_seguidores_directos, 0) as seguidores
            FROM v_colaboradores_completo
            ORDER BY total_seguidores_directos DESC LIMIT 80";
    // Construye arrays de nodes[] y links[]
}
```

**Vista:** `src/Views/dashboard/staging.php`

**Ruta:** `routes/web.php` línea 72
```php
$router->get('/dashboard-staging', 'DashboardController@staging');
```

### Dependencias CDN

```html
<script src="https://cdn.jsdelivr.net/npm/d3@7"></script>
```

### Archivos Relacionados

- `src/Controllers/DashboardController.php` - Métodos de preparación de datos
- `src/Views/dashboard/staging.php` - Vista con todos los gráficos D3.js
- `routes/web.php` - Ruta del dashboard staging
- `upload_staging.php` - Script FTP para deployment

## Force Graph D3.js en Detalle de Colaborador (2025-11-23)

### Descripción General

Visualización interactiva de la red de seguidores de un colaborador específico usando D3.js Force-Directed Graph. Se agregó a la pestaña "Red de Seguidores" en la vista de detalle del colaborador.

**URL:** https://colaboradores.aratio.mrmtech.net/colaboradores/{id} → Pestaña "Red de Seguidores"

### Características

#### Visualización
- **Nodo Central:** Colaborador principal (azul oscuro, borde grueso)
- **Nodos Secundarios:** Seguidores directos y sub-seguidores (hasta 3 niveles)
- **Escala Exponencial:** `d3.scalePow().exponent(0.6)` para acentuar diferencias de tamaño
- **Rango de Tamaño:** 8px a 45px según cantidad de seguidores

#### Colores por Cantidad de Seguidores
```javascript
function getNodeColor(d) {
    if (d.isRoot) return '#1e40af';     // Azul oscuro - Colaborador principal
    if (d.seguidores > 10) return '#3b82f6'; // Azul
    if (d.seguidores > 5) return '#22c55e';  // Verde
    if (d.seguidores > 0) return '#10b981';  // Verde claro
    return '#94a3b8';                        // Gris - Sin seguidores
}
```

#### Interactividad
- **Arrastre de Nodos:** Drag & drop para reorganizar
- **Zoom:** Scroll del mouse (escala 0.3x a 3x)
- **Tooltip:** Información completa al pasar el mouse:
  - Nombre completo
  - Documento
  - Perfil
  - Cantidad de seguidores
  - Indicador de clic para navegar
- **Click en Nodo:** Navega a `/colaboradores/{id}` del seguidor seleccionado
- **Botón Reiniciar:** Reinicia la simulación de fuerzas

#### Física de Simulación
```javascript
forceSimulation = d3.forceSimulation(nodes)
    .force('link', d3.forceLink(links).id(d => d.id).distance(100))
    .force('charge', d3.forceManyBody().strength(-400))
    .force('center', d3.forceCenter(width / 2, height / 2))
    .force('collision', d3.forceCollide().radius(d => sizeScale(d.seguidores) + 10));
```

### Implementación

**Archivo:** `src/Views/colaboradores/show.php`

#### Estructura HTML (líneas 729-786)
```html
<!-- Force Graph D3.js - Red Simplificada -->
<div class="card mb-6">
    <div class="card-header">
        <h3>Red de Fuerza Interactiva</h3>
        <button onclick="resetForceGraph()">Reiniciar</button>
    </div>
    <div class="card-body">
        <!-- Leyenda -->
        <div class="flex flex-wrap gap-4 mb-4 p-3 bg-gray-50 rounded-lg">
            <!-- Códigos de color -->
        </div>
        <!-- Contenedor del grafo -->
        <div id="force-graph-container" style="height: 500px;"></div>
    </div>
</div>
```

#### JavaScript Principal (líneas 1454-1766)
```javascript
// Inicialización
function initializeForceGraph() {
    // 1. Preparar datos desde PHP
    const colaborador = <?= json_encode($colaborador) ?>;
    const seguidores = <?= json_encode($seguidores) ?>;

    // 2. Construir nodos y enlaces recursivamente
    agregarNodos(seguidores, colaborador.id, 1);

    // 3. Crear SVG con D3.js
    const svg = d3.select(container).append('svg');

    // 4. Configurar simulación de fuerzas
    forceSimulation = d3.forceSimulation(nodes)...

    // 5. Agregar eventos de interacción
    node.on('click', (event, d) => {
        if (!d.isRoot) {
            window.location.href = '/colaboradores/' + d.id;
        }
    });
}

// Reiniciar simulación
function resetForceGraph() {
    if (forceSimulation) {
        forceSimulation.alpha(1).restart();
    }
}
```

### Dependencias

```html
<!-- D3.js v7 -->
<script src="https://cdn.jsdelivr.net/npm/d3@7"></script>
```

### Flujo de Datos

```
1. ColaboradorController::show($id)
   └── Carga $colaborador, $seguidores (3 niveles)

2. show.php (Vista)
   └── json_encode() → JavaScript

3. initializeForceGraph()
   ├── Construye nodes[] y links[]
   ├── Crea SVG y elementos
   └── Inicia simulación D3.js

4. Usuario interactúa
   ├── Arrastre → dragstarted/dragged/dragended
   ├── Hover → Tooltip visible
   └── Click → window.location.href = '/colaboradores/' + id
```

### Posición en la Vista

La sección "Red de Fuerza Interactiva" aparece **después** de la visualización existente "Red Jerárquica Multi-Nivel" (Vis.js) y **antes** de la tabla "Lista de Seguidores Directos".

```
Pestaña "Red de Seguidores"
├── Estadísticas de Red (4 tarjetas)
├── Red Jerárquica Multi-Nivel (Vis.js) ← Existente
├── Red de Fuerza Interactiva (D3.js)  ← NUEVO
└── Lista de Seguidores Directos (Tabla)
```

### Archivos Modificados (2025-11-23)

- ✅ `src/Views/colaboradores/show.php` - Agregado Force Graph D3.js
  - Línea 1025: Script D3.js CDN
  - Líneas 729-786: HTML del contenedor
  - Líneas 1454-1766: JavaScript de inicialización
- ✅ `upload_staging.php` - Agregado show.php a lista de archivos

### Verificación

1. Ir a: https://colaboradores.aratio.mrmtech.net/colaboradores/1
2. Clic en pestaña **"Red de Seguidores"**
3. Scroll hasta ver **"Red de Fuerza Interactiva"**
4. Probar:
   - Arrastrar nodos
   - Zoom con scroll
   - Hover para ver tooltip
   - **Clic en un seguidor** → Navega a su detalle

### Troubleshooting

**Grafo no aparece:**
- Verificar consola (F12) por errores de D3.js
- Confirmar que la pestaña "seguidores" está activa
- Revisar que el colaborador tenga seguidores

**Nodos muy pequeños:**
- La escala exponencial (0.6) ya acentúa diferencias
- Si todos tienen pocos seguidores, los nodos serán similares

**Navegación no funciona:**
- Solo nodos NO-raíz son clickeables
- El nodo principal (azul oscuro) no navega

### Script de Deployment

```bash
# Subir cambios al servidor
php upload_staging.php

# Archivos incluidos:
# - src/Views/dashboard/staging.php
# - src/Controllers/DashboardController.php
# - src/Views/colaboradores/show.php
# - routes/web.php
```
