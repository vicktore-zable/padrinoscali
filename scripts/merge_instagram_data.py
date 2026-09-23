#!/usr/bin/env python3
"""
Fusiona todas las corridas versionadas de Instagram en un solo archivo maestro.
Deriva shortcode y timestamp_unix de datos existentes cuando faltan.
Backfillea coordenadas visitando posts que no tienen lat/lng.
"""
import json, os, glob, time, re, sys
from datetime import datetime
from collections import OrderedDict

STORAGE_DIR = os.path.join(os.path.dirname(__file__), 'storage')
VERSIONED_FILES = sorted(glob.glob(os.path.join(STORAGE_DIR, 'instagram_data_*.json')))
MASTER_OUT = os.path.join(STORAGE_DIR, 'maestro_instagram.json')
SINCE_DATE = '2022-01-01'

print(f"Archivos versionados: {len(VERSIONED_FILES)}")

by_id = {}

for fpath in VERSIONED_FILES:
    with open(fpath, 'r', encoding='utf-8') as f:
        data = json.load(f)
    for periodo, posts in data.get('timeline', {}).items():
        for post in posts:
            fecha = post.get('fecha', '')
            if fecha < SINCE_DATE:
                continue
            post_id = (post.get('shortcode') or '') or post.get('url', '').rstrip('/').split('/')[-1]
            if not post_id:
                continue
            # Derive shortcode from URL if missing
            if not post.get('shortcode') and post.get('url'):
                post['shortcode'] = post['url'].rstrip('/').split('/')[-1]
            # Derive timestamp_unix from fecha_raw if missing
            if not post.get('timestamp_unix') and post.get('fecha_raw'):
                try:
                    iso = post['fecha_raw'].replace('Z', '+00:00')
                    dt = datetime.fromisoformat(iso)
                    post['timestamp_unix'] = int(dt.timestamp())
                except:
                    pass
            
            if post_id not in by_id:
                by_id[post_id] = post
            else:
                existing = by_id[post_id]
                old_score = sum(1 for v in [existing.get('lat'), existing.get('lng'), existing.get('ubicacion')] if v)
                new_score = sum(1 for v in [post.get('lat'), post.get('lng'), post.get('ubicacion')] if v)
                if new_score > old_score:
                    by_id[post_id] = post

print(f"Posts unicos desde 2022: {len(by_id)}")

# Backfill coordinates for posts with location name but no lat/lng
posts_missing_coords = {pid: p for pid, p in by_id.items()
                        if not p.get('lat') and not p.get('lng')
                        and p.get('ubicacion') and p['ubicacion'] not in ('Ubicaciones', '')
                        and (p.get('url') or p.get('shortcode'))}
posts_with_coords = {pid: p for pid, p in by_id.items() if p.get('lat') or p.get('lng')}
print(f"  Con coordenadas: {len(posts_with_coords)}")
print(f"  Sin coords (con ubicacion real): {len(posts_missing_coords)}")
print(f"  Sin coords (sin ubicacion): {len(by_id) - len(posts_with_coords) - len(posts_missing_coords)}")

if posts_missing_coords:
    batch_size = min(len(posts_missing_coords), 80)
    batch = list(posts_missing_coords.items())[:batch_size]
    print(f"\nBackfilleando coordenadas para {batch_size} posts con ubicacion...")
    try:
        from selenium import webdriver
        from selenium.webdriver.chrome.options import Options
        from selenium.webdriver.chrome.service import Service
        from webdriver_manager.chrome import ChromeDriverManager

        cookies_file = os.path.join(STORAGE_DIR, '.cookies', 'instagram_cookies.json')

        opts = Options()
        opts.add_argument('--headless')
        opts.add_argument('--no-sandbox')
        opts.add_argument('--disable-dev-shm-usage')
        opts.add_argument('user-agent=Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36')

        driver = webdriver.Chrome(service=Service(ChromeDriverManager().install()), options=opts)
        driver.get('https://www.instagram.com/')
        time.sleep(2)

        if os.path.exists(cookies_file):
            with open(cookies_file, 'r') as f:
                cookies = json.load(f)
            for c in cookies:
                try:
                    driver.add_cookie(c)
                except:
                    pass
            driver.get('https://www.instagram.com/')
            time.sleep(2)

        done = 0
        for pid, post in batch:
            url = post.get('url', '')
            if not url:
                continue
            try:
                driver.get(url)
                time.sleep(0.8)
                html = driver.page_source
                lat_m = re.search(r'"lat":(-?\d+\.\d+)', html)
                lng_m = re.search(r'"lng":(-?\d+\.\d+)', html)
                if lat_m and lng_m:
                    post['lat'] = float(lat_m.group(1))
                    post['lng'] = float(lng_m.group(1))
                    done += 1
                    print(f"  [{done}/{batch_size}] Coords: {post['lat']}, {post['lng']}")
                # Also try to get shortcode from page source
                if not post.get('shortcode'):
                    sc_m = re.search(r'"shortcode":"([^"]+)"', html)
                    if sc_m:
                        post['shortcode'] = sc_m.group(1)
                if not post.get('timestamp_unix'):
                    ts_m = re.search(r'"taken_at_timestamp":(\d{10})', html)
                    if ts_m:
                        post['timestamp_unix'] = int(ts_m.group(1))
            except Exception as e:
                print(f"  [SKIP] {e}")
                continue

        driver.quit()
        coords_after = sum(1 for p in by_id.values() if p.get('lat'))
        print(f"\nCoordenadas despues de backfill: {coords_after}")
    except ImportError:
        print("[AVISO] Selenium no disponible, saltando backfill.")
    except Exception as e:
        print(f"[AVISO] Error en backfill: {e}")

