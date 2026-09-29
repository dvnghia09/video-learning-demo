import glob

old_zalo_css = """
        .zalo-icon-wrap {
            width: 60px;
            height: 60px;
"""
new_zalo_css = """
        .zalo-icon-wrap {
            width: 45px;
            height: 45px;
"""

old_zalo_text = "font-size:18px;"
new_zalo_text = "font-size:14px;"

old_img_4 = "https://images.unsplash.com/photo-1508759073847-9ea705321d4c?auto=format&fit=crop&w=600&q=80"
new_img_4 = "https://images.unsplash.com/photo-1519378058457-4c29a0a2efac?auto=format&fit=crop&w=600&q=80"

for filepath in glob.glob('/Users/nghiadv/Projects/video-learning-demo/*.html'):
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()
    
    modified = False
    
    if old_zalo_css in content:
        content = content.replace(old_zalo_css, new_zalo_css)
        modified = True
        
    if old_zalo_text in content:
        content = content.replace(old_zalo_text, new_zalo_text)
        modified = True
        
    if old_img_4 in content:
        content = content.replace(old_img_4, new_img_4)
        modified = True
        
    if modified:
        with open(filepath, 'w', encoding='utf-8') as f:
            f.write(content)
        print(f"Updated {filepath}")

