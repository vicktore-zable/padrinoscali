#!/usr/bin/env python3
"""
Script para extraer publicaciones de Instagram de un perfil público
y generar un timeline de gestión política.

Requisitos:
    pip install requests beautifulsoup4 selenium webdriver-manager

Uso:
    python instagram_scraper.py --username edison_concejal --output timeline.json
"""

import argparse
import json
import re
import time
import sys
from datetime import datetime
from pathlib import Path
from typing import List, Dict, Optional
from dataclasses import dataclass, asdict

# Intentar importar dependencias con manejo de errores
try:
    import requests
    from bs4 import BeautifulSoup
except ImportError:
    print("[ERROR] Instala requests y beautifulsoup4: pip install requests beautifulsoup4")
    sys.exit(1)

try:
    from selenium import webdriver
    from selenium.webdriver.chrome.options import Options
    from selenium.webdriver.chrome.service import Service
    from selenium.webdriver.common.by import By
    from selenium.webdriver.support.ui import WebDriverWait
    from selenium.webdriver.support import expected_conditions as EC
    from selenium.common.exceptions import TimeoutException, NoSuchElementException
    from webdriver_manager.chrome import ChromeDriverManager
    SELENIUM_AVAILABLE = True
except ImportError:
    SELENIUM_AVAILABLE = False
    print("[AVISO] Selenium no disponible. Se usará método básico (puede fallar con Instagram).")
    print("Instala: pip install selenium webdriver-manager")


@dataclass
class Publicacion:
    """Representa una publicación de Instagram"""
    fecha: str
    fecha_raw: str
    texto: str
    hashtags: List[str]
    menciones: List[str]
    url: str
    tipo: str  # foto, video, carrusel, reel
    likes: Optional[int] = None
    comentarios: Optional[int] = None
    ubicacion: Optional[str] = None
    # Campos derivados del análisis
    categoria: str = "general"
    accion_detectada: str = ""
    relevancia_politica: int = 0  # 0-10


