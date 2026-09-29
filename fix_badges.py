import re

with open('/Users/nghiadv/Projects/video-learning-demo/video-player.html', 'r', encoding='utf-8') as f:
    vp = f.read()

vp = vp.replace('<p class="text-xs text-green-600 font-medium">Đang phát (Miễn phí)</p>',
                '<div class="flex items-center mt-1"><span class="bg-green-500 text-white text-[10px] sm:text-xs font-bold px-2 py-0.5 rounded-sm shadow-sm">MIỄN PHÍ</span><span class="ml-2 text-xs text-green-600 font-medium">Đang phát</span></div>')

vp = vp.replace('<p class="text-xs text-red-500 font-medium">Yêu cầu nâng cấp</p>',
                '<div class="mt-1"><span class="bg-red-500 text-white text-[10px] sm:text-xs font-bold px-2 py-0.5 rounded-sm shadow-sm inline-flex items-center">🔒 CẦN ĐĂNG KÝ VIP</span></div>')

with open('/Users/nghiadv/Projects/video-learning-demo/video-player.html', 'w', encoding='utf-8') as f:
    f.write(vp)


with open('/Users/nghiadv/Projects/video-learning-demo/courses.html', 'r', encoding='utf-8') as f:
    courses = f.read()

courses = re.sub(r'<span class="text-xs text-gray-500">([0-9:]+) \(Miễn phí\)</span>',
                 r'<span class="text-xs text-gray-500">\1</span> <span class="bg-green-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-sm ml-1 shadow-sm">MIỄN PHÍ</span>', courses)

courses = re.sub(r'<span class="text-xs text-gray-500">([0-9:]+) \(Khóa\)</span>',
                 r'<span class="text-xs text-gray-500">\1</span> <span class="bg-red-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-sm ml-1 shadow-sm inline-flex items-center">🔒 CẦN ĐĂNG KÝ</span>', courses)

with open('/Users/nghiadv/Projects/video-learning-demo/courses.html', 'w', encoding='utf-8') as f:
    f.write(courses)

print("Updated video badges.")
