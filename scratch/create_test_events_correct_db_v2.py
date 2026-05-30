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
            'fecha_inicio': (datetime.now() + timedelta(days=2)).strftime('%Y-%m-%d %H:%M:%S'),
            'fecha_fin': (datetime.now() + timedelta(days=2, hours=4)).strftime('%Y-%m-%d %H:%M:%S'),
            'ubicacion': 'Plaza de Cayzedo, Cali',
            'latitud': 3.4516,
            'longitud': -76.5320,
            'campana_id': 1,
            'tipo': 'reunion',
            'estado': 'programado'
        }
        
        # Test Event 2: Siloé, Cali
        event2 = {
            'nombre': 'Taller de Liderazgo Juvenil',
            'descripcion': 'Taller enfocado en jóvenes de la comuna 20 para fortalecimiento de la red de voluntarios.',
            'fecha_inicio': (datetime.now() + timedelta(days=5)).strftime('%Y-%m-%d %H:%M:%S'),
            'fecha_fin': (datetime.now() + timedelta(days=5, hours=3)).strftime('%Y-%m-%d %H:%M:%S'),
            'ubicacion': 'Siloé, Comuna 20, Cali',
            'latitud': 3.4244,
            'longitud': -76.5486,
            'campana_id': 1,
            'tipo': 'capacitacion',
            'estado': 'programado'
        }
        
        query = """INSERT INTO eventos 
                   (nombre, descripcion, fecha_inicio, fecha_fin, ubicacion, latitud, longitud, campana_id, tipo, estado) 
                   VALUES 
                   (%(nombre)s, %(descripcion)s, %(fecha_inicio)s, %(fecha_fin)s, %(ubicacion)s, %(latitud)s, %(longitud)s, %(campana_id)s, %(tipo)s, %(estado)s)"""
        
        cursor.execute(query, event1)
        cursor.execute(query, event2)
        
        conn.commit()
        print("Success: 2 Test events created in correct DB.")
        conn.close()
    except Exception as e:
        print(f"Error: {e}")

if __name__ == "__main__":
    create_test_events()
