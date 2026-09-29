import re

with open('/Users/nghiadv/Projects/video-learning-demo/courses.html', 'r', encoding='utf-8') as f:
    content = f.read()

old_search = r'<div class="max-w-3xl mx-auto mb-8 relative">\s*<div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">.*?</div>\s*<input type="text" id="searchInput".*?>\s*</div>'

new_search = """<div class="max-w-3xl mx-auto mb-8 flex shadow-sm rounded-xl overflow-hidden border-2 border-gray-300 focus-within:ring-4 focus-within:ring-amber-100 focus-within:border-amber-500 transition-all">
            <div class="pl-4 flex items-center bg-white pointer-events-none">
                <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>
            <input type="text" id="searchInput" onkeyup="searchLessons()" placeholder="Nhập tên bài học cần tìm..." class="w-full pl-3 pr-2 py-3 sm:py-4 bg-white focus:outline-none text-gray-700 font-medium text-base sm:text-lg">
            <button onclick="searchLessons()" class="px-4 sm:px-8 py-3 sm:py-4 bg-amber-600 text-white font-bold hover:bg-amber-700 transition text-base sm:text-lg whitespace-nowrap">
                Tìm kiếm
            </button>
        </div>"""

content = re.sub(old_search, new_search, content, flags=re.DOTALL)

with open('/Users/nghiadv/Projects/video-learning-demo/courses.html', 'w', encoding='utf-8') as f:
    f.write(content)

print("Added search button.")
