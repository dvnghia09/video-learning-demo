import re

with open('/Users/nghiadv/Projects/video-learning-demo/video-player.html', 'r', encoding='utf-8') as f:
    content = f.read()

# Fix Header
old_vp_header = r'<div class="flex justify-between h-16">.*?</div>\s*</div>\s*</nav>'
new_vp_header = """<div class="flex justify-between h-16 items-center">
                <a href="index.html" class="flex items-center space-x-1 sm:space-x-2 text-amber-600">
                    <div class="w-8 h-8 bg-amber-100 rounded-full flex items-center justify-center text-lg border border-amber-200">🌸</div>
                    <span class="text-xl sm:text-2xl font-bold tracking-tight">FloralArt</span>
                </a>
                <div class="flex items-center space-x-2 sm:space-x-4">
                    <a href="login.html" class="bg-amber-600 text-white px-3 py-1.5 sm:px-4 sm:py-2 rounded-full font-bold hover:bg-amber-700 transition text-sm">Đăng nhập</a>
                </div>
            </div>
        </div>
    </nav>"""
content = re.sub(old_vp_header, new_vp_header, content, flags=re.DOTALL)

# Replace Breadcrumb
old_breadcrumb = r'<!-- Breadcrumb -->\s*<div class="mb-4 text-sm text-gray-500">.*?</div>'
new_back_btn = """<!-- Back Button instead of Breadcrumb -->
        <div class="mb-6">
            <a href="courses.html" class="inline-flex items-center text-amber-700 font-bold hover:text-amber-800 transition text-base sm:text-lg bg-amber-100 px-5 py-2.5 rounded-full shadow-sm hover:shadow active:scale-95">
                <svg class="w-5 h-5 sm:w-6 sm:h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Quay lại Danh sách bài học
            </a>
        </div>"""
content = re.sub(old_breadcrumb, new_back_btn, content, flags=re.DOTALL)

with open('/Users/nghiadv/Projects/video-learning-demo/video-player.html', 'w', encoding='utf-8') as f:
    f.write(content)

print("Fixed video player header and breadcrumb.")
