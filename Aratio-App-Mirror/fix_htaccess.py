import paramiko

HOST = "157.173.208.254"
PORT = 65002
USER = "u577647812"


def main():
    with open(
        r"H:\Mi unidad/2025\5d\app\Multi-Campaign Management System\tmp_ssh_pass.txt",
        "r",
    ) as f:
        password = f.read().strip()

    client = paramiko.SSHClient()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    client.connect(HOST, port=PORT, username=USER, password=password, timeout=30)

    public_html = "/home/u577647812/domains/edisongiraldo.com/public_html"

    # Nuevo .htaccess que permite el incluye
    htaccess = """# Configuracion Aratio
Options -MultiViews
DirectoryIndex index.php index.html
RewriteEngine On
RewriteBase /

# Rutas
RewriteRule ^login/?$ index.php [L,QSA]
RewriteRule ^logout/?$ logout.php [L,QSA]
RewriteRule ^dashboard/?$ index.php [L,QSA]

# No bloquear includes
<FilesMatch "^(includes|DatabaseManager|Auth|CacheManager|RateLimiter)\.php$">
    Order allow,deny
    Allow from all
</FilesMatch>

# Proteger archivos sensibles
<FilesMatch "^\.">
    Order allow,deny
    Deny from all
</FilesMatch>

# PHP
php_value upload_max_filesize 10M
php_value post_max_size 10M
php_value max_execution_time 300

# Evitar listado
Options -Indexes
"""

    # Escribir nuevo .htaccess
    sftp = client.open_sftp()
    with sftp.file(public_html + "/.htaccess", "w") as f:
        f.write(htaccess.encode("utf-8"))
    sftp.close()

    print("OK - .htaccess actualizado")

    # Probar
    stdin, stdout, stderr = client.exec_command("curl -s http://127.0.0.1/ | head -10")
    print("\nPrueba local:")
    print(stdout.read().decode("utf-8", errors="replace"))

    client.close()


if __name__ == "__main__":
    main()
