# -*- coding: utf-8 -*-
with open('admin_pages_content.txt', 'r', encoding='utf-8', errors='replace') as f:
    content = f.read()

import re
match = re.search(r'--- CONTENT OF grupos.php ---.*?<\?php foreach \(.*? as \$g\): \?>(.*?)<\?php endforeach; \?>', content, re.DOTALL)
if match:
    print(match.group(0))
else:
    print("Loop not found")
