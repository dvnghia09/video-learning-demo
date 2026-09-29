import re

with open('/Users/nghiadv/Projects/video-learning-demo/courses.html', 'r', encoding='utf-8') as f:
    content = f.read()

# Make FREE video rows clickable
old_free_pattern = r'<div class="flex items-center justify-between p-3 bg-white border border-gray-200 rounded hover:border-amber-400 transition">\s*(<div class="flex items-center">.*?</div>\s*<a href="video-player.html")'
new_free_replacement = r'<div onclick="window.location.href=\'video-player.html\'" class="flex items-center justify-between p-3 bg-white border border-gray-200 rounded hover:border-amber-400 transition cursor-pointer">\n                        \1'
content = re.sub(old_free_pattern, new_free_replacement, content, flags=re.DOTALL)

# Make LOCKED video rows clickable
old_locked_pattern = r'<div class="flex items-center justify-between p-3 bg-white border border-gray-200 rounded hover:border-amber-400 transition">\s*(<div class="flex items-center">.*?</div>\s*<button onclick="showUpgradeModal\(\)")'
new_locked_replacement = r'<div onclick="showUpgradeModal()" class="flex items-center justify-between p-3 bg-white border border-gray-200 rounded hover:border-amber-400 transition cursor-pointer">\n                        \1'
content = re.sub(old_locked_pattern, new_locked_replacement, content, flags=re.DOTALL)

with open('/Users/nghiadv/Projects/video-learning-demo/courses.html', 'w', encoding='utf-8') as f:
    f.write(content)

print("Updated video rows to be fully clickable.")
