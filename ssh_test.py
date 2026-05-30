#!/usr/bin/env python3
import os
import subprocess

# Password with special chars handled
password = 'EDG$v6xSHUWhjrxE'

# SSH command
cmd = [
    "ssh",
    "-o",
    "StrictHostKeyChecking=no",
    "-p",
    "65002",
    "u577647812@157.173.208.254",
    "ls -la /home/u577647812/domains/edisongiraldo.com/public_html/aratio/pages/ | head -5",
]

# Run with password via sshpass equivalent
result = subprocess.run(
    [
        "ssh",
        "-o",
        "StrictHostKeyChecking=no",
        "-p",
        "65002",
        "u577647812@157.173.208.254",
    ],
    input=password.encode() + b"\n",
    timeout=15,
)
print(result.stdout.decode() if result.returncode == 0 else result.stderr.decode())
