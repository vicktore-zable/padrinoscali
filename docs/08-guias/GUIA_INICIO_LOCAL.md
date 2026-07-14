# 🚀 GUÍA DE INICIO - SERVIDOR LOCAL ARATIO (UNIFICADO)

## Configuración del Entorno Local ✅

Se ha unificado el inicio del servidor para que cubra tanto el núcleo de Aratio como el nuevo módulo de colaboradores (`mod_colab`), sincronizando el comportamiento con el servidor de producción.

### 🛠️ Cambios Realizados para Sincronización
1. **Router Unificado (`router.php`)**: Actualizado para que las rutas `/registro-simpatizante`, `/registro-lider` e `/inscripcion` utilicen la lógica de `mod_colab/public/index.php`, tal como lo hace el `.htaccess` en producción.
2. **Corrección de Base de Datos**: Se ajustó `mod_colab/.env` para usar la base de datos `u156469157_aratio_v1`, asegurando que los datos sean consistentes en todo el sistema.
3. **Script de Inicio Mejorado**: `run-local.bat` ahora verifica el entorno y abre automáticamente el navegador.

---

## 📝 INSTRUCCIONES DE USO

### Paso 1: Iniciar el Servidor Local

Ejecuta el archivo ubicado en la raíz del proyecto:

```batch
# Doble clic en:
run-local.bat
```

**El script realizará**:
1. Verificación de versión de PHP.
2. Inicio del servidor en `http://localhost:8000`.
3. Apertura automática de la página de inicio.

### Paso 2: Rutas Disponibles

- **Dashboard Principal**: [http://localhost:8000](http://localhost:8000)
- **Registro de Simpatizante**: [http://localhost:8000/registro-simpatizante](http://localhost:8000/registro-simpatizante)
- **Registro de Líder**: [http://localhost:8000/registro-lider](http://localhost:8000/registro-lider)
- **Inscripción General**: [http://localhost:8000/inscripcion](http://localhost:8000/inscripcion)

---

## 🧪 SOLUCIÓN DE PROBLEMAS (Diferencias Producción vs Local)

Si notas que algo funciona en producción pero no localmente, verifica:

1. **Rutas Case-Sensitive**: En Hostinger (Linux) `Archivo.php` y `archivo.php` son distintos. Localmente (Windows) funcionan igual. Asegúrate de usar minúsculas siempre.
2. **Base de Datos**: El módulo `mod_colab` utiliza un archivo `.env` independiente. Hemos sincronizado este archivo con las credenciales de `config/config.php` (v1).
3. **Router PHP**: El servidor local no lee el `.htaccess`. Todos los cambios de rutas deben replicarse en `router.php`.

---

## 📊 ESTADO ACTUAL

| Componente | Estado | Notas |
|------------|--------|-------|
| Servidor Local | ✅ Unificado | Usa `run-local.bat` en la raíz |
| Base de Datos | ✅ Sincronizada | Apuntando a `aratio_v1` en Hostinger |
| Rutas Públicas | ✅ Sincronizadas | Mismo comportamiento que producción |
| APIs Geográficas | ✅ Funcional | Cascada de 5 niveles operativa |

---

**Última actualización**: 17 de Febrero de 2026
**Autor**: Antigravity AI
