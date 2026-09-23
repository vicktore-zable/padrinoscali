# -*- coding: utf-8 -*-
with open('candidatos_content.txt', encoding='utf-8') as f:
    content = f.read()
    if '@click="eliminar(' in content:
        print("Found @click=\"eliminar(")
    else:
        print("NOT found @click=\"eliminar(")
    
    import re
    # Find all buttons with @click
    buttons = re.findall(r'<button[^>]+@click=[^>]+>.*?</button>', content, re.DOTALL)
    for b in buttons:
        print("--- Button found ---\n", b)
