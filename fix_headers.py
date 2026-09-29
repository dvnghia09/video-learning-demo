import re
import glob

# For index.html
with open('/Users/nghiadv/Projects/video-learning-demo/index.html', 'r', encoding='utf-8') as f:
    content = f.read()

# Make index.html header super responsive
old_index_header = r'<div class="flex justify-between h-20 items-center">.*?</div>'
new_index_header = """<div class="flex justify-between h-16 sm:h-20 items-center">
                <a href="index.html" class="flex items-center space-x-1.5 sm:space-x-2">
                    <div class="w-8 h-8 sm:w-10 sm:h-10 bg-amber-100 text-amber-600 rounded-full flex items-center justify-center text-lg sm:text-xl shadow-sm border border-amber-200">🌸</div>
                    <span class="text-xl sm:text-2xl font-bold text-gray-900 tracking-tight">FloralArt</span>
                </a>
                <div class="flex space-x-2 sm:space-x-4 items-center">
                    <a href="courses.html" class="text-gray-700 hover:text-amber-600 font-bold transition text-sm sm:text-base hidden xs:block">Bài học</a>
                    <a href="login.html" class="bg-amber-600 text-white px-3 py-1.5 sm:px-5 sm:py-2.5 rounded-full font-bold hover:bg-amber-700 transition shadow-sm text-sm sm:text-base">Đăng nhập</a>
                </div>
            </div>"""
content = re.sub(old_index_header, new_index_header, content, flags=re.DOTALL)
# add custom xs breakpoint in tailwind using arbitrary values or just let it hide? Wait, Tailwind doesn't have `xs:` by default. 
# Instead of `hidden xs:block`, I'll use `hidden sm:block` for the "Bài học" text to guarantee no overflow on small phones.
content = content.replace('hidden xs:block', 'hidden sm:block')

with open('/Users/nghiadv/Projects/video-learning-demo/index.html', 'w', encoding='utf-8') as f:
    f.write(content)


# For courses.html
with open('/Users/nghiadv/Projects/video-learning-demo/courses.html', 'r', encoding='utf-8') as f:
    content = f.read()

old_courses_header = r'<div class="flex justify-between h-16 items-center">.*?</div>'
new_courses_header = """<div class="flex justify-between h-16 items-center">
                <a href="index.html" class="text-lg sm:text-xl font-bold text-amber-600 flex items-center gap-1 sm:gap-2">
                    <span class="text-xl sm:text-2xl">🌸</span> FloralArt
                </a>
                <a href="login.html" class="bg-amber-600 text-white px-3 py-1.5 sm:px-4 sm:py-2 rounded font-bold hover:bg-amber-700 transition text-sm">Đăng nhập</a>
            </div>"""
content = re.sub(old_courses_header, new_courses_header, content, flags=re.DOTALL)

with open('/Users/nghiadv/Projects/video-learning-demo/courses.html', 'w', encoding='utf-8') as f:
    f.write(content)


# For video-player.html
with open('/Users/nghiadv/Projects/video-learning-demo/video-player.html', 'r', encoding='utf-8') as f:
    content = f.read()

old_vp_header = r'<div class="flex justify-between h-16 items-center">.*?</div>'
new_vp_header = """<div class="flex justify-between h-16 items-center">
                <a href="index.html" class="flex items-center text-amber-600 font-bold hover:text-amber-700 transition text-sm sm:text-base">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5 mr-1 sm:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    Quay lại
                </a>
                <a href="login.html" class="bg-amber-600 text-white px-3 py-1.5 sm:px-4 sm:py-2 rounded font-bold hover:bg-amber-700 transition text-sm">Đăng nhập</a>
            </div>"""
content = re.sub(old_vp_header, new_vp_header, content, flags=re.DOTALL)

with open('/Users/nghiadv/Projects/video-learning-demo/video-player.html', 'w', encoding='utf-8') as f:
    f.write(content)


# For login and register
for path in ['/Users/nghiadv/Projects/video-learning-demo/login.html', '/Users/nghiadv/Projects/video-learning-demo/register.html']:
    with open(path, 'r', encoding='utf-8') as f:
        content = f.read()
    old_auth_header = r'<div class="flex justify-between h-16 items-center">.*?</div>'
    new_auth_header = """<div class="flex justify-between h-16 items-center">
                <a href="index.html" class="text-lg sm:text-xl font-bold text-amber-600 flex items-center gap-1.5">
                    <span class="text-xl">🌸</span> FloralArt
                </a>
                <a href="courses.html" class="text-gray-500 hover:text-amber-600 text-sm font-medium transition">
                    Vào lớp học
                </a>
            </div>"""
    content = re.sub(old_auth_header, new_auth_header, content, flags=re.DOTALL)
    with open(path, 'w', encoding='utf-8') as f:
        f.write(content)

print("Updated headers for all pages to be responsive on small screens.")
