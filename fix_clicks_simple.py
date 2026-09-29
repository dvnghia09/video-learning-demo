with open('/Users/nghiadv/Projects/video-learning-demo/courses.html', 'r', encoding='utf-8') as f:
    content = f.read()

# Make all Free rows clickable to video-player.html
content = content.replace(
    '<div class="flex items-center justify-between p-3 bg-white border border-gray-200 rounded hover:border-amber-400 transition cursor-pointer">',
    '<div class="flex items-center justify-between p-3 bg-white border border-gray-200 rounded hover:border-amber-400 transition">'
)

lines = content.split('\n')
in_free_row = False
in_locked_row = False
row_start_index = -1

for i, line in enumerate(lines):
    if '<div class="flex items-center justify-between p-3 bg-white border border-gray-200 rounded hover:border-amber-400 transition">' in line:
        row_start_index = i
        in_free_row = False
        in_locked_row = False
    
    if 'MIỄN PHÍ' in line:
        in_free_row = True
    elif 'Cần nâng cấp gói' in line:
        in_locked_row = True
        
    if '<a href="video-player.html"' in line and row_start_index != -1:
        # We are at the end of the row
        if in_free_row:
            lines[row_start_index] = lines[row_start_index].replace(
                '<div class="flex items-center justify-between p-3 bg-white border border-gray-200 rounded hover:border-amber-400 transition">',
                '<div onclick="window.location.href=\'video-player.html\'" class="flex items-center justify-between p-3 bg-white border border-gray-200 rounded hover:border-amber-400 transition cursor-pointer">'
            )
        elif in_locked_row:
            lines[row_start_index] = lines[row_start_index].replace(
                '<div class="flex items-center justify-between p-3 bg-white border border-gray-200 rounded hover:border-amber-400 transition">',
                '<div onclick="showUpgradeModal()" class="flex items-center justify-between p-3 bg-white border border-gray-200 rounded hover:border-amber-400 transition cursor-pointer">'
            )
            # Replace the link with a button that doesn't navigate
            lines[i] = re.sub(r'<a href="video-player\.html" class="(.*?)">.*?</a>', r'<button type="button" class="\1">Khóa</button>', lines[i])
        
        row_start_index = -1

import re
with open('/Users/nghiadv/Projects/video-learning-demo/courses.html', 'w', encoding='utf-8') as f:
    f.write('\n'.join(lines))

print("Fixed row clicks safely.")
