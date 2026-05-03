#!/bin/bash

# Script de Verificación Post-Deployment
# Verifica que la aplicación esté funcionando correctamente en producción

echo "========================================"
echo "VERIFICACIÓN POST-DEPLOYMENT"
echo "Sistema A Ratio - Colaboradores"
echo "========================================"
echo ""

# Colores para output
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Contador de tests
TESTS_PASSED=0
TESTS_FAILED=0

# URL base
BASE_URL="https://aratio.mrmtech.net/mod_colab"

# Función para verificar HTTP status
check_url() {
    local url=$1
    local description=$2
    local expected_code=${3:-200}

    echo -n "Verificando $description... "

    status_code=$(curl -s -o /dev/null -w "%{http_code}" -L "$url")

    if [ "$status_code" -eq "$expected_code" ]; then
        echo -e "${GREEN}✓ OK${NC} (HTTP $status_code)"
        ((TESTS_PASSED++))
        return 0
    else
        echo -e "${RED}✗ FAIL${NC} (HTTP $status_code, esperado $expected_code)"
        ((TESTS_FAILED++))
        return 1
    fi
}

# Función para verificar que el contenido contiene texto
check_content() {
    local url=$1
    local search_text=$2
    local description=$3

    echo -n "Verificando $description... "

    content=$(curl -s -L "$url")

    if echo "$content" | grep -q "$search_text"; then
        echo -e "${GREEN}✓ OK${NC}"
        ((TESTS_PASSED++))
        return 0
    else
        echo -e "${RED}✗ FAIL${NC} (texto no encontrado: '$search_text')"
        ((TESTS_FAILED++))
        return 1
    fi
}

# Función para verificar conexión SSH
check_ssh() {
    echo -n "Verificando conexión SSH... "

    if ssh -o ConnectTimeout=5 -o StrictHostKeyChecking=no u156469157@212.1.208.241 "echo 'OK'" 2>/dev/null | grep -q "OK"; then
        echo -e "${GREEN}✓ OK${NC}"
        ((TESTS_PASSED++))
        return 0
    else
        echo -e "${RED}✗ FAIL${NC}"
        ((TESTS_FAILED++))
        return 1
    fi
}

# Función para verificar MySQL
check_mysql() {
    echo -n "Verificando conexión MySQL... "

    result=$(ssh -o StrictHostKeyChecking=no u156469157@212.1.208.241 \
        "mysql -h auth-db690.hstgr.io -u u156469157_aratio -p15zxCeBbvgsR u156469157_aratio -e 'SELECT COUNT(*) FROM colaboradores;' 2>/dev/null" | tail -1)

    if [ ! -z "$result" ] && [ "$result" -gt 0 ]; then
        echo -e "${GREEN}✓ OK${NC} ($result colaboradores en BD)"
        ((TESTS_PASSED++))
        return 0
    else
        echo -e "${RED}✗ FAIL${NC}"
        ((TESTS_FAILED++))
        return 1
    fi
}

# Función para verificar permisos
check_permissions() {
    echo -n "Verificando permisos de directorios... "

    perms=$(ssh -o StrictHostKeyChecking=no u156469157@212.1.208.241 \
        "cd /home/domains/aratio.mrmtech.net/public_html/mod_colab && ls -ld storage 2>/dev/null" | awk '{print $1}')

    if echo "$perms" | grep -q "drwxrwxr-x"; then
        echo -e "${GREEN}✓ OK${NC} ($perms)"
        ((TESTS_PASSED++))
        return 0
    else
        echo -e "${YELLOW}⚠ WARNING${NC} ($perms - debe ser drwxrwxr-x)"
        return 1
    fi
}

echo "=== VERIFICACIONES DE CONECTIVIDAD ==="
echo ""

# Test SSH
check_ssh

# Test MySQL
check_mysql

echo ""
echo "=== VERIFICACIONES DE PERMISOS ==="
echo ""

# Test permisos
check_permissions

echo ""
echo "=== VERIFICACIONES DE APLICACIÓN ==="
echo ""

# Test 1: URL principal redirige a login
check_url "$BASE_URL" "URL principal" 200

# Test 2: Página de login existe
check_url "$BASE_URL/login" "Página de login" 200

# Test 3: Login contiene formulario
check_content "$BASE_URL/login" "csrf_token" "CSRF token en login"

# Test 4: Login contiene campos
check_content "$BASE_URL/login" "usuario" "Campo usuario en login"

