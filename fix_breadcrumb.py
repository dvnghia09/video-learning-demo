import re

with open('/Users/nghiadv/Projects/video-learning-demo/courses.html', 'r', encoding='utf-8') as f:
    content = f.read()

old_breadcrumb = r'<div class="mb-6 text-sm text-gray-500">\s*<a href="index.html" class="hover:text-amber-600">Trang chủ</a> &gt; <span>Danh sách bài học</span>\s*</div>'

new_back_btn = """<div class="mb-6">
            <a href="index.html" class="inline-flex items-center text-amber-700 font-bold hover:text-amber-800 transition text-base sm:text-lg bg-amber-100 px-5 py-2.5 rounded-full shadow-sm hover:shadow active:scale-95">
                <svg class="w-5 h-5 sm:w-6 sm:h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Quay lại Trang chủ
            </a>
        </div>"""

content = re.sub(old_breadcrumb, new_back_btn, content)

with open('/Users/nghiadv/Projects/video-learning-demo/courses.html', 'w', encoding='utf-8') as f:
    f.write(content)

print("Breadcrumb replaced.")
