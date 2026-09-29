import re

filepath = '/Users/nghiadv/Projects/video-learning-demo/courses.html'
with open(filepath, 'r', encoding='utf-8') as f:
    content = f.read()

# 1. Remove " open" class from any accordion-content
content = content.replace('accordion-content bg-gray-50 border-t border-gray-100 open', 'accordion-content bg-gray-50 border-t border-gray-100')

# 2. Remove default icon rotation JS if exists
content = re.sub(r"document\.getElementById\('icon-lesson2'\)\.classList\.add\('rotate-180'\);", "", content)

# 3. Add Banner Image
banner_html = """
        <!-- Banner Khóa Học -->
        <div class="max-w-3xl mx-auto mb-8 rounded-xl overflow-hidden shadow-lg relative">
            <img src="https://images.unsplash.com/photo-1563241527-3004b7be0ffd?auto=format&fit=crop&w=1200&q=80" alt="Banner khóa học cắm hoa" class="w-full h-64 object-cover">
            <div class="absolute inset-0 bg-black bg-opacity-30 flex items-center justify-center">
                <h2 class="text-white text-3xl font-bold tracking-wider">Hành trình tạo nên cái đẹp</h2>
            </div>
        </div>
"""

# Insert banner before the search bar
if "<!-- Banner Khóa Học -->" not in content:
    content = content.replace('<div class="max-w-3xl mx-auto mb-8 relative">', banner_html + '\n        <div class="max-w-3xl mx-auto mb-8 relative">')

with open(filepath, 'w', encoding='utf-8') as f:
    f.write(content)

print("Fixed courses.html successfully")
