import re

# Update login.html
with open('/Users/nghiadv/Projects/video-learning-demo/login.html', 'r', encoding='utf-8') as f:
    login_html = f.read()

small_link_login = r'<div class="text-center mt-4 text-sm text-gray-600">\s*Chưa có tài khoản\? <a href="register.html" class="text-pink-600 hover:text-pink-500 font-medium">Đăng ký ngay</a>\s*</div>'
big_button_login = """
                <div class="text-center mt-8 pt-6 border-t border-gray-200">
                    <p class="text-gray-600 mb-4 text-lg">Bạn chưa có tài khoản?</p>
                    <a href="register.html" class="inline-block w-full flex justify-center py-3 px-4 border-2 border-pink-500 text-pink-600 font-bold rounded-md hover:bg-pink-50 transition text-lg">
                        Tạo tài khoản mới (Đăng ký)
                    </a>
                </div>
"""
login_html = re.sub(small_link_login, big_button_login, login_html)

# Let's also make the "Đăng nhập" button itself larger text
login_html = login_html.replace('text-sm font-medium rounded-md text-white bg-pink-500', 'text-lg font-bold rounded-md text-white bg-pink-500 py-3')
login_html = login_html.replace('py-2 px-4 border border-transparent text-sm', 'border border-transparent text-lg')

# make input labels visible and large instead of sr-only?
# The image shows "Email (*)" above the input.
# Let's make labels visible and large for elderly users.
login_html = login_html.replace('<label for="phone-number" class="sr-only">Số điện thoại</label>', '<label for="phone-number" class="block text-lg font-bold text-gray-700 mb-2">Số điện thoại <span class="text-red-500">*</span></label>')
login_html = login_html.replace('<label for="password" class="sr-only">Mật khẩu</label>', '<label for="password" class="block text-lg font-bold text-gray-700 mb-2 mt-4">Mật khẩu <span class="text-red-500">*</span></label>')
# Remove rounded-t-md and rounded-b-md so inputs look separate
login_html = login_html.replace('rounded-t-md', 'rounded-md')
login_html = login_html.replace('rounded-b-md', 'rounded-md')
# Make inputs bigger
login_html = login_html.replace('px-3 py-2 border', 'px-4 py-3 border-2')

with open('/Users/nghiadv/Projects/video-learning-demo/login.html', 'w', encoding='utf-8') as f:
    f.write(login_html)


# Update register.html
with open('/Users/nghiadv/Projects/video-learning-demo/register.html', 'r', encoding='utf-8') as f:
    register_html = f.read()

small_link_register = r'<div class="text-center mt-4 text-sm text-gray-600">\s*Đã có tài khoản\? <a href="login.html" class="text-pink-600 hover:text-pink-500 font-medium">Đăng nhập ngay</a>\s*</div>'
big_button_register = """
                <div class="text-center mt-8 pt-6 border-t border-gray-200">
                    <p class="text-gray-600 mb-4 text-lg">Đã có tài khoản?</p>
                    <a href="login.html" class="inline-block w-full flex justify-center py-3 px-4 border-2 border-pink-500 text-pink-600 font-bold rounded-md hover:bg-pink-50 transition text-lg">
                        Đăng nhập ngay
                    </a>
                </div>
"""
register_html = re.sub(small_link_register, big_button_register, register_html)

# Same for register labels and inputs
register_html = register_html.replace('<label for="full-name" class="sr-only">Họ và tên</label>', '<label for="full-name" class="block text-lg font-bold text-gray-700 mb-2">Họ và tên <span class="text-red-500">*</span></label>')
register_html = register_html.replace('<label for="phone-number" class="sr-only">Số điện thoại</label>', '<label for="phone-number" class="block text-lg font-bold text-gray-700 mb-2 mt-4">Số điện thoại <span class="text-red-500">*</span></label>')
register_html = register_html.replace('<label for="password" class="sr-only">Mật khẩu</label>', '<label for="password" class="block text-lg font-bold text-gray-700 mb-2 mt-4">Mật khẩu <span class="text-red-500">*</span></label>')
register_html = register_html.replace('<label for="confirm-password" class="sr-only">Xác nhận mật khẩu</label>', '<label for="confirm-password" class="block text-lg font-bold text-gray-700 mb-2 mt-4">Xác nhận mật khẩu <span class="text-red-500">*</span></label>')

register_html = register_html.replace('text-sm font-medium rounded-md text-white bg-pink-500', 'text-lg font-bold rounded-md text-white bg-pink-500 py-3')
register_html = register_html.replace('py-2 px-4 border border-transparent text-sm', 'border border-transparent text-lg')
register_html = register_html.replace('rounded-t-md', 'rounded-md')
register_html = register_html.replace('rounded-b-md', 'rounded-md')
register_html = register_html.replace('px-3 py-2 border', 'px-4 py-3 border-2')

with open('/Users/nghiadv/Projects/video-learning-demo/register.html', 'w', encoding='utf-8') as f:
    f.write(register_html)

print("Updated buttons and labels for elderly accessibility.")
