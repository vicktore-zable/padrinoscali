#!/bin/bash
# Script de conexion y despliegue - usa expect para el password

expect << 'EOF'
set timeout 30
spawn ssh -o StrictHostKeyChecking=no -p 65002 u577647812@157.173.208.254
expect "password:"
send "E=j\$`01yHi^?XfpoM@|CD\"5H4\r"
expect "~$"
send "echo OK\r"
expect "OK"
send "mkdir -p /domains/edisongiraldo.com/public_html/aratio\r"
expect "~$"
send "ls -la /domains/edisongiraldo.com/public_html/\r"
expect "~$"
send "exit\r"
expect eof
EOF