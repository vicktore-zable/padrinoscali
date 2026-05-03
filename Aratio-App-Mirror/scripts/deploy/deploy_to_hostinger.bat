@echo off
echo Starting Deployment to Hostinger...

:: Config files
curl.exe -T "mod_colab/.env.prod" ftp://212.1.208.241/mod_colab/.env --user "u156469157.aratio.mrmtech.net:sthLX6bJPoGh"
curl.exe -T "mod_colab/config/config.php" ftp://212.1.208.241/mod_colab/config/config.php --user "u156469157.aratio.mrmtech.net:sthLX6bJPoGh"

:: Core Root files
curl.exe -T "index.php" ftp://212.1.208.241/index.php --user "u156469157.aratio.mrmtech.net:sthLX6bJPoGh"
curl.exe -T ".htaccess" ftp://212.1.208.241/.htaccess --user "u156469157.aratio.mrmtech.net:sthLX6bJPoGh"
curl.exe -T "index.html" ftp://212.1.208.241/index.html --user "u156469157.aratio.mrmtech.net:sthLX6bJPoGh"

:: Application Logic
curl.exe -T "mod_colab/src/Models/Usuario.php" ftp://212.1.208.241/mod_colab/src/Models/Usuario.php --user "u156469157.aratio.mrmtech.net:sthLX6bJPoGh"
curl.exe -T "mod_colab/src/Controllers/AuthController.php" ftp://212.1.208.241/mod_colab/src/Controllers/AuthController.php --user "u156469157.aratio.mrmtech.net:sthLX6bJPoGh"
curl.exe -T "mod_colab/src/Controllers/PortalAuthController.php" ftp://212.1.208.241/mod_colab/src/Controllers/PortalAuthController.php --user "u156469157.aratio.mrmtech.net:sthLX6bJPoGh"

:: Views
curl.exe -T "mod_colab/src/Views/auth/login.php" ftp://212.1.208.241/mod_colab/src/Views/auth/login.php --user "u156469157.aratio.mrmtech.net:sthLX6bJPoGh"
curl.exe -T "mod_colab/src/Views/home/index.php" ftp://212.1.208.241/mod_colab/src/Views/home/index.php --user "u156469157.aratio.mrmtech.net:sthLX6bJPoGh"

echo Deployment Complete.
