import mysql.connector
from datetime import datetime

db_config = {
    "host": "157.173.208.254",
    "user": "u577647812_aratio",
    "password": "v6xSHUWhjrxE",
    "database": "u577647812_aratio"
}

events = [
    {
        "campana_id": 2,
        "responsable_id": 1,
        "nombre": "Gran Lanzamiento de Campaña - Comuna 2",
        "tipo": "mitin",
        "descripcion": "Lanzamiento oficial de la propuesta de gobierno para la Comuna 2. Encuentro con líderes y simpatizantes.",
        "fecha_inicio": "2026-05-10 18:00:00",
        "fecha_fin": "2026-05-10 21:00:00",
        "ubicacion": "Parque de la Comuna 2, Cali",
        "estado": "programado",
        "asistentes_esperados": 500,
        "presupuesto": 1500000
    },
    {
        "campana_id": 2,
        "responsable_id": 1,
        "nombre": "Diálogos Ciudadanos - Sector Comercio",
        "tipo": "reunion",
        "descripcion": "Encuentro con comerciantes para discutir temas de seguridad y desarrollo económico local.",
        "fecha_inicio": "2026-05-15 10:00:00",
        "fecha_fin": "2026-05-15 13:00:00",
        "ubicacion": "Cámara de Comercio de Cali, Auditorio Principal",
        "estado": "programado",
        "asistentes_esperados": 150,
        "presupuesto": 800000
    }
]

def create_events():
    try:
        conn = mysql.connector.connect(**db_config)
        cursor = conn.cursor()
        
        query = """
        INSERT INTO eventos (
            campana_id, responsable_id, nombre, tipo, descripcion, 
            fecha_inicio, fecha_fin, ubicacion, estado, 
            asistentes_esperados, presupuesto, created_at, updated_at
        ) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, NOW(), NOW())
        """
        
        for event in events:
            cursor.execute(query, (
                event["campana_id"],
                event["responsable_id"],
                event["nombre"],
                event["tipo"],
                event["descripcion"],
                event["fecha_inicio"],
                event["fecha_fin"],
                event["ubicacion"],
                event["estado"],
                event["asistentes_esperados"],
                event["presupuesto"]
            ))
            print(f"Created event: {event['nombre']}")
        
        conn.commit()
        cursor.close()
        conn.close()
        print("\nAll events created successfully!")
    except Exception as e:
        print(f"Error: {e}")

if __name__ == "__main__":
    create_events()
