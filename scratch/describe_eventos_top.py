# -*- coding: utf-8 -*-
import mysql.connector

# Correct Credentials from config.php
DB_HOST = "auth-db690.hstgr.io"
DB_NAME = "u156469157_aratio_v1"
DB_USER = "u156469157_aratio_v1"
DB_PASS = "15zxCeBbvgsR"

def describe_table():
    try:
        conn = mysql.connector.connect(
            host=DB_HOST,
            user=DB_USER,
            password=DB_PASS,
            database=DB_NAME
        )
        cursor = conn.cursor()
        cursor.execute("DESCRIBE eventos")
        rows = cursor.fetchall()
        for i, row in enumerate(rows):
            if i < 15:
                print(row)
        conn.close()
    except Exception as e:
        print(f"Error: {e}")

if __name__ == "__main__":
    describe_table()