# Test 5: Assets CSS cargan
check_url "$BASE_URL/public/assets/css/styles.css" "CSS principal" 200

# Test 6: Assets JS cargan
check_url "$BASE_URL/public/assets/js/app.js" "JavaScript principal" 200

echo ""
echo "=== VERIFICACIONES DE API ==="
echo ""

# Test 7: API de departamentos (requiere auth, esperamos 302 redirect)
check_url "$BASE_URL/territorios/departamentos" "API Departamentos" 302

echo ""
echo "=== VERIFICACIONES DE SEGURIDAD ==="
echo ""

# Test 8: HTTPS está activo
echo -n "Verificando HTTPS... "
if curl -s -I "https://aratio.mrmtech.net" | grep -q "HTTP/2 200\|HTTP/1.1 200"; then
    echo -e "${GREEN}✓ OK${NC}"
    ((TESTS_PASSED++))
else
    echo -e "${RED}✗ FAIL${NC}"
    ((TESTS_FAILED++))
fi

# Test 9: Headers de seguridad
echo -n "Verificando headers de seguridad... "
headers=$(curl -s -I "$BASE_URL/login")

if echo "$headers" | grep -q "X-Frame-Options"; then
    echo -e "${GREEN}✓ OK${NC} (X-Frame-Options presente)"
    ((TESTS_PASSED++))
else
    echo -e "${YELLOW}⚠ WARNING${NC} (X-Frame-Options no encontrado)"
fi

echo ""
echo "=== VERIFICACIONES DE ARCHIVOS ==="
echo ""

# Test 10: .env existe y tiene permisos correctos
echo -n "Verificando .env... "
env_perms=$(ssh -o StrictHostKeyChecking=no u156469157@212.1.208.241 \
    "cd /home/domains/aratio.mrmtech.net/public_html/mod_colab && ls -l .env 2>/dev/null" | awk '{print $1}')

if echo "$env_perms" | grep -q "rw-------"; then
    echo -e "${GREEN}✓ OK${NC} ($env_perms)"
    ((TESTS_PASSED++))
else
    echo -e "${YELLOW}⚠ WARNING${NC} ($env_perms - debe ser -rw-------)"
fi

# Test 11: Directorio storage existe
echo -n "Verificando directorio storage... "
if ssh -o StrictHostKeyChecking=no u156469157@212.1.208.241 \
    "[ -d /home/domains/aratio.mrmtech.net/public_html/mod_colab/storage ]" 2>/dev/null; then
    echo -e "${GREEN}✓ OK${NC}"
    ((TESTS_PASSED++))
else
    echo -e "${RED}✗ FAIL${NC}"
    ((TESTS_FAILED++))
fi

# Test 12: Logs directory existe
echo -n "Verificando directorio logs... "
if ssh -o StrictHostKeyChecking=no u156469157@212.1.208.241 \
    "[ -d /home/domains/aratio.mrmtech.net/public_html/mod_colab/storage/logs ]" 2>/dev/null; then
    echo -e "${GREEN}✓ OK${NC}"
    ((TESTS_PASSED++))
else
    echo -e "${RED}✗ FAIL${NC}"
    ((TESTS_FAILED++))
fi

echo ""
echo "========================================"
echo "RESUMEN DE VERIFICACIONES"
echo "========================================"
echo ""
echo -e "Tests pasados: ${GREEN}$TESTS_PASSED${NC}"
echo -e "Tests fallidos: ${RED}$TESTS_FAILED${NC}"
echo ""

TOTAL=$((TESTS_PASSED + TESTS_FAILED))
PERCENTAGE=$((TESTS_PASSED * 100 / TOTAL))

echo "Porcentaje de éxito: $PERCENTAGE%"
echo ""

if [ $TESTS_FAILED -eq 0 ]; then
    echo -e "${GREEN}✓ DEPLOYMENT EXITOSO${NC}"
    echo "La aplicación está funcionando correctamente."
    exit 0
elif [ $PERCENTAGE -ge 80 ]; then
    echo -e "${YELLOW}⚠ DEPLOYMENT PARCIALMENTE EXITOSO${NC}"
    echo "La aplicación funciona pero hay algunos problemas menores."
    echo "Revisa los tests fallidos arriba."
    exit 1
else
    echo -e "${RED}✗ DEPLOYMENT CON PROBLEMAS${NC}"
    echo "Hay problemas significativos que deben resolverse."
    echo "Revisa la guía de troubleshooting en DEPLOYMENT_GUIDE.md"
    exit 2
fi
