# Reglas de Calidad — Sistema Edison Giraldo

Todo output generado por agentes de IA para la campaña de Edison Giraldo
debe cumplir los siguientes estándares antes de ser usado o publicado.

---

## 📏 Rúbrica de Puntuación (0-100)

| Rango | Estado | Acción |
|-------|--------|--------|
| **≥ 90** | Excelencia | Publicar / Usar directamente |
| **80-89** | Aprobado | Publicar con correcciones menores |
| **60-79** | Revisión Necesaria | Iterar con el agente Worker |
| **< 60** | Rechazado | Reformular el objetivo y reiniciar |

---

## 🛑 Issues Fatales (Bloqueantes — rechazo inmediato)

- **Datos Falsos**: Estadísticas electorales no verificadas o inventadas
- **Goal Drift**: El output no responde la pregunta original
- **Promesas Inviables**: Propuestas sin respaldo presupuestal demostrable
- **Exposición de Credenciales**: Cualquier contraseña, token o credencial visible
- **Contradicción Interna**: Secciones del mismo documento que se contradicen

---

## ⚠️ Issues Dirigibles (Requieren corrección antes de aprobar)

- Falta de citas a fuentes oficiales
- Tono inapropiado para el canal (muy técnico para redes, muy informal para prensa)
- Propuestas sin "Próximos Pasos" concretos
- Análisis territorial sin referencia a comunas o datos específicos de Cali

---

## ✅ Requisitos de Diseño

- **Trazabilidad**: Todo dato debe tener referencia [Fuente]
- **Estructura Lógica**: Contexto → Análisis → Síntesis → Recomendaciones
- **Tono**: Propositivo, verificable, orientado al ciudadano
- **Revisión Doble**: Worker genera → Auditor valida → Score ≥ 80 → Uso permitido

---

## 🔄 Flujo de Trabajo Estándar

```
1. Definir objetivo claro
2. Worker (Estratega/Comunicador) genera respuesta
3. Auditor evalúa y puntúa
4. Si Score ≥ 80 → Usar/Publicar
5. Si Score < 80 → Identificar issues → Worker revisa → Volver al paso 3
```
