import re

# 1. Update video-player.html badges
with open('/Users/nghiadv/Projects/video-learning-demo/video-player.html', 'r', encoding='utf-8') as f:
    vp = f.read()

vp = vp.replace('CẦN ĐĂNG KÝ VIP', 'CẦN NÂNG CẤP GÓI')

with open('/Users/nghiadv/Projects/video-learning-demo/video-player.html', 'w', encoding='utf-8') as f:
    f.write(vp)


# 2. Fix courses.html header and badges
with open('/Users/nghiadv/Projects/video-learning-demo/courses.html', 'r', encoding='utf-8') as f:
    courses = f.read()

# Fix header
old_courses_header = r'<div class="flex justify-between h-16">.*?</div>\s*</div>\s*</nav>'
new_courses_header = """<div class="flex justify-between h-16 items-center">
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
courses = re.sub(old_courses_header, new_courses_header, courses, flags=re.DOTALL)

# Fix Badges
courses = courses.replace('CẦN ĐĂNG KÝ', 'CẦN NÂNG CẤP GÓI')

# Add modal to courses.html
modal_html = """
    <!-- Upgrade Modal for Courses -->
    <div id="upgradeModal" class="hidden fixed inset-0 bg-gray-900 bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white p-6 sm:p-8 rounded-xl shadow-2xl max-w-sm w-full text-center relative">
            <button onclick="closeUpgradeModal()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
            <div class="w-16 h-16 bg-amber-100 text-amber-600 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-2">Video độc quyền</h3>
            <p class="text-gray-600 mb-6">Bạn cần nâng cấp gói học phí để xem toàn bộ video hướng dẫn chuyên sâu này.</p>
            <a href="https://zalo.me/0123456789" target="_blank" class="block w-full py-3 bg-amber-600 text-white font-bold rounded-lg hover:bg-amber-700 transition">
                Liên hệ tư vấn nâng cấp
            </a>
        </div>
    </div>
    
    <script>
        function showUpgradeModal() {
            document.getElementById('upgradeModal').classList.remove('hidden');
        }
        function closeUpgradeModal() {
            document.getElementById('upgradeModal').classList.add('hidden');
        }
        function toggleAccordion(id) {
            const content = document.getElementById(id);
            const icon = document.getElementById('icon-' + id);
            if (content.classList.contains('open')) {
                content.classList.remove('open');
                icon.style.transform = 'rotate(0deg)';
            } else {
                content.classList.add('open');
                icon.style.transform = 'rotate(180deg)';
            }
        }
    </script>
</body>"""

if "upgradeModal" not in courses:
    # First, replace the existing simple toggleAccordion script
    courses = re.sub(r'<script>.*?</script>\s*</body>', modal_html, courses, flags=re.DOTALL)

# Make locked video links trigger the modal instead of navigating
courses = re.sub(r'<a href="video-player\.html" class="px-4 py-2 bg-gray-200 text-gray-500 rounded text-sm font-semibold cursor-not-allowed">Học ngay</a>',
                 r'<button onclick="showUpgradeModal()" class="px-4 py-2 bg-amber-100 text-amber-700 rounded text-sm font-semibold hover:bg-amber-200">Học ngay</button>', courses)

with open('/Users/nghiadv/Projects/video-learning-demo/courses.html', 'w', encoding='utf-8') as f:
    f.write(courses)

print("Fixed courses.html header, badges, and modal.")
