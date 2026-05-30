# -*- coding: utf-8 -*-
import mysql.connector

# Correct Credentials from config.php
DB_HOST = "auth-db690.hstgr.io"
DB_NAME = "u156469157_aratio_v1"
DB_USER = "u156469157_aratio_v1"
DB_PASS = "15zxCeBbvgsR"

def list_campaigns():
    try:
        conn = mysql.connector.connect(
            host=DB_HOST,
            user=DB_USER,
            password=DB_PASS,
            database=DB_NAME
        )
        cursor = conn.cursor()
        cursor.execute("SELECT id, nombre FROM campanas")
        for (id, nombre) in cursor:
            print(f"ID: {id}, Name: {nombre}")
        conn.close()
    except Exception as e:
        print(f"Error: {e}")

if __name__ == "__main__":
    list_campaigns()
