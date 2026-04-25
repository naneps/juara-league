
import re

with open('d:/PROJECT/juara-league/juara-league-web/app/components/tournaments/TournamentStageManager.vue', 'r', encoding='utf-8') as f:
    content = f.read()

# Simple tag matcher
stack = []
tags = re.findall(r'<(/?)([a-zA-Z0-9-]+)', content)

for is_close, name in tags:
    if name in ['img', 'br', 'hr', 'input', 'link', 'meta']: # void elements
        continue
    if is_close:
        if not stack:
            print(f"Extra closing tag: </{name}>")
        else:
            last = stack.pop()
            if last != name:
                print(f"Mismatch: <{last}> closed by </{name}>")
    else:
        stack.append(name)

if stack:
    print(f"Unclosed tags: {stack}")
