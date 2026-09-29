filepath = '/Users/nghiadv/Projects/video-learning-demo/courses.html'
with open(filepath, 'r', encoding='utf-8') as f:
    content = f.read()

# Add class "lesson-card" to each lesson div
content = content.replace('<div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">', '<div class="lesson-card bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">')

# Insert the search bar and id for the list
search_bar_html = """
        <div class="max-w-3xl mx-auto mb-8 relative">
            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>
            <input type="text" id="searchInput" onkeyup="searchLessons()" placeholder="Nhập tên bài học cần tìm..." class="w-full pl-12 pr-4 py-4 rounded-lg border border-gray-300 focus:outline-none focus:border-pink-500 focus:ring-2 focus:ring-pink-200 transition shadow-sm text-gray-700 font-medium">
        </div>

        <div class="max-w-3xl mx-auto space-y-4" id="lessonList">
"""
content = content.replace('<div class="max-w-3xl mx-auto space-y-4">', search_bar_html)

# Add search JS function
search_js = """
        function searchLessons() {
            let input = document.getElementById('searchInput').value.toLowerCase();
            let cards = document.getElementsByClassName('lesson-card');
            
            for (let i = 0; i < cards.length; i++) {
                let title = cards[i].querySelector('h2').innerText.toLowerCase();
                if (title.indexOf(input) > -1) {
                    cards[i].style.display = "";
                } else {
                    cards[i].style.display = "none";
                }
            }
        }
"""
content = content.replace("function toggleAccordion(id) {", search_js + "\n        function toggleAccordion(id) {")

with open(filepath, 'w', encoding='utf-8') as f:
    f.write(content)
print("Updated successfully")
