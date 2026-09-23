# -*- coding: utf-8 -*-
import paramiko, sys, io
sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8', errors='replace')

HOST="157.173.208.254"; PORT=65002; USER="u577647812"
PASSWORD='E=j$`01yHi^?XfpoM@|CD"5H4'
ARATIO="/home/u577647812/domains/edisongiraldo.com/public_html/aratio"

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(HOST, port=PORT, username=USER, password=PASSWORD, timeout=20)

new_config = """<?php
/**
 * ARATIO - Config Wrapper
 * Este archivo ahora es un puente hacia root_config.php para evitar duplicidad
 * y errores de rutas en la API.
 */
require_once __DIR__ . '/../root_config.php';
"""

sftp = client.open_sftp()
with sftp.open(f"{ARATIO}/config/config.php", "w") as f:
    f.write(new_config)
sftp.close()

print("config/config.php unificado con root_config.php.")
client.close()
