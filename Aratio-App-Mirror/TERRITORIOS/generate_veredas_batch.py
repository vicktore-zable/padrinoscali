
import json
from shapely.geometry import shape, MultiPolygon, Polygon
import os

# Approved list of municipalities
TARGET_NAMES = {
    'ALCALÁ', 'ANDALUCIA', 'ANSERMANUEVO', 'ARGELIA', 'BOLÍVAR', 'BUGA', 
    'BUGA LA GRANDE', 'CAICEDONIA', 'CALIMA', 'CANDELARIA', 'DAGUA', 
    'EL CAIRO', 'EL CERRITO', 'EL DOVIO', 'EL ÁGUILA', 'FLORIDA', 
    'GINEBRA', 'GUACARÍ', 'LA CUMBRE', 'LA UNIÓN', 'LA VICTORIA', 
    'OBANDO', 'PRADERA', 'RESTREPO', 'RIOFRÍO', 'ROLDANILLO', 
    'SAN PEDRO', 'SEVILLA', 'TORO', 'TRUJILLO', 'TULUÁ', 'ULLOA', 
    'VERSALLES', 'VIJES', 'YOTOCO', 'ZARZAL'
}

geojson_path = r'h:\Mi unidad\2025\5d\app\Multi-Campaign Management System\TERRITORIOS\VEREDAS_O76.geojson'
output_path = r'h:\Mi unidad\2025\5d\app\Multi-Campaign Management System\TERRITORIOS\veredas_batch_2026.sql'

with open(geojson_path, 'r', encoding='utf-8') as f:
    data = json.load(f)

sql_statements = []
processed_ids = set()
count = 0

for feature in data['features']:
    props = feature['properties']
    mpio_name = props.get('NOMB_MPIO', '').upper().strip()
    
    if mpio_name in TARGET_NAMES:
        vereda_id = props.get('CODIGO_VER')
        
        # Skip duplicates or invalid IDs
        if not vereda_id or vereda_id in processed_ids:
            continue
            
        geom = shape(feature['geometry'])
        if not geom.is_valid:
            geom = geom.buffer(0)
        
        if isinstance(geom, Polygon):
            geom = MultiPolygon([geom])
        
        wkt = geom.wkt
        
        departamento = props.get('NOM_DEP', 'VALLE DEL CAUCA')
        municipio = props.get('NOMB_MPIO', '').title().replace('Buga La Grande', 'Bugalagrande') # Minor cleanup for consistency
        vereda_nombre = props.get('NOMBRE_VER', '').upper()
        mpio_code = props.get('DPTOMPIO')
        
        # INSERT INTO `territorios` VALUES (id, departamento, municipio, Tipo_territorio, cod_mpio, Código, Territorio, barrio, geometria)
        # Using vereda_id as both id and Código. Territorio set to 'Rural'.
        stmt = f"INSERT INTO `territorios` VALUES ({vereda_id}, '{departamento}', '{municipio}', 'Rural', '{mpio_code}', '{vereda_id}', 'Rural', '{vereda_nombre}', ST_GeomFromText('{wkt}'));"
        sql_statements.append(stmt)
        processed_ids.add(vereda_id)
        count += 1

with open(output_path, 'w', encoding='utf-8') as f:
    for stmt in sql_statements:
        f.write(stmt + '\n')

print(f"DONE: Generated {count} vereda entries.")
print(f"Output saved to: {output_path}")
