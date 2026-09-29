login_body = """<body class="bg-gray-50 min-h-screen flex flex-col font-sans">
    <div class="flex-grow flex items-center justify-center p-4">
        <div class="max-w-md w-full bg-white p-6 sm:p-8 rounded-2xl shadow-xl">
            <!-- Logo & Title -->
            <div class="text-center mb-8">
                <a href="index.html" class="inline-block">
                    <div class="w-16 h-16 bg-amber-100 text-amber-600 rounded-full flex items-center justify-center mx-auto mb-3 text-3xl shadow-sm border-2 border-amber-200">🌸</div>
                </a>
                <h2 class="text-2xl font-bold text-gray-900">FloralArt</h2>
                <p class="text-sm text-gray-500 mt-1">Đánh thức vẻ đẹp từ những đóa hoa</p>
            </div>

            <!-- Tab Switcher -->
            <div class="flex bg-gray-100 rounded-full p-1 mb-6 border border-gray-200 shadow-inner">
                <a href="login.html" class="w-1/2 text-center py-2 rounded-full bg-amber-600 text-white font-bold shadow-md transition text-base">Đăng nhập</a>
                <a href="register.html" class="w-1/2 text-center py-2 rounded-full text-gray-600 font-medium hover:text-amber-600 transition text-base">Đăng ký</a>
            </div>

            <form action="index.html" method="GET" class="space-y-5">
                <div>
                    <label for="phone-number" class="block text-base font-bold text-gray-700 mb-1">Số điện thoại <span class="text-red-500">*</span></label>
                    <input id="phone-number" name="phone" type="tel" pattern="[0-9]*" autocomplete="tel" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500 text-lg shadow-sm" placeholder="Nhập số điện thoại">
                </div>

                <div>
                    <div class="flex justify-between items-center mb-1">
                        <label for="password" class="block text-base font-bold text-gray-700">Mật khẩu <span class="text-red-500">*</span></label>
                        <a href="#" class="text-sm font-medium text-amber-600 hover:text-amber-700">Quên mật khẩu?</a>
                    </div>
                    <input id="password" name="password" type="password" autocomplete="current-password" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500 text-lg shadow-sm" placeholder="Nhập mật khẩu">
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full flex justify-center py-3.5 px-4 border border-transparent text-lg font-bold rounded-xl text-white bg-amber-600 hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500 shadow-lg transform transition hover:-translate-y-0.5">
                        ĐĂNG NHẬP
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>"""

import re

with open('/Users/nghiadv/Projects/video-learning-demo/login.html', 'r', encoding='utf-8') as f:
    content = f.read()

content = re.sub(r'<body.*</body>', login_body, content, flags=re.DOTALL)

with open('/Users/nghiadv/Projects/video-learning-demo/login.html', 'w', encoding='utf-8') as f:
    f.write(content)

print("Updated login.html layout")
