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
import io
from datetime import datetime
from pathlib import Path
from typing import List, Dict, Optional
from dataclasses import dataclass, asdict

# Forzar UTF-8 en consola Windows
if sys.platform == 'win32':
    sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8', errors='replace')
    sys.stderr = io.TextIOWrapper(sys.stderr.buffer, encoding='utf-8', errors='replace')

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

    def _update_status(self, status_file: Optional[str], status: str, message: str, current: int = 0, total: int = 0):
        if not status_file:
            return
        try:
            with open(status_file, 'w', encoding='utf-8') as f:
                json.dump({
                    'status': status,
                    'message': message,
                    'current': current,
                    'total': total,
                    'timestamp': time.time()
                }, f, ensure_ascii=False, indent=2)
        except Exception as e:
            print(f"[ERROR] No se pudo escribir status file: {e}")

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

    def scrape_selenium(self, max_posts: int = 100, since_date: Optional[str] = None, status_file: Optional[str] = None) -> List[Publicacion]:
        """Extrae publicaciones usando Selenium (método más robusto)"""
        self._update_status(status_file, 'running', 'Iniciando navegador Chrome...', 0, max_posts)
        if not self._init_selenium():
            self._update_status(status_file, 'failed', 'No se pudo iniciar Chrome Selenium.', 0, max_posts)
            return []

        publicaciones = []

        try:
            self._update_status(status_file, 'running', f'Navegando al perfil de @{self.username}...', 0, max_posts)
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
            scrolls = 0
            max_scrolls = 60 if since_date else 10
            height_stable_count = 0
            last_post_count = 0

            while scrolls < max_scrolls:
                self._update_status(status_file, 'running', f'Cargando publicaciones (scroll {scrolls+1}/{max_scrolls})...', 0, max_posts)
                
                # Scroll gradual: baja en increments para activar lazy loading
                for step in range(5):
                    pixels = 500 + (step * 400)
                    self.driver.execute_script(f"window.scrollTo(0, {pixels + scrolls * 2000});")
                    time.sleep(0.3)
                
                # Scroll completo al final
                self.driver.execute_script("window.scrollTo(0, document.body.scrollHeight);")
                time.sleep(self.delay)

                # Verificar cuantos posts hay ahora
                current_items = self.driver.find_elements(By.CSS_SELECTOR, "a[href*='/p/'], a[href*='/reel/']")
                current_post_count = len(current_items)

                if current_post_count > last_post_count:
                    height_stable_count = 0
                    last_post_count = current_post_count
                else:
                    height_stable_count += 1
                    # Si 3 scrolls seguidos sin nuevos posts, intentar scroll mas agresivo
                    if height_stable_count >= 3:
                        self.driver.execute_script("window.scrollTo(0, document.documentElement.scrollHeight);")
                        time.sleep(2)
                        self.driver.execute_script("window.scrollBy(0, -200); window.scrollBy(0, 200);")
                        time.sleep(1)

                scrolls += 1
                print(f"  Scroll {scrolls}/{max_scrolls} — Posts detectados: {current_post_count}")

                # Si ya tenemos suficientes y no crece, salir temprano
                if current_post_count >= max_posts and height_stable_count >= 5:
                    print(f"[INFO] Ya se detectaron {current_post_count} posts. Suficiente.")
                    break

                # Si despues de 10 scrolls sin cambio, salir
                if height_stable_count >= 10:
                    print(f"[INFO] Sin nuevos posts tras {height_stable_count} intentos. Finalizando scroll.")
                    break

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
                
                extracted_data.append({
                    'url': url,
                    'texto_alt': texto_alt,
                    'has_img': bool(texto_alt)
                })
                
                if len(extracted_data) >= max_posts:
                    break

            print(f"[INFO] Encontradas {len(extracted_data)} publicaciones en la cuadrícula")
            # Si no se encontraron con alt text, igual procesar las URLs
            if len(extracted_data) == 0 and len(seen_urls) > 0:
                for url in list(seen_urls)[:max_posts]:
                    extracted_data.append({'url': url, 'texto_alt': '', 'has_img': False})
                print(f"[INFO] Usando {len(extracted_data)} URLs sin alt text")
            self._update_status(status_file, 'running', f'Encontradas {len(extracted_data)} publicaciones. Analizando fechas...', 0, len(extracted_data))

            # Visitar cada publicación para intentar obtener la fecha exacta (si es posible)
            for i, data in enumerate(extracted_data, 1):
                post_url = data['url']
                try:
                    self._update_status(status_file, 'running', f'Analizando post {i} de {len(extracted_data)}...', i, len(extracted_data))
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

                    # Filtro por fecha (evitar posts anteriores a since_date)
                    # Omitimos romper el bucle para los primeros 3 posts (potenciales pinned posts)
                    if since_date and fecha != "fecha_desconocida" and i > 3:
                        if fecha < since_date:
                            print(f"[INFO] Post con fecha {fecha} es anterior a {since_date}. Finalizando extracción.")
                            break

                except Exception as e:
                    print(f"  [ERROR] Fallo en publicación {post_url}: {e}")
                    continue

        except Exception as e:
            self._update_status(status_file, 'failed', f'Error crítico: {str(e)}')
            raise e
        finally:
            self._cerrar_selenium()

        return publicaciones

    def scrape_api(self, max_posts: int = 50, since_date: Optional[str] = None, status_file: Optional[str] = None) -> List[Publicacion]:
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

    def _extraer_user_id(self, html: str) -> Optional[str]:
        """Extrae el user_id de la página HTML de Instagram"""
        match = re.search(r'"user_id":"(\d+)"', html)
        if match: return match.group(1)
        match = re.search(r'"id":"(\d+)"', html)
        if match: return match.group(1)
        match = re.search(r'window\._sharedData\s*=\s*({.+?});</script>', html, re.DOTALL)
        if match:
            try:
                data = json.loads(match.group(1))
                uid = (data.get('entry_data', {}).get('ProfilePage', [{}])[0]
                       .get('graphql', {}).get('user', {}).get('id'))
                if uid: return str(uid)
            except: pass
        match = re.search(r'window\.__INITIAL_STATE__\s*=\s*({.+?});</script>', html, re.DOTALL)
        if match:
            try:
                data = json.loads(match.group(1))
                uid = data.get('user', {}).get('id')
                if uid: return str(uid)
            except: pass
        return None

    def _extraer_query_hash(self, html: str) -> Optional[str]:
        known = [
            "42323d64886122307be10013ad2dcc44",
            "e769aa130647d2354c40ea6a439bfc08",
        ]
        for h in known:
            if h in html: return h
        return known[0]

    def _node_to_publicacion(self, node: Dict) -> Optional[Publicacion]:
        """Convierte un node de GraphQL a Publicacion"""
        try:
            timestamp = node.get('taken_at_timestamp', 0)
            fecha = datetime.fromtimestamp(timestamp).strftime('%Y-%m-%d') if timestamp else "fecha_desconocida"
            caption_edges = node.get('edge_media_to_caption', {}).get('edges', [])
            texto = caption_edges[0].get('node', {}).get('text', '') if caption_edges else ''
            shortcode = node.get('shortcode', '')
            tipo_map = {'GraphImage': 'foto', 'GraphVideo': 'video', 'GraphSidecar': 'carrusel'}
            analisis = self._analizar_texto(texto)
            return Publicacion(
                fecha=fecha,
                fecha_raw=str(timestamp),
                texto=texto,
                hashtags=self._extraer_hashtags(texto),
                menciones=self._extraer_menciones(texto),
                url=f"https://www.instagram.com/p/{shortcode}/",
                tipo=tipo_map.get(node.get('__typename', ''), 'foto'),
                likes=node.get('edge_liked_by', {}).get('count'),
                comentarios=node.get('edge_media_to_comment', {}).get('count'),
                categoria=analisis['categoria'],
                accion_detectada=analisis['accion_detectada'],
                relevancia_politica=analisis['relevancia_politica']
            )
        except: return None

    def fetch_all_posts_via_selenium(self, max_posts: int = 5000, status_file: Optional[str] = None) -> List[Publicacion]:
        """
        Usa Selenium para cargar el perfil, extraer user_id real,
        luego pagina la API GraphQL con requests usando cookies frescas.
        """
        self._update_status(status_file, 'running', 'Iniciando Selenium para obtener sesión real...', 0, max_posts)
        if not self._init_selenium():
            self._update_status(status_file, 'failed', 'No se pudo iniciar Chrome.')
            return []

        publicaciones = []
        user_id = None
        seen = set()

        try:
            print(f"[INFO] Navegando a {self.url}")
            self.driver.get(self.url)
            time.sleep(self.delay * 2)

            try:
                btn = self.driver.find_element(By.XPATH, "//button[contains(text(), 'Cerrar')] | //button[contains(text(), 'Close')]")
                btn.click(); time.sleep(2)
            except: pass

            # Extraer cookies del driver para la session de requests
            for c in self.driver.get_cookies():
                self.session.cookies.set(c['name'], c['value'], domain=c.get('domain', '.instagram.com'))

            page_source = self.driver.page_source
            user_id = self._extraer_user_id(page_source)
            query_hash = self._extraer_query_hash(page_source)

            if not user_id:
                print("[ERROR] No se pudo extraer user_id.")
                return []

            print(f"[INFO] User ID real: {user_id} | Query Hash: {query_hash}")
            self._cerrar_selenium()
            self.driver = None

            # Ahora usar requests para paginar
            self._update_status(status_file, 'running', f'User ID obtenido. Paginando API GraphQL...', 0, max_posts)
            api_headers = {
                'x-ig-app-id': '936619743392459',
                'x-requested-with': 'XMLHttpRequest',
                'referer': self.url,
            }

            # Probar el query_hash, si falla probar alternativos
            hash_options = [query_hash, "42323d64886122307be10013ad2dcc44", "e769aa130647d2354c40ea6a439bfc08"]
            working_hash = None

            for h in hash_options:
                test_vars = json.dumps({"id": user_id, "first": 12, "after": None}, separators=(',', ':'))
                test_url = f"https://www.instagram.com/graphql/query/?query_hash={h}&variables={test_vars}"
                tr = self.session.get(test_url, headers=api_headers)
                if tr.status_code == 200:
                    td = tr.json()
                    if td.get('data', {}).get('user', {}).get('edge_owner_to_timeline_media'):
                        working_hash = h
                        print(f"[INFO] Query hash funciona: {h}")
                        break
                    # También puede devolver en 'data.user.edge_web_feed_timeline'
                    if td.get('data', {}).get('user', {}).get('edge_web_feed_timeline'):
                        working_hash = h
                        print(f"[INFO] Query hash funciona (edge_web_feed): {h}")
                        break

            if not working_hash:
                print("[ERROR] Ningún query_hash funciona. Usando Selenium scrolling.")
                self._cerrar_selenium()
                return self.scrape_selenium_monthly(max_posts, status_file=status_file)

            query_hash = working_hash
            after = None
            has_next = True
            page = 0
            consecutive_empty = 0

            while has_next and len(publicaciones) < max_posts and consecutive_empty < 3:
                page += 1
                self._update_status(status_file, 'running',
                    f'Página {page} — {len(publicaciones)} posts obtenidos', 0, max_posts)
                print(f"  [API {page}] Solicitando página...", end=" ")

                variables = {"id": user_id, "first": 50, "after": after}
                url = f"https://www.instagram.com/graphql/query/?query_hash={query_hash}&variables={json.dumps(variables, separators=(',', ':'))}"
                resp = self.session.get(url, headers=api_headers, timeout=30)

                if resp.status_code != 200:
                    print(f"HTTP {resp.status_code}")
                    consecutive_empty += 1
                    time.sleep(2)
                    continue

                data = resp.json()
                user_data = data.get('data', {}).get('user', {})
                media = (user_data.get('edge_owner_to_timeline_media', {})
                         or user_data.get('edge_web_feed_timeline', {}))
                edges = media.get('edges', [])
                page_info = media.get('page_info', {})

                print(f"{len(edges)} posts")

                new_count = 0
                for edge in edges:
                    node = edge.get('node', {})
                    sc = node.get('shortcode', '')
                    if sc in seen: continue
                    seen.add(sc)
                    pub = self._node_to_publicacion(node)
                    if pub:
                        publicaciones.append(pub)
                        new_count += 1

                if new_count == 0:
                    consecutive_empty += 1
                else:
                    consecutive_empty = 0

                after = page_info.get('end_cursor')
                has_next = page_info.get('has_next_page', False)
                time.sleep(0.3)

            print(f"\n[INFO] Total: {len(publicaciones)} posts via API GraphQL")
            return publicaciones

        except Exception as e:
            print(f"[ERROR] fetch_all_posts_via_selenium: {e}")
            import traceback; traceback.print_exc()
            return []

        finally:
            if self.driver:
                self._cerrar_selenium()

    def scrape_selenium_monthly(self, max_posts: int = 500, since_date: str = "2024-01-01", status_file: Optional[str] = None) -> List[Publicacion]:
        """
        Scraping mes a mes con Selenium.
        Itera desde el mes actual hacia atrás hasta since_date.
        Cada mes hace un pass independiente.
        """
        all_posts = []
        seen = set()

        # Calcular meses desde since_date hasta hoy
        start = datetime.strptime(since_date, "%Y-%m-%d")
        end = datetime.now()
        months = []
        c = start
        while c < end:
            if c.month == 12:
                nxt = c.replace(year=c.year+1, month=1)
            else:
                nxt = c.replace(month=c.month+1)
            months.append((c.strftime("%Y-%m-%d"), nxt.strftime("%Y-%m-%d")))
            c = nxt

        total = len(months)
        self._update_status(status_file, 'running', f'Extracción mensual: {total} meses desde {since_date}', 0, max_posts)

        for idx, (mes_inicio, mes_fin) in enumerate(months, 1):
            mes_nombre = mes_inicio[:7]
            print(f"\n{'='*50}")
            print(f"[MES {idx}/{total}] {mes_nombre}")
            print(f"{'='*50}")

            self._update_status(status_file, 'running',
                f'Procesando {mes_nombre} ({idx}/{total}) — {len(all_posts)} posts totales', 0, max_posts)

            # Scraping para este mes: since=mes_inicio para cortar antes de este mes
            posts_mes = self.scrape_selenium(
                max_posts=200,
                since_date=mes_inicio,
                status_file=None
            )

            # Filtrar solo este mes
            nuevos = 0
            for p in posts_mes:
                if p.url in seen: continue
                # Solo posts de este mes
                if p.fecha >= mes_inicio and p.fecha < mes_fin:
                    all_posts.append(p)
                    seen.add(p.url)
                    nuevos += 1

            print(f"  -> {nuevos} posts nuevos para {mes_nombre} (total: {len(all_posts)})")

        print(f"\n[INFO] Total mensual: {len(all_posts)} posts")
        return all_posts

    def scrape(self, max_posts: int = 5000, prefer_selenium: bool = False, since_date: Optional[str] = None, status_file: Optional[str] = None, no_selenium_fallback: bool = False, monthly: bool = True) -> List[Publicacion]:
        """Método principal de extracción"""
        if monthly:
            print("[INFO] Modo: MES A MES desde 2024-01-01")
            print("[INFO] Fase 1: Intentando API GraphQL (Selenium + requests)...")
            posts = self.fetch_all_posts_via_selenium(max_posts, status_file)
            if len(posts) >= 50:
                print(f"[INFO] API obtenida: {len(posts)} posts. Organizando por mes...")
                return posts
            print(f"[INFO] API dio solo {len(posts)} posts. Fallback a Selenium mes a mes...")
            return self.scrape_selenium_monthly(max_posts, since_date or "2024-01-01", status_file)

        if not prefer_selenium:
            print("[INFO] Intentando extracción vía API...")
            posts = self.fetch_all_posts_via_selenium(max_posts, status_file)
            if posts: return posts
            if no_selenium_fallback: return []

        if SELENIUM_AVAILABLE and not no_selenium_fallback:
            return self.scrape_selenium(max_posts, since_date, status_file)
        return self.scrape_api(max_posts, since_date, status_file)


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
    parser.add_argument('--max-posts', type=int, default=300, help='Máximo de publicaciones a extraer')
    parser.add_argument('--output', default='timeline_concejal.json', help='Archivo JSON de salida')
    parser.add_argument('--markdown', default='timeline_concejal.md', help='Archivo Markdown de salida')
    parser.add_argument('--no-selenium', action='store_true', help='Forzar método requests (sin navegador)')
    parser.add_argument('--delay', type=float, default=3.0, help='Segundos de espera entre requests')
    parser.add_argument('--since-date', default='2024-01-01', help='Fecha de inicio de extracción (YYYY-MM-DD)')
    parser.add_argument('--status-file', default=None, help='Archivo de estado de sincronización')
    parser.add_argument('--monthly', action='store_true', default=True, help='Modo mes a mes (default: True)')

    args = parser.parse_args()

    print("=" * 60)
    print("INSTAGRAM POLITICAL TIMELINE SCRAPER")
    print("=" * 60)

    scraper = InstagramScraper(args.username, delay=args.delay)
    
    try:
        publicaciones = scraper.scrape(
            max_posts=args.max_posts,
            prefer_selenium=not args.no_selenium,
            since_date=args.since_date,
            status_file=args.status_file,
            no_selenium_fallback=args.no_selenium,
            monthly=args.monthly
        )
    except Exception as e:
        scraper._update_status(args.status_file, 'failed', f'Excepción: {str(e)}')
        sys.exit(1)

    if not publicaciones:
        scraper._update_status(args.status_file, 'failed', 'No se pudieron extraer publicaciones. Verifica que el perfil sea público y Selenium funcione.')
        print("[ERROR] No se pudieron extraer publicaciones.")
        print("Sugerencias:")
        print("1. Verifica que el perfil sea PÚBLICO")
        print("2. Instala Selenium: pip install selenium webdriver-manager")
        print("3. Aumenta el delay: --delay 5.0")
        print("4. Intenta con menos publicaciones: --max-posts 20")
        sys.exit(1)

    print(f"[OK] {len(publicaciones)} publicaciones extraídas")
    scraper._update_status(args.status_file, 'running', 'Generando timeline...', len(publicaciones), len(publicaciones))

    # Generar timeline
    timeline_data = generar_timeline(publicaciones)
    timeline_data['concejal']['username'] = args.username

    # Guardar JSON
    with open(args.output, 'w', encoding='utf-8') as f:
        json.dump(timeline_data, f, ensure_ascii=False, indent=2)
    print(f"[OK] JSON guardado en: {args.output}")

    # Exportar Markdown
    exportar_markdown(timeline_data, args.markdown)
    
    # Escribir estado final completado
    scraper._update_status(args.status_file, 'completed', 'Sincronización finalizada correctamente.', len(publicaciones), len(publicaciones))

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