class InstagramScraper:
    """Scraper para perfiles públicos de Instagram"""

    def __init__(self, username: str, headless: bool = True, delay: float = 3.0):
        self.username = username
        self.url = f"https://www.instagram.com/{username}/"
        self.delay = delay
        self.session = requests.Session()
        self.session.headers.update({
            'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
            'Accept-Language': 'es-ES,es;q=0.9,en;q=0.8',
            'Referer': 'https://www.google.com/'
        })
        self.driver = None
        self.headless = headless

        # Palabras clave para clasificación política
        self.palabras_accion = {
            'obra': ['obra', 'construcción', 'pavimentación', 'calle', 'acera', 'puente', 'infraestructura'],
            'proyecto': ['proyecto', 'iniciativa', 'propuesta', 'proyecto de acuerdo', 'ley', 'decreto'],
            'denuncia': ['denuncia', 'irregularidad', 'corrupción', 'inconsistencia', 'investigación', 'fiscalización'],
            'reunion': ['reunión', 'sesión', 'plenaria', 'comisión', 'debate', 'votación', 'concejo'],
            'territorio': ['barrio', 'comuna', 'vereda', 'territorio', 'comunidad', 'vecinos', 'recorrido'],
            'social': ['salud', 'educación', 'deporte', 'cultura', 'juventud', 'adulto mayor', 'discapacidad'],
            'evento': ['evento', 'celebración', 'festival', 'encuentro', 'foro', 'conversatorio'],
            'gestion': ['gestión', 'logro', 'avance', 'resultado', 'cumplimiento', 'meta']
        }

    def _init_selenium(self):
        """Inicializa navegador Chrome con Selenium"""
        if not SELENIUM_AVAILABLE:
            return False

        chrome_options = Options()
        if self.headless:
            chrome_options.add_argument("--headless")
        chrome_options.add_argument("--no-sandbox")
        chrome_options.add_argument("--disable-dev-shm-usage")
        chrome_options.add_argument("--disable-gpu")
        chrome_options.add_argument("--window-size=1920,1080")
        chrome_options.add_argument("--disable-blink-features=AutomationControlled")
        chrome_options.add_experimental_option("excludeSwitches", ["enable-automation"])
        chrome_options.add_experimental_option('useAutomationExtension', False)

        try:
            service = Service(ChromeDriverManager().install())
            self.driver = webdriver.Chrome(service=service, options=chrome_options)
            self.driver.execute_script("Object.defineProperty(navigator, 'webdriver', {get: () => undefined})")
            return True
        except Exception as e:
            print(f"[ERROR] No se pudo iniciar Selenium: {e}")
            return False

    def _cerrar_selenium(self):
        """Cierra el navegador"""
        if self.driver:
            self.driver.quit()
            self.driver = None

    def _extraer_json_ld(self, html: str) -> Optional[Dict]:
        """Extrae datos estructurados JSON-LD de la página"""
        soup = BeautifulSoup(html, 'html.parser')
        scripts = soup.find_all('script', type='application/ld+json')
        for script in scripts:
            try:
                data = json.loads(script.string)
                if isinstance(data, dict) and data.get('@type') in ['ProfilePage', 'Person']:
                    return data
            except (json.JSONDecodeError, AttributeError):
                continue
        return None

    def _extraer_shared_data(self, html: str) -> Optional[Dict]:
        """Extrae _sharedData de los scripts de Instagram"""
        match = re.search(r'window\._sharedData\s*=\s*({.+?});</script>', html)
        if match:
            try:
                return json.loads(match.group(1))
            except json.JSONDecodeError:
                pass

        # Intentar con datos de Next.js / Relay
        match = re.search(r'<script[^>]*>\s*window\.__additionalDataLoaded\([\'"]extra[\'"]\s*,\s*({.+?})\);\s*</script>', html)
        if match:
            try:
                return json.loads(match.group(1))
            except json.JSONDecodeError:
                pass

        return None

    def _analizar_texto(self, texto: str) -> Dict:
        """Analiza el texto para detectar tipo de acción política"""
        texto_lower = texto.lower()
        categorias = []

        for categoria, palabras in self.palabras_accion.items():
            if any(palabra in texto_lower for palabra in palabras):
                categorias.append(categoria)

        # Determinar categoría principal
        categoria = categorias[0] if categorias else "general"

        # Detectar acción específica
        accion = ""
        if "denuncia" in texto_lower or "denuncio" in texto_lower:
            accion = "Denuncia pública"
        elif "proyecto de acuerdo" in texto_lower or "presente proyecto" in texto_lower:
            accion = "Presentación de proyecto"
        elif "sesión" in texto_lower or "plenaria" in texto_lower:
            accion = "Intervención en sesión"
        elif "reunión" in texto_lower and ("barrio" in texto_lower or "comunidad" in texto_lower):
            accion = "Reunión comunitaria"
        elif "obra" in texto_lower or "inauguración" in texto_lower:
            accion = "Seguimiento de obra"
        elif "gestión" in texto_lower or "gestioné" in texto_lower:
            accion = "Gestión administrativa"

        # Calcular relevancia política (0-10)
        relevancia = min(len(categorias) * 2, 10)
        if any(x in texto_lower for x in ['concejo', 'concejal', 'proyecto de acuerdo', 'denuncia']):
            relevancia = max(relevancia, 7)

        return {
            'categoria': categoria,
            'accion_detectada': accion,
            'relevancia_politica': relevancia,
            'categorias_todas': categorias
        }

    def _parsear_fecha(self, fecha_str: str) -> str:
        """Normaliza fechas de Instagram a formato estándar"""
        # Formatos comunes de Instagram
        formatos = [
            '%Y-%m-%dT%H:%M:%S.%fZ',
            '%Y-%m-%dT%H:%M:%SZ',
            '%Y-%m-%d %H:%M:%S',
            '%d de %B de %Y',
            '%B %d, %Y',
        ]

        for fmt in formatos:
            try:
                dt = datetime.strptime(fecha_str, fmt)
                return dt.strftime('%Y-%m-%d')
            except ValueError:
                continue

        # Si no coincide, devolver la fecha original
        return fecha_str[:10] if len(fecha_str) > 10 else fecha_str

    def _extraer_hashtags(self, texto: str) -> List[str]:
        """Extrae hashtags del texto"""
        return re.findall(r'#(\w+)', texto)

    def _extraer_menciones(self, texto: str) -> List[str]:
        """Extrae menciones @ del texto"""
        return re.findall(r'@(\w+)', texto)

    def scrape_selenium(self, max_posts: int = 100) -> List[Publicacion]:
        """Extrae publicaciones usando Selenium (método más robusto)"""
        if not self._init_selenium():
            return []

        publicaciones = []

        try:
            print(f"[INFO] Navegando a {self.url}")
            self.driver.get(self.url)
            time.sleep(self.delay * 2) # Más tiempo para la carga inicial

            # Intentar cerrar popup de login si aparece
            try:
                cerrar_btn = self.driver.find_element(By.XPATH, "//button[contains(text(), 'Cerrar')] | //button[contains(text(), 'Close')]")
                cerrar_btn.click()
                time.sleep(2)
            except:
                pass

            # Scroll para cargar publicaciones
            print(f"[INFO] Cargando publicaciones (scroll)...")
            last_height = self.driver.execute_script("return document.body.scrollHeight")
            scrolls = 0
            max_scrolls = 3 # Suficiente para 10-20 posts

            while scrolls < max_scrolls:
                self.driver.execute_script("window.scrollTo(0, document.body.scrollHeight);")
                time.sleep(self.delay)
                new_height = self.driver.execute_script("return document.body.scrollHeight")
                if new_height == last_height:
                    break
                last_height = new_height
                scrolls += 1
                print(f"  Scroll {scrolls}/{max_scrolls}...")

            # Extraer enlaces y texto alternativo (caption parcial) de la cuadrícula
            print("[INFO] Extrayendo datos de la cuadrícula...")
            grid_items = self.driver.find_elements(By.CSS_SELECTOR, "a[href*='/p/'], a[href*='/reel/']")
            
            extracted_data = []
            seen_urls = set()
            
            for item in grid_items:
                url = item.get_attribute('href')
                if not url or url in seen_urls:
                    continue
                seen_urls.add(url)
                
                # Intentar obtener el texto del atributo alt de la imagen
                texto_alt = ""
                try:
                    img = item.find_element(By.TAG_NAME, "img")
                    texto_alt = img.get_attribute('alt') or ""
                except:
                    pass
                
                if texto_alt:
                    extracted_data.append({
                        'url': url,
                        'texto_alt': texto_alt
                    })
                
                if len(extracted_data) >= max_posts:
                    break

            print(f"[INFO] Encontradas {len(extracted_data)} publicaciones en la cuadrícula")

            # Visitar cada publicación para intentar obtener la fecha exacta (si es posible)
            for i, data in enumerate(extracted_data, 1):
                post_url = data['url']
                try:
                    print(f"  [{i}/{len(extracted_data)}] Analizando: {post_url}")
                    
                    # Usar el texto del alt como base
                    texto = data['texto_alt']
                    fecha = "fecha_desconocida"
                    fecha_elem = None
                    
                    # Intentar visitar para fecha y likes, pero con manejo de error/login
                    self.driver.get(post_url)
                    time.sleep(self.delay / 2) # Delay más corto para fecha
                    
                    actual_url = self.driver.current_url
                    if "login" in actual_url:
                        print(f"      [INFO] Login detectado, usando datos de cuadrícula para este post.")
                    else:
                        fecha_elem = None
                        try:
                            # Múltiples selectores para fecha
                            selectors = [
                                "time",
                                "a time",
                                "[datetime]",
                                "span.x1rg5asf",
                                "._a9ze",
                                "._a9zd",
                                "[title]"
                            ]
                            for sel in selectors:
                                elems = self.driver.find_elements(By.CSS_SELECTOR, sel)
                                for elem in elems:
                                    attr = elem.get_attribute('datetime') or elem.get_attribute('title')
                                    if attr:
                                        fecha_elem = attr
                                        break
                                if fecha_elem:
                                    break
                        except:
                            pass

                        fecha = self._parsear_fecha(fecha_elem) if fecha_elem else "fecha_desconocida"
                        fecha_raw = fecha_elem or ""

                        # Intentar obtener texto completo si el del alt es muy corto o incompleto
                        text_selectors = [
                            "div.x193iq5w.xeuugli.x13faqbe.x1vvkbs.xt0psk2.x1i0vuye.xvs91rp.xo1l8bm.x5n08af.x126k92a",
                            "div._a9zs", "span._a9zs", "h1", "article span"
                        ]
                        try:
                            for sel in text_selectors:
                                elems = self.driver.find_elements(By.CSS_SELECTOR, sel)
                                for elem in elems:
                                    t = elem.text.strip()
                                    if len(t) > len(texto) and not t.startswith('@'):
                                        texto = t
                                        break
                                if texto and len(texto) > 100:
                                    break
                        except:
                            pass

                    # Extraer likes
                    likes = None
                    try:
                        likes_selectors = [
                            "div.x1i10hfl.xjbqb8w.x6um56n.x1n2onr6.x1npa8yc.x403mre.x13fuv20.xu71vkh.x134z9hs.x193iq5w.x1pn7m5v.x17z2hu9.x1yc453h.x10l6tqk.x13vifvy.x17qophe.xyy8z9k.x10w6t97.x16n37ib.x108nvyi.x138676.x1v83x69.x4u8f5u.x17r8fjc.xf8064l.x1p5jdqy.x1npxr68.x1120s87.x19f5jgu.x1vun87.x1iorvi3.xexx8yu.x4u8un3.x11nj6px.x78zum5.x1q0g604.x10l44ad",
                            "span.x193iq5w.xeuugli.x13faqbe.x1vvkbs.xt0psk2.x1i0vuye.xvs91rp.xo1l8bm.x5n08af.x126k92a",
                            "section span",
                            "div._aacl._aaco._aacw._aacx._aada._aade span"
                        ]
                        for sel in likes_selectors:
                            elems = self.driver.find_elements(By.CSS_SELECTOR, sel)
                            for elem in elems:
                                t = elem.text.lower()
                                if 'like' in t or 'gusta' in t:
                                    likes_match = re.search(r'(\d[\d,.]*)', t.replace(',', ''))
                                    if likes_match:
                                        likes = int(likes_match.group(1))
                                        break
                            if likes:
                                break
                    except:
                        pass

                    # Extraer comentarios
                    comentarios = None
                    try:
                        comm_text = self.driver.find_element(By.XPATH, "//span[contains(text(), 'comment')] | //span[contains(text(), 'comentario')]").text
                        comm_match = re.search(r'(\d[\d,.]*)', comm_text.replace(',', ''))
                        if comm_match:
                            comentarios = int(comm_match.group(1))
                    except:
                        pass

                    # Determinar tipo
                    tipo = "foto"
                    try:
                        if self.driver.find_elements(By.CSS_SELECTOR, "video"):
                            tipo = "video"
                        elif self.driver.find_elements(By.CSS_SELECTOR, "[class*='Carousel']"):
                            tipo = "carrusel"
                        elif "reel" in post_url:
                            tipo = "reel"
                    except:
                        pass

                    # Analizar contenido político
                    analisis = self._analizar_texto(texto)

                    pub = Publicacion(
                        fecha=fecha,
                        fecha_raw=fecha_raw,
                        texto=texto,
                        hashtags=self._extraer_hashtags(texto),
                        menciones=self._extraer_menciones(texto),
                        url=post_url,
                        tipo=tipo,
                        likes=likes,
                        comentarios=comentarios,
                        categoria=analisis['categoria'],
                        accion_detectada=analisis['accion_detectada'],
                        relevancia_politica=analisis['relevancia_politica']
                    )
                    publicaciones.append(pub)

                except Exception as e:
                    print(f"  [ERROR] Fallo en publicación {post_url}: {e}")
                    continue

        finally:
            self._cerrar_selenium()

        return publicaciones

    def scrape_api(self, max_posts: int = 50) -> List[Publicacion]:
        """Método alternativo usando requests (puede ser bloqueado por Instagram)"""
        publicaciones = []

        try:
            print(f"[INFO] Intentando extracción vía API/requests...")
            response = self.session.get(self.url)

            if response.status_code != 200:
                print(f"[ERROR] HTTP {response.status_code}. Instagram puede estar bloqueando requests.")
                return []

            html = response.text

            # Intentar extraer datos estructurados
            shared_data = self._extraer_shared_data(html)

            if shared_data:
                print("[INFO] Datos estructurados encontrados")
                # Procesar datos de _sharedData
                user_data = shared_data.get('entry_data', {}).get('ProfilePage', [{}])[0].get('graphql', {}).get('user', {})
                edges = user_data.get('edge_owner_to_timeline_media', {}).get('edges', [])

                for edge in edges[:max_posts]:
                    node = edge.get('node', {})

                    # Fecha
                    timestamp = node.get('taken_at_timestamp', 0)
                    fecha = datetime.fromtimestamp(timestamp).strftime('%Y-%m-%d') if timestamp else ""

                    # Texto
                    caption_edges = node.get('edge_media_to_caption', {}).get('edges', [])
                    texto = caption_edges[0].get('node', {}).get('text', '') if caption_edges else ''

                    # URL
                    shortcode = node.get('shortcode', '')
                    url = f"https://www.instagram.com/p/{shortcode}/" if shortcode else ''

                    # Tipo
                    tipo_map = {'GraphImage': 'foto', 'GraphVideo': 'video', 'GraphSidecar': 'carrusel'}
                    tipo = tipo_map.get(node.get('__typename', ''), 'foto')

                    # Stats
                    likes = node.get('edge_liked_by', {}).get('count')
                    comentarios = node.get('edge_media_to_comment', {}).get('count')

                    # Análisis
                    analisis = self._analizar_texto(texto)

                    pub = Publicacion(
                        fecha=fecha,
                        fecha_raw=str(timestamp),
                        texto=texto,
                        hashtags=self._extraer_hashtags(texto),
                        menciones=self._extraer_menciones(texto),
                        url=url,
                        tipo=tipo,
                        likes=likes,
                        comentarios=comentarios,
                        categoria=analisis['categoria'],
                        accion_detectada=analisis['accion_detectada'],
                        relevancia_politica=analisis['relevancia_politica']
                    )
                    publicaciones.append(pub)
            else:
                print("[AVISO] No se encontraron datos estructurados. Instagram ha cambiado su frontend.")

        except Exception as e:
            print(f"[ERROR] {e}")

        return publicaciones

    def scrape(self, max_posts: int = 100, prefer_selenium: bool = True) -> List[Publicacion]:
        """Método principal de extracción"""
        if prefer_selenium and SELENIUM_AVAILABLE:
            print("[INFO] Usando Selenium (método recomendado)")
            return self.scrape_selenium(max_posts)
        else:
            print("[INFO] Usando método API/requests")
            return self.scrape_api(max_posts)


