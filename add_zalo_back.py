zalo_html = """
    <!-- Floating Zalo -->
    <a href="https://zalo.me/0123456789" target="_blank" class="zalo-floating-btn">
        <div class="zalo-icon-wrap">
            <div style="color:white; font-weight:900; font-size:14px; font-family:Arial, sans-serif; letter-spacing:0.5px; animation: zalo-shake 2s infinite ease-in-out;">Zalo</div>
        </div>
    </a>
</body>"""

for path in ['/Users/nghiadv/Projects/video-learning-demo/login.html', '/Users/nghiadv/Projects/video-learning-demo/register.html']:
    with open(path, 'r', encoding='utf-8') as f:
        content = f.read()
    if 'zalo-floating-btn' not in content:
        content = content.replace('</body>', zalo_html)
        with open(path, 'w', encoding='utf-8') as f:
            f.write(content)
