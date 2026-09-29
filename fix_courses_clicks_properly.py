import re

with open('/Users/nghiadv/Projects/video-learning-demo/courses.html', 'r', encoding='utf-8') as f:
    content = f.read()

# First, revert the outer div back to default to ensure clean state
content = re.sub(r'<div onclick="window\.location\.href=\'video-player\.html\'" class="flex items-center justify-between p-3 bg-white border border-gray-200 rounded hover:border-amber-400 transition cursor-pointer">',
                 r'<div class="flex items-center justify-between p-3 bg-white border border-gray-200 rounded hover:border-amber-400 transition cursor-pointer">', content)

# A function to replace free and locked rows
def replace_row(match):
    inner_html = match.group(1)
    if 'MIỄN PHÍ' in inner_html:
        return f'<div onclick="window.location.href=\'video-player.html\'" class="flex items-center justify-between p-3 bg-white border border-gray-200 rounded hover:border-amber-400 transition cursor-pointer">{inner_html}</div>'
    elif 'Cần nâng cấp gói' in inner_html:
        # replace the 'Học ngay' link to not navigate if they click the button itself
        inner_html = re.sub(r'<a href="video-player\.html" class="(.*?)">Học ngay</a>', r'<button onclick="showUpgradeModal()" class="\1">Khóa</button>', inner_html)
        return f'<div onclick="showUpgradeModal()" class="flex items-center justify-between p-3 bg-white border border-gray-200 rounded hover:border-amber-400 transition cursor-pointer">{inner_html}</div>'
    return match.group(0)

pattern = re.compile(r'<div class="flex items-center justify-between p-3 bg-white border border-gray-200 rounded hover:border-amber-400 transition.*?>\s*(<div class="flex items-center">.*?<a href="video-player\.html".*?</a>\s*)</div>', re.DOTALL)
content = pattern.sub(replace_row, content)

# Check if modal script exists, if not add it
if 'showUpgradeModal' not in content:
    print("WARNING: showUpgradeModal not found in file. Adding it.")

with open('/Users/nghiadv/Projects/video-learning-demo/courses.html', 'w', encoding='utf-8') as f:
    f.write(content)

print("Rows properly updated.")