def generar_timeline(publicaciones: List[Publicacion]) -> Dict:
    """Genera estructura de timeline organizada"""

    # Ordenar por fecha
    pubs_sorted = sorted(publicaciones, key=lambda x: x.fecha if x.fecha != "fecha_desconocida" else "9999-99-99")

    # Agrupar por mes/año
    timeline = {}
    for pub in pubs_sorted:
        if len(pub.fecha) >= 7:
            periodo = pub.fecha[:7]  # YYYY-MM
        else:
            periodo = "desconocido"

        if periodo not in timeline:
            timeline[periodo] = []

        timeline[periodo].append(asdict(pub))

    # Estadísticas
    stats = {
        'total_publicaciones': len(publicaciones),
        'periodos_cubiertos': list(timeline.keys()),
        'categorias': {},
        'acciones_detectadas': {},
        'publicaciones_relevantes': sum(1 for p in publicaciones if p.relevancia_politica >= 5)
    }

    for pub in publicaciones:
        stats['categorias'][pub.categoria] = stats['categorias'].get(pub.categoria, 0) + 1
        if pub.accion_detectada:
            stats['acciones_detectadas'][pub.accion_detectada] = stats['acciones_detectadas'].get(pub.accion_detectada, 0) + 1

    return {
        'concejal': {
            'username': '',
            'nombre': 'Edison Alberto Giraldo',
            'ciudad': 'Cali, Valle del Cauca',
            'periodo': '2024-2027'
        },
        'estadisticas': stats,
        'timeline': timeline,
        'lista_acciones': [
            {
                'fecha': pub.fecha,
                'accion': pub.accion_detectada or pub.categoria,
                'descripcion': pub.texto[:200] + ('...' if len(pub.texto) > 200 else ''),
                'categoria': pub.categoria,
                'relevancia': pub.relevancia_politica,
                'url': pub.url,
                'engagement': {
                    'likes': pub.likes,
                    'comentarios': pub.comentarios
                }
            }
            for pub in pubs_sorted if pub.relevancia_politica >= 3 or pub.accion_detectada
        ]
    }


