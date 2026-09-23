# -*- coding: utf-8 -*-
import mysql.connector
import sys

# Target Database on Hostinger
DB_CONFIG = {
    'host': '157.173.208.254',
    'user': 'u577647812_aratio',
    'password': 'v6xSHUWhjrxE', # Password from config.php
    'database': 'u577647812_aratio'
}

try:
    print(f"Connecting to {DB_CONFIG['host']}...")
    conn = mysql.connector.connect(**DB_CONFIG)
    print("Connection successful!")
    cursor = conn.cursor()
    cursor.execute("SELECT COUNT(*) FROM usuarios")
    count = cursor.fetchone()[0]
    print(f"Test query successful. User count: {count}")
    conn.close()
except Exception as e:
    print(f"Connection failed: {e}")
    # Try another password if needed
    print("Retrying with Admin123!...")
    DB_CONFIG['password'] = 'Admin123!'
    try:
        conn = mysql.connector.connect(**DB_CONFIG)
        print("Connection successful with Admin123!")
        conn.close()
    except Exception as e2:
        print(f"Retry failed: {e2}")
