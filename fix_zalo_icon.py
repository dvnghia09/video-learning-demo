import glob

html_zalo = """<div style="color:white; font-weight:900; font-size:18px; font-family:Arial, sans-serif; letter-spacing:0.5px; animation: zalo-shake 2s infinite ease-in-out;">Zalo</div>"""
img_tag = '<img src="https://upload.wikimedia.org/wikipedia/commons/thumb/9/91/Icon_of_Zalo.svg/1024px-Icon_of_Zalo.svg.png" alt="Zalo">'

for filepath in glob.glob('/Users/nghiadv/Projects/video-learning-demo/*.html'):
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()
    
    if img_tag in content:
        content = content.replace(img_tag, html_zalo)
        
        with open(filepath, 'w', encoding='utf-8') as f:
            f.write(content)
            print(f"Updated {filepath}")

