with open('/Users/nghiadv/Projects/video-learning-demo/courses.html', 'r', encoding='utf-8') as f:
    content = f.read()

# Make sure it's clean
content = content.replace(' cursor-pointer', '')
content = content.replace("onclick=\"window.location.href='video-player.html'\" ", '')
content = content.replace('onclick="showUpgradeModal()" ', '')
content = content.replace('<button type="button" class="px-4 py-2 bg-gray-100 text-gray-600 rounded text-sm font-semibold hover:bg-gray-200">Khóa</button>', '<a href="video-player.html" class="px-4 py-2 bg-gray-100 text-gray-600 rounded text-sm font-semibold hover:bg-gray-200">Khóa</a>')

# Split by the row wrapper
rows = content.split('<div class="flex items-center justify-between p-3 bg-white border border-gray-200 rounded hover:border-amber-400 transition">')

new_content = rows[0]

for i in range(1, len(rows)):
    row = rows[i]
    if 'MIỄN PHÍ' in row:
        new_content += '<div onclick="window.location.href=\'video-player.html\'" class="flex items-center justify-between p-3 bg-white border border-gray-200 rounded hover:border-amber-400 transition cursor-pointer">' + row
    elif 'Cần nâng cấp' in row:
        row = row.replace('<a href="video-player.html" class="px-4 py-2 bg-gray-100 text-gray-600 rounded text-sm font-semibold hover:bg-gray-200">Khóa</a>', '<button type="button" class="px-4 py-2 bg-gray-100 text-gray-600 rounded text-sm font-semibold hover:bg-gray-200">Khóa</button>')
        new_content += '<div onclick="showUpgradeModal()" class="flex items-center justify-between p-3 bg-white border border-gray-200 rounded hover:border-amber-400 transition cursor-pointer">' + row
    else:
        new_content += '<div class="flex items-center justify-between p-3 bg-white border border-gray-200 rounded hover:border-amber-400 transition">' + row

with open('/Users/nghiadv/Projects/video-learning-demo/courses.html', 'w', encoding='utf-8') as f:
    f.write(new_content)

print("Manual fix successful.")
