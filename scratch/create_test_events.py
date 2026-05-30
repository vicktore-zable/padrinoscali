# -*- coding: utf-8 -*-
import paramiko

HOST = "157.173.208.254"
PORT = 65002
USER = "u577647812"
PASS = 'E=j$`01yHi^?XfpoM@|CD"5H4'
DB_USER = "u577647812_aratio"
DB_PASS = "Aratio2026*"
DB_NAME = "u577647812_aratio"

def create_test_events():
    client = paramiko.SSHClient()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    client.connect(HOST, port=PORT, username=USER, password=PASS)
    
    # 1. Get IDs
    query_ids = f"mysql -u {DB_USER} -p'{DB_PASS}' -e 'SELECT id FROM campanas LIMIT 1; SELECT id FROM usuarios LIMIT 1;' {DB_NAME}"
    stdin, stdout, stderr = client.exec_command(query_ids)
    ids_output = stdout.read().decode()
    print(f"IDs Output: {ids_output}")
    
    # 2. Extract IDs (very simple extraction)
    lines = ids_output.splitlines()
    campana_id = lines[1] if len(lines) > 1 else "1"
    user_id = lines[3] if len(lines) > 3 else "1"
    
    print(f"Using Campana ID: {campana_id}, User ID: {user_id}")
    
    # 3. Insert Events
    event1 = f"INSERT INTO eventos (campana_id, responsable_id, nombre, tipo, descripcion, fecha_inicio, fecha_fin, ubicacion, departamento, municipio, latitud, longitud) VALUES ({campana_id}, {user_id}, \"Lanzamiento de Campaña Cali\", \"Mitín\", \"Evento masivo en el centro\", \"2026-05-01 18:00:00\", \"2026-05-01 21:00:00\", \"Plaza de Cayzedo\", \"Valle del Cauca\", \"Cali\", 3.4516, -76.5320);"
    event2 = f"INSERT INTO eventos (campana_id, responsable_id, nombre, tipo, descripcion, fecha_inicio, fecha_fin, ubicacion, departamento, municipio, latitud, longitud) VALUES ({campana_id}, {user_id}, \"Reunión Comunal Siloé\", \"Reunión\", \"Encuentro con líderes del sector\", \"2026-05-02 10:00:00\", \"2026-05-02 12:00:00\", \"Cancha de Siloé\", \"Valle del Cauca\", \"Cali\", 3.4244, -76.5486);"
    
    insert_query = f"mysql -u {DB_USER} -p'{DB_PASS}' -e '{event1} {event2}' {DB_NAME}"
    stdin, stdout, stderr = client.exec_command(insert_query)
    
    print("Test events created.")
    client.close()

if __name__ == "__main__":
    create_test_events()
