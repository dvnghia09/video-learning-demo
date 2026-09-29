from bs4 import BeautifulSoup

with open('/Users/nghiadv/Projects/video-learning-demo/courses.html', 'r', encoding='utf-8') as f:
    soup = BeautifulSoup(f, 'html.parser')

rows = soup.find_all('div', class_=lambda c: c and 'flex items-center justify-between p-3' in c)

for row in rows:
    # Ensure class doesn't have cursor-pointer yet so we can append it cleanly
    classes = row.get('class', [])
    if 'cursor-pointer' not in classes:
        classes.append('cursor-pointer')
    row['class'] = classes
    
    # Check if free or locked
    text = row.get_text()
    is_locked = 'Cần nâng cấp' in text
    
    if is_locked:
        row['onclick'] = "showUpgradeModal()"
        # Find the a tag
        a_tag = row.find('a')
        if a_tag:
            btn = soup.new_tag('button')
            btn['type'] = 'button'
            btn['class'] = a_tag.get('class', [])
            btn.string = 'Khóa'
            # We don't need onclick on button because it bubbles to div
            a_tag.replace_with(btn)
    else:
        row['onclick'] = "window.location.href='video-player.html'"
        # Ensure it's a link or button
        pass

with open('/Users/nghiadv/Projects/video-learning-demo/courses.html', 'w', encoding='utf-8') as f:
    # use html.parser formatter
    f.write(str(soup))

print("Fixed using BS4.")