def exportar_markdown(data: Dict, output_path: str):
    """Exporta el timeline a Markdown"""
    lines = []
    lines.append(f"# Timeline de Gestión: {data['concejal']['nombre']}")
    lines.append(f"**Ciudad:** {data['concejal']['ciudad']}  ")
    lines.append(f"**Período:** {data['concejal']['periodo']}  ")
    lines.append(f"**Total publicaciones analizadas:** {data['estadisticas']['total_publicaciones']}  ")
    lines.append(f"**Publicaciones políticamente relevantes:** {data['estadisticas']['publicaciones_relevantes']}  ")
    lines.append("")

    # Resumen de categorías
    lines.append("## Resumen por Categorías")
    for cat, count in sorted(data['estadisticas']['categorias'].items(), key=lambda x: -x[1]):
        lines.append(f"- **{cat.capitalize()}:** {count} publicaciones")
    lines.append("")

    # Resumen de acciones
    if data['estadisticas']['acciones_detectadas']:
        lines.append("## Acciones Detectadas")
        for acc, count in sorted(data['estadisticas']['acciones_detectadas'].items(), key=lambda x: -x[1]):
            lines.append(f"- **{acc}:** {count} veces")
        lines.append("")

    # Timeline detallado
    lines.append("## Timeline Detallado")
    for periodo in sorted(data['timeline'].keys()):
        if periodo == "desconocido":
            continue

        # Formatear período
        try:
            dt = datetime.strptime(periodo, '%Y-%m')
            periodo_str = dt.strftime('%B %Y').capitalize()
        except:
            periodo_str = periodo

        lines.append(f"### {periodo_str}")

        for pub in data['timeline'][periodo]:
            lines.append(f"**{pub['fecha']}** | {pub['accion_detectada'] or pub['categoria']} | Relevancia: {pub['relevancia_politica']}/10")
            lines.append(f"> {pub['texto'][:300]}{'...' if len(pub['texto']) > 300 else ''}")
            lines.append(f"🔗 [Ver publicación]({pub['url']})")
            if pub['likes'] or pub['comentarios']:
                lines.append(f"❤️ {pub['likes'] or '?'} | 💬 {pub['comentarios'] or '?'}")
            lines.append("")

    # Lista de acciones
    lines.append("## Lista de Acciones (Resumen Ejecutivo)")
    lines.append("| Fecha | Acción | Categoría | Relevancia | URL |")
    lines.append("|-------|--------|-----------|------------|-----|")
    for acc in data['lista_acciones']:
        lines.append(f"| {acc['fecha']} | {acc['accion']} | {acc['categoria']} | {acc['relevancia']}/10 | [Link]({acc['url']}) |")

    with open(output_path, 'w', encoding='utf-8') as f:
        f.write('\n'.join(lines))

    print(f"[OK] Markdown exportado a: {output_path}")


