import re

filepath = '/Users/nghiadv/Projects/video-learning-demo/courses.html'
with open(filepath, 'r', encoding='utf-8') as f:
    content = f.read()

# 1. Remove the global banner
banner_pattern = r'<!-- Banner Khóa Học -->.*?</div>\s*</div>'
content = re.sub(banner_pattern, '', content, flags=re.DOTALL)

# 2. Add small thumbnails to each lesson header
# We can find each <h2 class="text-xl font-bold text-gray-800">Bài X: ...</h2>
# and wrap it in a flex div with an image.

img_lesson1 = '<img src="https://images.unsplash.com/photo-1563241527-3004b7be0ffd?auto=format&fit=crop&w=200&q=80" class="w-20 h-14 sm:w-24 sm:h-16 object-cover rounded shadow-sm">'
img_lesson2 = '<img src="https://images.unsplash.com/photo-1526047932273-341f2a7631f9?auto=format&fit=crop&w=200&q=80" class="w-20 h-14 sm:w-24 sm:h-16 object-cover rounded shadow-sm">'
img_lesson3 = '<img src="https://images.unsplash.com/photo-1457089328109-e5d9bd499191?auto=format&fit=crop&w=200&q=80" class="w-20 h-14 sm:w-24 sm:h-16 object-cover rounded shadow-sm">'
img_lesson4 = '<img src="https://images.unsplash.com/photo-1490750967868-88aa4486c946?auto=format&fit=crop&w=200&q=80" class="w-20 h-14 sm:w-24 sm:h-16 object-cover rounded shadow-sm">'

def replace_h2(match, img_html):
    h2_content = match.group(0)
    # The h2 is already inside a flex button, but we want to group the image and the h2 together
    # Wait, the button has `flex justify-between items-center`.
    # If we replace `<h2...` with `<div class="flex items-center space-x-4"> {img} {h2} </div>` it will perfectly align!
    return f'<div class="flex items-center space-x-4">{img_html}{h2_content}</div>'

# Apply replacements for each lesson
# Since we know the exact text, we can use simple replace
content = content.replace('<h2 class="text-xl font-bold text-gray-800">Bài 1: Cắm hoa cổng vòm</h2>', 
                          f'<div class="flex items-center space-x-4">{img_lesson1}<h2 class="text-xl font-bold text-gray-800 text-left">Bài 1: Cắm hoa cổng vòm</h2></div>')

content = content.replace('<h2 class="text-xl font-bold text-gray-800">Bài 2: Cắm hoa cổng vuông</h2>', 
                          f'<div class="flex items-center space-x-4">{img_lesson2}<h2 class="text-xl font-bold text-gray-800 text-left">Bài 2: Cắm hoa cổng vuông</h2></div>')

content = content.replace('<h2 class="text-xl font-bold text-gray-800">Bài 3: Cắm hoa cổng ngà voi</h2>', 
                          f'<div class="flex items-center space-x-4">{img_lesson3}<h2 class="text-xl font-bold text-gray-800 text-left">Bài 3: Cắm hoa cổng ngà voi</h2></div>')

content = content.replace('<h2 class="text-xl font-bold text-gray-800">Bài 4: Cắm hoa theo mô hình</h2>', 
                          f'<div class="flex items-center space-x-4">{img_lesson4}<h2 class="text-xl font-bold text-gray-800 text-left">Bài 4: Cắm hoa theo mô hình</h2></div>')


with open(filepath, 'w', encoding='utf-8') as f:
    f.write(content)

print("Updated lesson headers successfully")
