filepath = '/Users/nghiadv/Projects/video-learning-demo/index.html'
with open(filepath, 'r', encoding='utf-8') as f:
    content = f.read()

# Fix Navbar: Make right buttons stack or hide the large button on very small screens, or use smaller padding
content = content.replace(
    '<div class="flex items-center">\n                    <a href="login.html" class="text-gray-600 hover:text-pink-500 px-3 py-2 rounded-md font-medium transition">Đăng nhập</a>\n                    <a href="courses.html" class="ml-4 bg-gradient-to-r from-pink-500 to-rose-500 text-white px-5 py-2.5 rounded-full font-bold hover:shadow-lg hover:scale-105 transition transform duration-200">Học cắm hoa ngay</a>\n                </div>',
    '<div class="flex items-center space-x-2 sm:space-x-4">\n                    <a href="login.html" class="text-gray-600 hover:text-pink-500 px-2 py-2 rounded-md font-medium transition text-sm sm:text-base">Đăng nhập</a>\n                    <a href="courses.html" class="hidden sm:inline-block bg-gradient-to-r from-pink-500 to-rose-500 text-white px-4 py-2 sm:px-5 sm:py-2.5 rounded-full font-bold text-sm sm:text-base hover:shadow-lg hover:scale-105 transition transform duration-200">Học ngay</a>\n                </div>'
)

# Fix Hero title sizes
content = content.replace('text-5xl md:text-6xl', 'text-4xl sm:text-5xl md:text-6xl')

# Fix Hero buttons: Make them stack on mobile
content = content.replace(
    '<div class="flex space-x-4">',
    '<div class="flex flex-col sm:flex-row space-y-4 sm:space-y-0 sm:space-x-4 w-full sm:w-auto">'
)

content = content.replace(
    'class="bg-pink-500 text-white px-8 py-4 rounded-full font-bold text-lg hover:bg-pink-600 hover:shadow-xl transition transform hover:-translate-y-1"',
    'class="bg-pink-500 text-white px-6 py-3 sm:px-8 sm:py-4 rounded-full font-bold text-base sm:text-lg text-center hover:bg-pink-600 hover:shadow-xl transition transform hover:-translate-y-1"'
)

content = content.replace(
    'class="bg-white text-pink-500 border border-pink-200 px-8 py-4 rounded-full font-bold text-lg hover:bg-pink-50 transition shadow-sm flex items-center"',
    'class="bg-white text-pink-500 border border-pink-200 px-6 py-3 sm:px-8 sm:py-4 rounded-full font-bold text-base sm:text-lg text-center hover:bg-pink-50 transition shadow-sm justify-center flex items-center"'
)

with open(filepath, 'w', encoding='utf-8') as f:
    f.write(content)

print("Fixed index.html responsive issues")
