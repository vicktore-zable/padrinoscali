import json
import os
import re

# Configuración de archivos (Relativos al directorio TERRITORIOS)
file_barrios = "TERRITORIOS/BARRIOS-C-Y-P-B-J.geojson"
file_veredas = "TERRITORIOS/VEREDAS-C-Y-P-B-J.geojson"
output_sql = "TERRITORIOS/territorios_con_poligonos.sql"

def geojson_to_wkt(geometry):
    if not geometry:
        return "NULL"
    
    g_type = geometry["type"].upper()
    coords = geometry["coordinates"]

    if g_type == "POLYGON":
        rings = []
        for ring in coords:
            points = ", ".join([f"{p[0]} {p[1]}" for p in ring])
            rings.append(f"({points})")
        # Un Polígono es (anillo1, anillo2...) wrap en MULTIPOLYGON ((...))
        return "ST_GeomFromText('MULTIPOLYGON((" + ", ".join(rings) + "))', 4326)"

    elif g_type == "MULTIPOLYGON":
        polys = []
        for poly in coords:
            rings = []
            for ring in poly:
                points = ", ".join([f"{p[0]} {p[1]}" for p in ring])
                rings.append(f"({points})")
            polys.append("(" + ", ".join(rings) + ")")
        return "ST_GeomFromText('MULTIPOLYGON(" + ", ".join(polys) + ")', 4326)"

    return "NULL"

def clean(text):
    if text is None:
        return ""
    # Escapar comillas simples para SQL
    return str(text).replace("'", "''").strip()

# Estructura del contador global sin usar nonlocal (compatible con todas las versiones)
class Counter:
    val = 1

id_counter = Counter()

# Diccionario de códigos DANE base para municipios
m_codes = {
    "CALI": "76001",
    "PALMIRA": "76520",
    "YUMBO": "76892",
    "BUENAVENTURA": "76109",
    "JAMUNDI": "76364",
    "CARTAGO": "76147"
}

def process_features(features, source_type):
    inserts = []
    
    for feat in features:
        props = feat["properties"]
        geom = feat["geometry"]

        if not geom:
            continue

        nom_dep = "VALLE DEL CAUCA"
        m_name_raw = props.get("NOMB_MPIO", "").upper()
        
        if source_type == "BARRIOS":
            municipio = props.get("NOMB_MPIO", "").capitalize()
            # Normalizar nombres de municipios comunes
            if municipio.upper() == "CALI": municipio = "Cali"
            if municipio.upper() == "YUMBO": municipio = "Yumbo"
            if municipio.upper() == "PALMIRA": municipio = "Palmira"
            
            tipo_terr = "Urbano"
            cod_mpio = props.get("COD_MPIO", "")
            if not str(cod_mpio).isdigit():
                cod_mpio = m_codes.get(m_name_raw, "76000")
                
            codigo = props.get("cod_barrio", "")
            
            # Formatear Comuna para que coincida con la jerarquía actual (ej: "Comuna 1")
            comuna_raw = str(props.get("comuna", ""))
            if comuna_raw.isdigit():
                territorio = f"Comuna {comuna_raw}"
            else:
                territorio = comuna_raw
                
            barrio = props.get("n_barrio", "")
            
            # Generar ID puramente numérico (MPIO + COMUNA_SANE + SECUENCIA)
            num_comuna = re.findall(r'\d+', comuna_raw)
            c_part = num_comuna[0].zfill(2) if num_comuna else "00"
            custom_id = f"{cod_mpio}{c_part}{str(id_counter.val).zfill(3)}"
            
        else:  # VEREDAS
            municipio = props.get("NOMB_MPIO", "").capitalize()
            if municipio.upper() == "CALI": municipio = "Cali"
            if municipio.upper() == "YUMBO": municipio = "Yumbo"
            if municipio.upper() == "PALMIRA": municipio = "Palmira"
            
            tipo_terr = "Rural"
            cod_mpio = props.get("DPTOMPIO", "")
            if not str(cod_mpio).isdigit():
                cod_mpio = m_codes.get(m_name_raw, "76000")

            codigo = props.get("CODIGO_VER", "")
            territorio = "Rural"
            barrio = props.get("NOMBRE_VER", "")
            
            if "NOM_DEP" in props:
                nom_dep = props["NOM_DEP"]
            
            # Asegurar que el ID de vereda sea numérico
            if codigo and str(codigo).isdigit():
                custom_id = codigo
            else:
                custom_id = f"{cod_mpio}99{str(id_counter.val).zfill(3)}"

        wkt_func = geojson_to_wkt(geom)
        
        val_str = f"({custom_id}, '{clean(nom_dep)}', '{clean(municipio)}', '{clean(tipo_terr)}', '{clean(cod_mpio)}', '{clean(codigo)}', '{clean(territorio)}', '{clean(barrio)}', {wkt_func})"
        inserts.append(val_str)
        id_counter.val += 1

    return inserts

with open(output_sql, "w", encoding="utf-8") as f_out:
    f_out.write("SET NAMES utf8mb4;\nSET FOREIGN_KEY_CHECKS = 0;\n\n")
    f_out.write("DROP TABLE IF EXISTS `territorios`;\n")
    f_out.write("CREATE TABLE `territorios` (\n")
    f_out.write("  `id` bigint NOT NULL,\n")
    f_out.write("  `departamento` varchar(255) DEFAULT NULL,\n")
    f_out.write("  `municipio` varchar(255) DEFAULT NULL,\n")
    f_out.write("  `Tipo_territorio` varchar(255) DEFAULT NULL,\n")
    f_out.write("  `cod_mpio` varchar(255) DEFAULT NULL,\n")
    f_out.write("  `Código` varchar(255) DEFAULT NULL,\n")
    f_out.write("  `Territorio` varchar(255) DEFAULT NULL,\n")
    f_out.write("  `barrio` varchar(255) DEFAULT NULL,\n")
    f_out.write("  `geometria` GEOMETRY NOT NULL,\n")
    f_out.write("  PRIMARY KEY (`id`),\n")
    f_out.write("  SPATIAL INDEX (`geometria`)\n")
    f_out.write(") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;\n\n")

    # Procesar Barrios
    if os.path.exists(file_barrios):
        with open(file_barrios, "r", encoding="utf-8") as f:
            data = json.load(f)
            inserts = process_features(data["features"], "BARRIOS")
            if inserts:
                # Escribir en lotes de 50 para evitar problemas de memoria/paquete
                batch_size = 50
                for i in range(0, len(inserts), batch_size):
                    batch = inserts[i:i + batch_size]
                    f_out.write("INSERT INTO `territorios` VALUES\n")
                    f_out.write(",\n".join(batch) + ";\n\n")

    # Procesar Veredas
    if os.path.exists(file_veredas):
        with open(file_veredas, "r", encoding="utf-8") as f:
            data = json.load(f)
            inserts = process_features(data["features"], "VEREDAS")
            if inserts:
                batch_size = 50
                for i in range(0, len(inserts), batch_size):
                    batch = inserts[i:i + batch_size]
                    f_out.write("INSERT INTO `territorios` VALUES\n")
                    f_out.write(",\n".join(batch) + ";\n\n")

    f_out.write("\nSET FOREIGN_KEY_CHECKS = 1;")

print(f"Éxito: Archivo generado en {output_sql} con {id_counter.val - 1} registros.")

