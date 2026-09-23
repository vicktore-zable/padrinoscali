# -*- coding: utf-8 -*-
with open('candidatos_full_content.txt', 'r', encoding='utf-8', errors='replace') as f:
    content = f.read()

import re
scripts = re.findall(r'<script>(.*?)</script>', content, re.DOTALL)
for s in scripts:
    print("--- Script detected ---")
    # This is rough, but we can check if it looks like valid JS
    # We can try to use a node.js syntax check if available, but for now just manual scan
    print(s)
