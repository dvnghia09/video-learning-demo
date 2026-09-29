import os
import urllib.request
import re
import glob

# Create images directory
os.makedirs('/Users/nghiadv/Projects/video-learning-demo/images', exist_ok=True)

# Image mapping
image_urls = {
    'hero-flower.jpg': 'https://images.unsplash.com/photo-1561181286-d3fee7d55364?auto=format&fit=crop&w=800&q=80',
    'gallery-1.jpg': 'https://images.unsplash.com/photo-1563241527-3004b7be0ffd?auto=format&fit=crop&w=600&q=80',
    'gallery-2.jpg': 'https://images.unsplash.com/photo-1507290439931-a861b5a38200?auto=format&fit=crop&w=600&q=80',
    'gallery-3.jpg': 'https://images.unsplash.com/photo-1582794543139-8ac9cb0f7b11?auto=format&fit=crop&w=600&q=80',
    'gallery-4.jpg': 'https://images.unsplash.com/photo-1508610048659-a06b669e3321?auto=format&fit=crop&w=600&q=80'
}

# Download images
for filename, url in image_urls.items():
    filepath = f'/Users/nghiadv/Projects/video-learning-demo/images/{filename}'
    print(f"Downloading {filename}...")
    req = urllib.request.Request(url, headers={'User-Agent': 'Mozilla/5.0'})
    with urllib.request.urlopen(req) as response, open(filepath, 'wb') as out_file:
        out_file.write(response.read())

# Replace in index.html
with open('/Users/nghiadv/Projects/video-learning-demo/index.html', 'r', encoding='utf-8') as f:
    content = f.read()

# Replace hero
content = re.sub(r'https://images\.unsplash\.com/photo-1561181286-[^"]+', 'images/hero-flower.jpg', content)

# Replace gallery (we just replace the 4 <img> tags in the grid entirely to use our new local images)
old_gallery_pattern = r'<div class="grid grid-cols-2 md:grid-cols-4 gap-4">.*?</div>'
new_gallery = """<div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <img src="images/gallery-1.jpg" alt="Lẵng hoa nghệ thuật 1" class="rounded-2xl h-48 sm:h-64 w-full object-cover shadow-md hover:scale-105 transition duration-300">
                <img src="images/gallery-2.jpg" alt="Lẵng hoa nghệ thuật 2" class="rounded-2xl h-48 sm:h-64 w-full object-cover shadow-md hover:scale-105 transition duration-300 mt-0 md:mt-8">
                <img src="images/gallery-3.jpg" alt="Lẵng hoa nghệ thuật 3" class="rounded-2xl h-48 sm:h-64 w-full object-cover shadow-md hover:scale-105 transition duration-300">
                <img src="images/gallery-4.jpg" alt="Lẵng hoa nghệ thuật 4" class="rounded-2xl h-48 sm:h-64 w-full object-cover shadow-md hover:scale-105 transition duration-300 mt-0 md:mt-8">
            </div>"""
content = re.sub(old_gallery_pattern, new_gallery, content, flags=re.DOTALL)

with open('/Users/nghiadv/Projects/video-learning-demo/index.html', 'w', encoding='utf-8') as f:
    f.write(content)

# Replace in courses.html
with open('/Users/nghiadv/Projects/video-learning-demo/courses.html', 'r', encoding='utf-8') as f:
    content = f.read()

# Replace thumbnails
content = re.sub(r'https://images\.unsplash\.com/photo-1563241527[^"]+', 'images/gallery-1.jpg', content)
content = re.sub(r'https://images\.unsplash\.com/photo-1526047932273[^"]+', 'images/gallery-2.jpg', content)
content = re.sub(r'https://images\.unsplash\.com/photo-1457089328109[^"]+', 'images/gallery-3.jpg', content)
content = re.sub(r'https://images\.unsplash\.com/photo-1490750967868[^"]+', 'images/gallery-4.jpg', content)

with open('/Users/nghiadv/Projects/video-learning-demo/courses.html', 'w', encoding='utf-8') as f:
    f.write(content)

print("Images downloaded and HTML updated.")
