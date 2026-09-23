"""
instagram_login.py — Abre Chrome para login manual en Instagram.
Guarda las cookies para que el scraper las reutilice.

Uso: python instagram_login.py
Chrome se abre, esperas 60 segundos para iniciar sesion,
las cookies se guardan automaticamente.
"""
import json
import os
import time
import sys
from selenium import webdriver
from selenium.webdriver.chrome.service import Service
from selenium.webdriver.chrome.options import Options
from selenium.webdriver.common.by import By
from webdriver_manager.chrome import ChromeDriverManager

STORAGE_DIR = os.path.join(os.path.dirname(os.path.abspath(__file__)), 'Aratio-App-Mirror', 'storage')
COOKIES_FILE = os.path.join(STORAGE_DIR, '.cookies', 'instagram_cookies.json')

os.makedirs(os.path.dirname(COOKIES_FILE), exist_ok=True)

chrome_options = Options()
chrome_options.add_argument("--no-sandbox")
chrome_options.add_argument("--disable-dev-shm-usage")
chrome_options.add_argument("--disable-gpu")
chrome_options.add_argument("--window-size=1280,900")
chrome_options.add_argument("--disable-blink-features=AutomationControlled")
chrome_options.add_experimental_option("excludeSwitches", ["enable-automation"])

# Usar perfil real de Chrome (con credenciales guardadas)
CHROME_USER_DATA = os.path.join(os.environ['LOCALAPPDATA'], 'Google', 'Chrome', 'User Data')
chrome_options.add_argument(f"--user-data-dir={CHROME_USER_DATA}")
chrome_options.add_argument("--profile-directory=Default")

service = Service(ChromeDriverManager().install())
driver = webdriver.Chrome(service=service, options=chrome_options)
driver.execute_script("Object.defineProperty(navigator, 'webdriver', {get: () => undefined})")

print("=" * 60)
print("INSTAGRAM LOGIN HELPER")
print("=" * 60)
print()
print("Chrome se ha abierto con Instagram.")
print("Tienes 90 segundos para:")
print("  1. Iniciar sesion en Instagram")
print("  2. Navegar a @edison_concejal para confirmar que carga")
print()
print("Las cookies se guardan automaticamente al finalizar.")
print()

driver.get("https://www.instagram.com/accounts/login/")
time.sleep(3)

WAIT_SECONDS = 90
print(f"Esperando {WAIT_SECONDS} segundos para que inicies sesion...")
print()

for remaining in range(WAIT_SECONDS, 0, -10):
    current_url = driver.current_url
    logged_in = 'login' not in current_url.lower()
    status = "SESION ACTIVA" if logged_in else "esperando login..."
    print(f"  [{remaining:3d}s] {status} | URL: {current_url[:60]}", end="\r")
    sys.stdout.flush()
    time.sleep(10)

print()
print()

# Check if logged in
current_url = driver.current_url
if 'login' in current_url.lower():
    print("TIMEOUT: No se detecto login. Intentando de todas formas...")
else:
    print("Login detectado!")

# Navigate to profile
driver.get("https://www.instagram.com/edison_concejal/")
time.sleep(5)

posts = driver.find_elements("css selector", "a[href*='/p/'], a[href*='/reel/']")
print(f"Posts visibles en perfil: {len(posts)}")

# Export cookies
cookies = driver.get_cookies()
with open(COOKIES_FILE, 'w', encoding='utf-8') as f:
    json.dump(cookies, f, ensure_ascii=False, indent=2)

print(f"Cookies guardadas: {COOKIES_FILE}")
print(f"Total cookies: {len(cookies)}")

# Save page source for debugging
debug_file = os.path.join(STORAGE_DIR, '.cookies', 'last_profile_page.html')
with open(debug_file, 'w', encoding='utf-8') as f:
    f.write(driver.page_source)
print(f"HTML debug guardado: {debug_file}")

driver.quit()

if len(posts) > 0:
    print("\nListo! Ahora ejecuta el scraper:")
    print('  python instagram_scraper.py --username edison_concejal --max-posts 5000 --monthly')
else:
    print("\nNo se detectaron posts. Verifica el HTML debug para entender que devolvio Instagram.")
