import re

with open('/Users/nghiadv/Projects/video-learning-demo/login.html', 'r', encoding='utf-8') as f:
    login_html = f.read()

login_html = login_html.replace('<label for="email-address" class="sr-only">Số điện thoại</label>', '<label for="phone-number" class="block text-lg font-bold text-gray-700 mb-2">Số điện thoại <span class="text-red-500">*</span></label>')
login_html = login_html.replace('rounded-none', '')
login_html = login_html.replace('sm:text-sm', 'text-lg')

with open('/Users/nghiadv/Projects/video-learning-demo/login.html', 'w', encoding='utf-8') as f:
    f.write(login_html)

with open('/Users/nghiadv/Projects/video-learning-demo/register.html', 'r', encoding='utf-8') as f:
    register_html = f.read()

register_html = register_html.replace('rounded-none', '')
register_html = register_html.replace('sm:text-sm', 'text-lg')

with open('/Users/nghiadv/Projects/video-learning-demo/register.html', 'w', encoding='utf-8') as f:
    f.write(register_html)

print("Fixed labels and input sizes")
