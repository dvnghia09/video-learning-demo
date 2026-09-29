filepath = '/Users/nghiadv/Projects/video-learning-demo/index.html'
with open(filepath, 'r', encoding='utf-8') as f:
    content = f.read()

# Fix Hero Image height
content = content.replace('h-[500px] w-full', 'h-64 sm:h-80 md:h-[500px] w-full')

# Fix Gallery Image heights to be responsive
content = content.replace('h-64 w-full', 'h-48 sm:h-64 w-full')

# Add explicit overflow-hidden to body to prevent any accidental horizontal scrolling
if 'body class="bg-white text-gray-800"' in content:
    content = content.replace('body class="bg-white text-gray-800"', 'body class="bg-white text-gray-800 overflow-x-hidden"')

with open(filepath, 'w', encoding='utf-8') as f:
    f.write(content)

print("Applied ultra responsive fixes")