# Sort by fecha
sorted_posts = sorted(by_id.values(), key=lambda x: x.get('fecha', '9999-99-99'))

# Build timeline by month
timeline = OrderedDict()
for post in sorted_posts:
    periodo = post.get('fecha', '')[:7]
    if periodo not in timeline:
        timeline[periodo] = []
    timeline[periodo].append(post)

# Stats
categorias = {}
acciones = {}
relevantes = 0
for post in sorted_posts:
    cat = post.get('categoria', 'general')
    categorias[cat] = categorias.get(cat, 0) + 1
    acc = post.get('accion_detectada', '')
    if acc:
        acciones[acc] = acciones.get(acc, 0) + 1
    if post.get('relevancia_politica', 0) >= 5:
        relevantes += 1

# Build lista_acciones (relevant posts only)
lista_acciones = []
for post in sorted_posts:
    rel = post.get('relevancia_politica', 0)
    acc = post.get('accion_detectada', '')
    if rel >= 3 or acc:
        txt = post.get('texto', '')
        lista_acciones.append({
            'fecha': post.get('fecha', ''),
            'shortcode': post.get('shortcode') or '',
            'accion': acc or post.get('categoria', 'general'),
            'descripcion': txt[:200] + ('...' if len(txt) > 200 else ''),
            'categoria': post.get('categoria', 'general'),
            'relevancia': rel,
            'url': post.get('url', ''),
            'ubicacion': post.get('ubicacion'),
            'lat': post.get('lat'),
            'lng': post.get('lng'),
            'timestamp_unix': post.get('timestamp_unix'),
            'engagement': {
                'likes': post.get('likes'),
                'comentarios': post.get('comentarios')
            }
        })

master = {
    'concejal': {
        'username': 'edison_concejal',
        'nombre': 'Edison Alberto Giraldo',
        'ciudad': 'Cali, Valle del Cauca',
        'periodo': '2024-2027'
    },
    'estadisticas': {
        'total_publicaciones': len(sorted_posts),
        'periodos_cubiertos': list(timeline.keys()),
        'categorias': categorias,
        'acciones_detectadas': acciones,
        'publicaciones_relevantes': relevantes
    },
    'timeline': timeline,
    'lista_acciones': lista_acciones,
    '_metadata': {
        'fuentes_fusionadas': [os.path.basename(f) for f in VERSIONED_FILES],
        'fecha_generacion': datetime.now().strftime('%Y-%m-%d %H:%M:%S'),
        'total_timeline_posts': len(sorted_posts),
        'total_acciones': len(lista_acciones),
        'posts_con_coordenadas': sum(1 for p in sorted_posts if p.get('lat')),
        'posts_con_shortcode': sum(1 for p in sorted_posts if p.get('shortcode')),
        'since_date': SINCE_DATE
    }
}

with open(MASTER_OUT, 'w', encoding='utf-8') as f:
    json.dump(master, f, ensure_ascii=False, indent=2)

print(f"\n=== MAESTRO GUARDADO ===")
print(f"  Archivo: {MASTER_OUT}")
print(f"  Posts unicos: {len(sorted_posts)}")
print(f"  Acciones: {len(lista_acciones)}")
print(f"  Periodos: {list(timeline.keys())}")
print(f"  Rango: {sorted_posts[0]['fecha']} -> {sorted_posts[-1]['fecha']}")
print(f"  Con coordenadas: {sum(1 for p in sorted_posts if p.get('lat'))}")
print(f"  Con shortcode: {sum(1 for p in sorted_posts if p.get('shortcode'))}")
print(f"  Categorias: {categorias}")
print(f"  Acciones detectadas: {acciones}")
print(f"  Relevantes: {relevantes}")
