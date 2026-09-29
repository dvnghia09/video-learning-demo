import glob

# 1. Add Tailwind to all HTML files that are missing it
tailwind_tag = '<script src="https://cdn.tailwindcss.com"></script>'
for filepath in glob.glob('/Users/nghiadv/Projects/video-learning-demo/*.html'):
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()
    if tailwind_tag not in content:
        # insert after <title>
        content = content.replace('</title>', f'</title>\n    {tailwind_tag}')
        with open(filepath, 'w', encoding='utf-8') as f:
            f.write(content)
        print(f"Added Tailwind to {filepath}")

# 2. Create Register.html from login.html
login_path = '/Users/nghiadv/Projects/video-learning-demo/login.html'
register_path = '/Users/nghiadv/Projects/video-learning-demo/register.html'
with open(login_path, 'r', encoding='utf-8') as f:
    login_content = f.read()

register_content = login_content.replace('Đăng nhập hệ thống', 'Đăng ký tài khoản')
register_content = register_content.replace('Đăng nhập</button>', 'Đăng ký</button>')
register_content = register_content.replace('<title>Đăng nhập</title>', '<title>Đăng ký tài khoản</title>')

# Add name and confirm password fields
name_field = """
                <div>
                    <label class="block text-sm font-medium text-gray-700">Họ và tên</label>
                    <input type="text" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-pink-500 focus:border-pink-500" required>
                </div>
"""
email_field = '<div>\n                    <label class="block text-sm font-medium text-gray-700">Email</label>'
register_content = register_content.replace(email_field, name_field + '\n                ' + email_field)

password_field_end = 'focus:ring-pink-500 focus:border-pink-500" required>\n                </div>'
confirm_password = """
                <div>
                    <label class="block text-sm font-medium text-gray-700">Xác nhận mật khẩu</label>
                    <input type="password" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-pink-500 focus:border-pink-500" required>
                </div>
"""
# Replace the first occurrence of password_field_end safely
parts = register_content.split(password_field_end)
if len(parts) >= 2:
    register_content = parts[0] + password_field_end + '\n' + confirm_password + parts[1]

# Link to login
register_content = register_content.replace(
    'Chưa có tài khoản? <a href="#" class="text-pink-600 hover:text-pink-500 font-medium">Liên hệ Zalo đăng ký</a>',
    'Đã có tài khoản? <a href="login.html" class="text-pink-600 hover:text-pink-500 font-medium">Đăng nhập ngay</a>'
)

with open(register_path, 'w', encoding='utf-8') as f:
    f.write(register_content)

# 3. Update login.html to link to register.html
with open(login_path, 'r', encoding='utf-8') as f:
    login_content = f.read()

login_content = login_content.replace(
    'Chưa có tài khoản? <a href="#" class="text-pink-600 hover:text-pink-500 font-medium">Liên hệ Zalo đăng ký</a>',
    'Chưa có tài khoản? <a href="register.html" class="text-pink-600 hover:text-pink-500 font-medium">Đăng ký ngay</a>'
)

with open(login_path, 'w', encoding='utf-8') as f:
    f.write(login_content)

print("Created register.html and fixed scripts")
