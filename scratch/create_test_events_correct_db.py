# -*- coding: utf-8 -*-
import mysql.connector
from datetime import datetime, timedelta

# Correct Credentials from config.php
DB_HOST = "auth-db690.hstgr.io"
DB_NAME = "u156469157_aratio_v1"
DB_USER = "u156469157_aratio_v1"
DB_PASS = "15zxCeBbvgsR"

def create_test_events():
    try:
        conn = mysql.connector.connect(
            host=DB_HOST,
            user=DB_USER,
            password=DB_PASS,
            database=DB_NAME
        )
        cursor = conn.cursor()
        
        # Test Event 1: Plaza de Cayzedo, Cali
        event1 = {
            'nombre': 'Gran Encuentro Comunitario - Centro',
            'descripcion': 'Evento de prueba para verificar visualización en Aratio. Encuentro con líderes del sector centro.',
            'fecha': (datetime.now() + timedelta(days=2)).strftime('%Y-%m-%d %H:%M:%S'),
            'ubicacion': 'Plaza de Cayzedo, Cali',
            'latitud': 3.4516,
            'longitud': -76.5320,
            'campana_id': 1,
            'estado': 'proximo'
        }
        
        # Test Event 2: Siloé, Cali
        event2 = {
            'nombre': 'Taller de Liderazgo Juvenil',
            'descripcion': 'Taller enfocado en jóvenes de la comuna 20 para fortalecimiento de la red de voluntarios.',
            'fecha': (datetime.now() + timedelta(days=5)).strftime('%Y-%m-%d %H:%M:%S'),
            'ubicacion': 'Siloé, Comuna 20, Cali',
            'latitud': 3.4244,
            'longitud': -76.5486,
            'campana_id': 1,
            'estado': 'proximo'
        }
        
        query = "INSERT INTO eventos (nombre, descripcion, fecha, ubicacion, latitud, longitud, campana_id, estado) VALUES (%(nombre)s, %(descripcion)s, %(fecha)s, %(ubicacion)s, %(latitud)s, %(longitud)s, %(campana_id)s, %(estado)s)"
        
        cursor.execute(query, event1)
        cursor.execute(query, event2)
        
        conn.commit()
        print("Success: 2 Test events created in correct DB.")
        conn.close()
    except Exception as e:
        print(f"Error: {e}")

if __name__ == "__main__":
    create_test_events()
