import mysql.connector
import json

db_config = {
    "host": "auth-db690.hstgr.io",
    "user": "u156469157_aratio_v1",
    "password": "15zxCeBbvgsR",
    "database": "u156469157_aratio_v1"
}

def describe_tables():
    try:
        conn = mysql.connector.connect(**db_config)
        cursor = conn.cursor(dictionary=True)
        
        for table in ['candidatos', 'campanas', 'eventos']:
            cursor.execute(f"DESCRIBE {table}")
            columns = cursor.fetchall()
            print(f"\nTable: {table}")
            for col in columns:
                print(f"  {col['Field']} ({col['Type']})")
        
        cursor.close()
        conn.close()
    except Exception as e:
        print(f"Error: {e}")

if __name__ == "__main__":
    describe_tables()