def main():
    parser = argparse.ArgumentParser(description='Scraper de Instagram para timeline político')
    parser.add_argument('--username', default='edison_concejal', help='Usuario de Instagram')
    parser.add_argument('--max-posts', type=int, default=100, help='Máximo de publicaciones a extraer')
    parser.add_argument('--output', default='timeline_concejal.json', help='Archivo JSON de salida')
    parser.add_argument('--markdown', default='timeline_concejal.md', help='Archivo Markdown de salida')
    parser.add_argument('--no-selenium', action='store_true', help='Forzar método requests (sin navegador)')
    parser.add_argument('--delay', type=float, default=3.0, help='Segundos de espera entre requests')

    args = parser.parse_args()

    print("=" * 60)
    print("INSTAGRAM POLITICAL TIMELINE SCRAPER")
    print("=" * 60)

    scraper = InstagramScraper(args.username, delay=args.delay)
    publicaciones = scraper.scrape(
        max_posts=args.max_posts,
        prefer_selenium=not args.no_selenium
    )

    if not publicaciones:
        print("[ERROR] No se pudieron extraer publicaciones.")
        print("Sugerencias:")
        print("1. Verifica que el perfil sea PÚBLICO")
        print("2. Instala Selenium: pip install selenium webdriver-manager")
        print("3. Aumenta el delay: --delay 5.0")
        print("4. Intenta con menos publicaciones: --max-posts 20")
        sys.exit(1)

    print(f"[OK] {len(publicaciones)} publicaciones extraídas")

    # Generar timeline
    timeline_data = generar_timeline(publicaciones)
    timeline_data['concejal']['username'] = args.username

    # Guardar JSON
    with open(args.output, 'w', encoding='utf-8') as f:
        json.dump(timeline_data, f, ensure_ascii=False, indent=2)
    print(f"[OK] JSON guardado en: {args.output}")

    # Exportar Markdown
    exportar_markdown(timeline_data, args.markdown)

    # Resumen en consola
    print("\n" + "=" * 60)
    print("RESUMEN")
    print("=" * 60)
    print(f"Publicaciones analizadas: {timeline_data['estadisticas']['total_publicaciones']}")
    print(f"Relevantes políticamente: {timeline_data['estadisticas']['publicaciones_relevantes']}")
    print(f"Períodos: {', '.join(timeline_data['estadisticas']['periodos_cubiertos'][:5])}...")
    print(f"Acciones detectadas: {len(timeline_data['estadisticas']['acciones_detectadas'])}")
    print(f"\nArchivos generados:")
    print(f"  - JSON: {args.output}")
    print(f"  - Markdown: {args.markdown}")


if __name__ == '__main__':
    main()