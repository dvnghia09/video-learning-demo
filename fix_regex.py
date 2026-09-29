import re

with open('/Users/nghiadv/Projects/video-learning-demo/courses.html', 'r', encoding='utf-8') as f:
    content = f.read()

# Fix the broken escaped quote first:
content = content.replace("window.location.href=\\'video-player.html\\'", "window.location.href='video-player.html'")

# Now match all rows. A row starts with `<div` and contains `flex items-center justify-between p-3`
# We'll split the content by `<div class="flex items-center justify-between p-3` (and its variants)
parts = re.split(r'(<div [^>]*class="[^"]*flex items-center justify-between p-3[^"]*"[^>]*>)', content)

# parts is: [prefix, match1, inner1, match2, inner2, ...]
# actually re.split with a capture group keeps the matched delimiters.
# Let's iterate through parts
for i in range(1, len(parts), 2):
    row_start = parts[i]
    row_inner = parts[i+1]
    
    # We need to find where the row ends. But this split doesn't give us the end.
    pass

# Let's use a simpler approach. 
# We know the rows look like this:
# <div ... class="... flex items-center justify-between p-3 bg-white ...">
#   <div class="flex items-center">
#      ...
#   </div>
#   <a ...>...</a>
# </div>

# Let's clean up all onclicks and cursor-pointers from the div first.
content = re.sub(r'<div onclick="[^"]*" class="([^"]*flex items-center justify-between p-3[^"]*)"', r'<div class="\1"', content)
content = content.replace(' cursor-pointer', '')
content = content.replace('cursor-pointer ', '')

def repl(match):
    div_start = match.group(1)
    inner = match.group(2)
    a_tag = match.group(3)
    
    if 'Cần nâng cấp' in inner:
        # replace a_tag with button
        classes = re.search(r'class="([^"]*)"', a_tag).group(1)
        new_btn = f'<button type="button" onclick="showUpgradeModal()" class="{classes}">Khóa</button>'
        return f'<div onclick="showUpgradeModal()" class="{div_start} cursor-pointer">\n{inner}\n{new_btn}'
    else:
        return f'<div onclick="window.location.href=\'video-player.html\'" class="{div_start} cursor-pointer">\n{inner}\n{a_tag}'

pattern = re.compile(r'<div class="([^"]*flex items-center justify-between p-3[^"]*)">\n(.*?)\n(<a href="video-player\.html"[^>]*>.*?</a>)', re.DOTALL)

content = pattern.sub(repl, content)

with open('/Users/nghiadv/Projects/video-learning-demo/courses.html', 'w', encoding='utf-8') as f:
    f.write(content)
print("Regex replace successful.")
