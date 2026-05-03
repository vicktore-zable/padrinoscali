import mysql.connector
from mysql.connector import Error

# Configuración de las conexiones
# Source: Base de datos antigua/origen (u156469157_aratio)
config_source = {
    'user': 'u156469157_aratio',
    'password': '15zxCeBbvgsR', # Contraseña de producción (asumida igual a V1)
    'host': 'auth-db690.hstgr.io', # Host de Hostinger
    'database': 'u156469157_aratio'
}

# Target: Base de datos nueva/destino (u156469157_aratio_v1)
config_target = {
    'user': 'u156469157_aratio_v1',
    'password': '15zxCeBbvgsR',
    'host': 'auth-db690.hstgr.io',
    'database': 'u156469157_aratio_v1'
}

def sincronizar_colaboradores():
    conn_src = None
    conn_tgt = None
    
    try:
        # 1. Conectar a la base de datos ORIGEN
        print("Conectando a base de datos origen (u156469157_aratio)...")
        conn_src = mysql.connector.connect(**config_source)
        cursor_src = conn_src.cursor(dictionary=True)
        
        # 2. Obtener los datos actuales
        cursor_src.execute("SELECT * FROM colaboradores")
        rows = cursor_src.fetchall()
        
        if not rows:
            print("No hay datos para sincronizar.")
            return

        # 3. Conectar a la base de datos DESTINO
        print("Conectando a base de datos destino (u156469157_aratio_v1)...")
        conn_tgt = mysql.connector.connect(**config_target)
        cursor_tgt = conn_tgt.cursor()
        
        # 4. Preparar la consulta de inserción/actualización (UPSERT)
        # Sincronizamos TODOS los campos que usa el sistema (PHP y V1)
        # Campos agregados: telefono, email, puesto_votacion, mesa_votacion, lider_directo, dato_potencial
        upsert_query = """
            INSERT INTO colaboradores (
                campana_id, nombres, apellidos, tipo_documento, documento, 
                fecha_nacimiento, genero, telefono, email,
                departamento, municipio, puesto_votacion, mesa_votacion,
                lider_directo, perfil, nivel_participacion, dato_potencial,
                created_at, updated_at
            ) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
            ON DUPLICATE KEY UPDATE 
                nombres = VALUES(nombres),
                apellidos = VALUES(apellidos),
                genero = VALUES(genero),
                telefono = VALUES(telefono),
                email = VALUES(email),
                departamento = VALUES(departamento),
                municipio = VALUES(municipio),
                puesto_votacion = VALUES(puesto_votacion),
                mesa_votacion = VALUES(mesa_votacion),
                lider_directo = VALUES(lider_directo),
                perfil = VALUES(perfil),
                nivel_participacion = VALUES(nivel_participacion),
                dato_potencial = VALUES(dato_potencial),
                updated_at = NOW()
        """

        # 5. Procesar y enviar cada registro
        data_to_sync = []
        for row in rows:
            # Mapeo de campos - Usamos .get() para evitar errores si la columna no existe en origen
            
            # Campos obligatorios o con defaults
            campana_id = 2 # Forzado a Campaña 2
            depto = row.get('departamento') or 'Valle del Cauca'
            muni = row.get('municipio') or 'Yumbo'
            
            values = (
                campana_id,
                row.get('nombres'),
                row.get('apellidos'),
                row.get('tipo_documento'),
                row.get('documento'),
                row.get('fecha_nacimiento'),
                row.get('genero'),
                row.get('telefono'),
                row.get('email'),
                depto,
                muni,
                row.get('puesto_votacion'),
                row.get('mesa_votacion'),
                row.get('lider_directo'),
                row.get('perfil') or 'Simpatizante',
                row.get('nivel_participacion') or 'Simpatizante',
                row.get('dato_potencial') or 0,
                row.get('created_at'),
                row.get('updated_at')
            )
            data_to_sync.append(values)

        # Ejecución masiva
        print(f"Insertando/Actualizando {len(data_to_sync)} registros...")
        cursor_tgt.executemany(upsert_query, data_to_sync)
        conn_tgt.commit()
        
        print(f"Sincronización exitosa: {cursor_tgt.rowcount} registros procesados.")

    except Error as e:
        print(f"Error durante la sincronización: {e}")
    
    finally:
        if conn_src and conn_src.is_connected():
            cursor_src.close()
            conn_src.close()
        if conn_tgt and conn_tgt.is_connected():
            cursor_tgt.close()
            conn_tgt.close()

if __name__ == "__main__":
    sincronizar_colaboradores()