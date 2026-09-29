import re

filepath = '/Users/nghiadv/Projects/video-learning-demo/index.html'
with open(filepath, 'r', encoding='utf-8') as f:
    content = f.read()

new_body_content = """
    <!-- Navbar -->
    <nav class="bg-white shadow-md border-b border-gray-100 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="index.html" class="text-2xl font-extrabold text-pink-500 tracking-tight">Floral<span class="text-gray-800">Art</span></a>
                    <div class="hidden md:flex space-x-8 ml-10">
                        <a href="index.html" class="text-pink-500 font-bold border-b-2 border-pink-500 py-5">Trang chủ</a>
                        <a href="courses.html" class="text-gray-600 hover:text-pink-500 font-medium py-5 transition">Danh sách bài học</a>
                    </div>
                </div>
                <div class="flex items-center">
                    <a href="login.html" class="text-gray-600 hover:text-pink-500 px-3 py-2 rounded-md font-medium transition">Đăng nhập</a>
                    <a href="courses.html" class="ml-4 bg-gradient-to-r from-pink-500 to-rose-500 text-white px-5 py-2.5 rounded-full font-bold hover:shadow-lg hover:scale-105 transition transform duration-200">Học cắm hoa ngay</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="relative bg-pink-50 py-20 overflow-hidden">
        <div class="absolute inset-y-0 right-0 w-1/2 bg-rose-100 rounded-l-full opacity-50 transform translate-x-1/3"></div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative">
            <div class="flex flex-col md:flex-row items-center">
                <div class="md:w-1/2 pr-0 md:pr-12 mb-12 md:mb-0">
                    <span class="inline-block py-1 px-3 rounded-full bg-pink-200 text-pink-700 text-sm font-bold mb-6">Khóa học dành cho mọi lứa tuổi 🌸</span>
                    <h1 class="text-5xl md:text-6xl font-extrabold text-gray-900 leading-tight mb-6">
                        Đánh thức vẻ đẹp từ <span class="text-transparent bg-clip-text bg-gradient-to-r from-pink-500 to-rose-500">những đóa hoa</span>
                    </h1>
                    <p class="text-lg text-gray-600 mb-8 leading-relaxed">
                        Chỉ với 15 phút mỗi ngày, tự tay cắm những lẵng hoa tuyệt đẹp tô điểm cho ngôi nhà của bạn. Kỹ thuật cắm hoa hiện đại được hướng dẫn siêu chậm và dễ hiểu.
                    </p>
                    <div class="flex space-x-4">
                        <a href="courses.html" class="bg-pink-500 text-white px-8 py-4 rounded-full font-bold text-lg hover:bg-pink-600 hover:shadow-xl transition transform hover:-translate-y-1">Vào học ngay</a>
                        <a href="https://zalo.me/0123456789" target="_blank" class="bg-white text-pink-500 border border-pink-200 px-8 py-4 rounded-full font-bold text-lg hover:bg-pink-50 transition shadow-sm flex items-center">
                            Tư vấn Zalo
                        </a>
                    </div>
                </div>
                <div class="md:w-1/2 relative">
                    <div class="absolute inset-0 bg-pink-300 rounded-3xl transform rotate-3 scale-105 opacity-20"></div>
                    <img src="https://images.unsplash.com/photo-1561181286-d3fee7d55364?auto=format&fit=crop&w=800&q=80" alt="Cắm hoa nghệ thuật" class="relative rounded-3xl shadow-2xl border-4 border-white object-cover h-[500px] w-full">
                </div>
            </div>
        </div>
    </div>

    <!-- Gallery Section -->
    <div class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-4xl font-bold text-gray-900 mb-4">Các tác phẩm nổi bật</h2>
                <p class="text-gray-500 text-lg">Những lẵng hoa mang phong cách hiện đại và đầy nghệ thuật</p>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <img src="https://images.unsplash.com/photo-1490750967868-88aa4486c946?auto=format&fit=crop&w=600&q=80" alt="Hoa 1" class="rounded-2xl h-64 w-full object-cover shadow-md hover:scale-105 transition duration-300">
                <img src="https://images.unsplash.com/photo-1457089328109-e5d9bd499191?auto=format&fit=crop&w=600&q=80" alt="Hoa 2" class="rounded-2xl h-64 w-full object-cover shadow-md hover:scale-105 transition duration-300 mt-0 md:mt-8">
                <img src="https://images.unsplash.com/photo-1526047932273-341f2a7631f9?auto=format&fit=crop&w=600&q=80" alt="Hoa 3" class="rounded-2xl h-64 w-full object-cover shadow-md hover:scale-105 transition duration-300">
                <img src="https://images.unsplash.com/photo-1508759073847-9ea705321d4c?auto=format&fit=crop&w=600&q=80" alt="Hoa 4" class="rounded-2xl h-64 w-full object-cover shadow-md hover:scale-105 transition duration-300 mt-0 md:mt-8">
            </div>
        </div>
    </div>

    <!-- Features -->
    <div class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-4xl font-bold text-gray-900 mb-12">Học cắm hoa chưa bao giờ dễ đến thế</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-10">
                <div class="p-8 bg-white rounded-3xl shadow-sm hover:shadow-xl transition duration-300 border border-gray-100">
                    <div class="w-16 h-16 bg-pink-100 text-pink-500 rounded-2xl flex items-center justify-center mx-auto mb-6 text-3xl">🌸</div>
                    <h3 class="text-2xl font-bold mb-3 text-gray-800">Thực hành thực tế</h3>
                    <p class="text-gray-600 leading-relaxed">Góc quay video cận cảnh từng bước cắt tỉa, định hình xốp. Rất chậm và chi tiết để các bác lớn tuổi cũng dễ dàng làm theo.</p>
                </div>
                <div class="p-8 bg-white rounded-3xl shadow-sm hover:shadow-xl transition duration-300 border border-gray-100">
                    <div class="w-16 h-16 bg-pink-100 text-pink-500 rounded-2xl flex items-center justify-center mx-auto mb-6 text-3xl">🎨</div>
                    <h3 class="text-2xl font-bold mb-3 text-gray-800">Tư duy phối màu</h3>
                    <p class="text-gray-600 leading-relaxed">Học lỏm các bí quyết phối màu hoa theo phong cách Hàn Quốc sang trọng, nhẹ nhàng mà không hề lòe loẹt.</p>
                </div>
                <div class="p-8 bg-white rounded-3xl shadow-sm hover:shadow-xl transition duration-300 border border-gray-100">
                    <div class="w-16 h-16 bg-pink-100 text-pink-500 rounded-2xl flex items-center justify-center mx-auto mb-6 text-3xl">✨</div>
                    <h3 class="text-2xl font-bold mb-3 text-gray-800">Hỗ trợ trọn đời</h3>
                    <p class="text-gray-600 leading-relaxed">Cắm xong có thể chụp ảnh tác phẩm gửi qua Zalo, giáo viên sẽ nhận xét và tư vấn cách cắm đẹp hơn hoàn toàn miễn phí.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-900 text-gray-300 py-12 mt-0">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div>
                    <h3 class="text-2xl font-bold text-white mb-6">FloralArt</h3>
                    <p class="text-base text-gray-400">Chia sẻ đam mê và lan tỏa nghệ thuật cắm hoa đến mọi gia đình.</p>
                </div>
                <div>
                    <h4 class="font-bold text-white mb-6 text-lg">Liên kết</h4>
                    <ul class="text-base space-y-3">
                        <li><a href="index.html" class="hover:text-pink-400 transition">Trang chủ</a></li>
                        <li><a href="courses.html" class="hover:text-pink-400 transition">Danh sách bài học</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-bold text-white mb-6 text-lg">Liên hệ Admin</h4>
                    <ul class="text-base space-y-3">
                        <li>Hotline: 0123.456.789</li>
                        <li>Zalo: <a href="https://zalo.me/0123456789" class="text-pink-400 font-bold hover:text-pink-300 transition">0123.456.789</a></li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-gray-800 mt-10 pt-8 text-sm text-center text-gray-500">
                &copy; 2026 FloralArt. Tất cả bản quyền thuộc về nhà sáng lập.
            </div>
        </div>
    </footer>

    <!-- Floating Zalo -->
    <a href="https://zalo.me/0123456789" target="_blank" class="zalo-floating-btn">
        <div class="zalo-icon-wrap">
            <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/9/91/Icon_of_Zalo.svg/1024px-Icon_of_Zalo.svg.png" alt="Zalo">
        </div>
    </a>
"""

start_tag = '<body class="bg-white text-gray-800">'
end_tag = '</body>'

start_idx = content.find(start_tag) + len(start_tag)
end_idx = content.find(end_tag)

content = content[:start_idx] + new_body_content + content[end_idx:]

with open(filepath, 'w', encoding='utf-8') as f:
    f.write(content)

print("Updated index.html successfully")
