# -*- coding: utf-8 -*-
with open('candidatos_full_content.txt', 'rb') as f:
    raw = f.read()
    # Skip BOM if present
    if raw.startswith(b'\xff\xfe'):
        content = raw.decode('utf-16')
    elif raw.startswith(b'\xef\xbb\xbf'):
        content = raw.decode('utf-8-sig')
    else:
        content = raw.decode('utf-8', errors='replace')

import re
match = re.search(r'<\?php foreach \(\$candidatos as \$c\): \?>(.*?)<\?php endforeach; \?>', content, re.DOTALL)
if match:
    print(match.group(0))
else:
    print("Loop not found")
