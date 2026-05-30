# -*- coding: utf-8 -*-
import mysql.connector

def update_test_events():
    config = {
        'host': 'auth-db690.hstgr.io',
        'database': 'u156469157_aratio_v1',
        'user': 'u156469157_aratio_v1',
        'password': '15zxCeBbvgsR'
    }
    
    try:
        conn = mysql.connector.connect(**config)
        cursor = conn.cursor()
        
        # Move events 5 and 6 to campaign 2
        cursor.execute("UPDATE eventos SET campana_id = 2 WHERE id IN (5, 6)")
        
        conn.commit()
        print(f"Updated {cursor.rowcount} events to campaign ID 2.")
        
        cursor.close()
        conn.close()
    except Exception as e:
        print(f"Error: {e}")

if __name__ == "__main__":
    update_test_events()
