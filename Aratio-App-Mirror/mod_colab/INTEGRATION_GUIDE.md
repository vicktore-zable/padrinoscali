# Guía de Integración: Aratio -> Colaboradores

Esta guía describe cómo integrar la plataforma principal (aratio.mrmtech.net) con el módulo de gestión de colaboradores.

## 🔗 Estructura de Enlaces

Para filtrar automáticamente a los colaboradores por una campaña específica desde el sitio principal, utilice los siguientes parámetros en la URL:

### 1. Filtrar por ID de Campaña
Si tiene un ID numérico para la campaña:
`https://colaboradores.aratio.mrmtech.net/colaboradores?campana_id=[ID]`
*Alias soportado: `campaign_id`*

### 2. Filtrar por Nombre de Campaña
Para mostrar el nombre de la campaña en el título:
`https://colaboradores.aratio.mrmtech.net/colaboradores?campana=[NOMBRE_CAMPAÑA]`
*Alias soportados: `campaign`, `campana_nombre`*

### 3. Combinado (Recomendado)
`https://colaboradores.aratio.mrmtech.net/colaboradores?campana_id=10&campana=Cali+Limpia`

## 🔌 Integración vía API (Sin Password)

Si deseas mostrar los colaboradores directamente dentro de la interfaz de Aratio sin que el usuario tenga que loguearse en el sistema de colaboradores, puedes usar nuestra API REST:

### Endpoint
`GET https://colaboradores.aratio.mrmtech.net/api/v1/colaboradores?campana_id=[ID]`

### Autenticación
Debes enviar el siguiente header en tu petición desde Aratio:
`X-API-Key: aratio_prod_secure_token_5d_2025`

### Respuesta (JSON)
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "nombres": "Juan",
      "apellidos": "Pérez",
      ...
    }
  ],
  "count": 1
}
```

## 🛠 Cambios Realizados en el Sistema

1.  **Modelo de Datos**: Se habilitó la búsqueda por los campos `campana_id` y `campana_nombre`.
2.  **Interfaz Dinámica**: El título del listado ahora muestra el nombre de la campaña si está presente (ej: "Colaboradores - Cali Limpia").
3.  **Persistencia**: Si el usuario navega a /create o aplica otros filtros (perfil, estado), el ID de la campaña se mantiene mediante inputs ocultos y parámetros en los enlaces.
4.  **Base de Datos**: Se preparó una migración en `database/migrations/add_campaign_support.sql`.

## ⚠️ Pasos para completar la integración

1.  **Ejecutar Migración**: El usuario debe ejecutar el siguiente SQL en su base de datos de Hostinger:
    ```sql
    ALTER TABLE colaboradores ADD COLUMN campana_id INT NULL;
    ALTER TABLE colaboradores ADD COLUMN campana_nombre VARCHAR(100) NULL;
    CREATE INDEX idx_campana_id ON colaboradores(campana_id);
    ```
2.  **Actualizar el menú en Aratio**: En la plataforma principal, actualice los enlaces de "Ver Colaboradores" para que incluyan los parámetros mencionados arriba.

---
**Nota**: El sistema de colaboradores ahora es agnóstico a la campaña, permitiendo ser una herramienta centralizada para múltiples proyectos de Aratio.
