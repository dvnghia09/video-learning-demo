import re

with open('/Users/nghiadv/Projects/video-learning-demo/courses.html', 'r', encoding='utf-8') as f:
    content = f.read()

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
    </script>
</body>
"""

if 'id="upgradeModal"' not in content:
    content = content.replace('</body>', modal_html)
    with open('/Users/nghiadv/Projects/video-learning-demo/courses.html', 'w', encoding='utf-8') as f:
        f.write(content)
    print("Added modal successfully.")
else:
    print("Modal already exists.")
