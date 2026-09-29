import re

with open('/Users/nghiadv/Projects/video-learning-demo/login.html', 'r', encoding='utf-8') as f:
    login_html = f.read()

# Fix login.html to use Phone instead of Email
login_html = login_html.replace('type="email"', 'type="tel" pattern="[0-9]*"')
login_html = login_html.replace('Địa chỉ Email', 'Số điện thoại')
login_html = login_html.replace('Email (ví dụ: demo@gmail.com)', 'Số điện thoại')
login_html = login_html.replace('id="email-address"', 'id="phone-number"')
login_html = login_html.replace('name="email"', 'name="phone"')
login_html = login_html.replace('autocomplete="email"', 'autocomplete="tel"')

# Ensure link to register is present at the bottom of the form
link_register = """
                <div class="text-center mt-4 text-sm text-gray-600">
                    Chưa có tài khoản? <a href="register.html" class="text-pink-600 hover:text-pink-500 font-medium">Đăng ký ngay</a>
                </div>
"""
if "Chưa có tài khoản?" not in login_html:
    login_html = login_html.replace('</form>', link_register + '            </form>')

with open('/Users/nghiadv/Projects/video-learning-demo/login.html', 'w', encoding='utf-8') as f:
    f.write(login_html)


# Rewrite register.html completely based on login.html layout but with proper fields
register_form = """
            <div>
                <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                    Đăng ký tài khoản
                </h2>
                <p class="mt-2 text-center text-sm text-gray-600">
                    Bắt đầu hành trình nghệ thuật cắm hoa
                </p>
            </div>
            <form class="mt-8 space-y-6" action="index.html" method="GET">
                <div class="rounded-md shadow-sm space-y-4">
                    <div>
                        <label for="full-name" class="sr-only">Họ và tên</label>
                        <input id="full-name" name="name" type="text" required class="appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-pink-500 focus:border-pink-500 focus:z-10 sm:text-sm" placeholder="Họ và tên">
                    </div>
                    <div>
                        <label for="phone-number" class="sr-only">Số điện thoại</label>
                        <input id="phone-number" name="phone" type="tel" pattern="[0-9]*" required class="appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-pink-500 focus:border-pink-500 focus:z-10 sm:text-sm" placeholder="Số điện thoại">
                    </div>
                    <div>
                        <label for="password" class="sr-only">Mật khẩu</label>
                        <input id="password" name="password" type="password" required class="appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-pink-500 focus:border-pink-500 focus:z-10 sm:text-sm" placeholder="Mật khẩu">
                    </div>
                    <div>
                        <label for="confirm-password" class="sr-only">Xác nhận mật khẩu</label>
                        <input id="confirm-password" name="confirm-password" type="password" required class="appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-pink-500 focus:border-pink-500 focus:z-10 sm:text-sm" placeholder="Xác nhận mật khẩu">
                    </div>
                </div>

                <div>
                    <button type="submit" class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-pink-500 hover:bg-pink-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-pink-500">
                        Đăng ký
                    </button>
                </div>
                
                <div class="text-center mt-4 text-sm text-gray-600">
                    Đã có tài khoản? <a href="login.html" class="text-pink-600 hover:text-pink-500 font-medium">Đăng nhập ngay</a>
                </div>
            </form>
"""

with open('/Users/nghiadv/Projects/video-learning-demo/register.html', 'r', encoding='utf-8') as f:
    register_html = f.read()

# Replace the inner block of max-w-md w-full space-y-8 bg-white p-8 rounded-lg shadow-lg
pattern = re.compile(r'<div>\s*<h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">.*?</form>', re.DOTALL)
register_html = pattern.sub(register_form.strip(), register_html)
register_html = register_html.replace('<title>Đăng nhập</title>', '<title>Đăng ký tài khoản</title>')
register_html = register_html.replace('<title>Đăng nhập tài khoản</title>', '<title>Đăng ký tài khoản</title>')

with open('/Users/nghiadv/Projects/video-learning-demo/register.html', 'w', encoding='utf-8') as f:
    f.write(register_html)

print("Updated forms in login.html and register.html")
