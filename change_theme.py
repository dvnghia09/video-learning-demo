import glob
import re

replacements = {
    'pink-500': 'amber-600',
    'pink-600': 'amber-700',
    'pink-400': 'amber-500',
    'pink-300': 'amber-400',
    'pink-200': 'amber-200',
    'pink-100': 'amber-100',
    'pink-50': 'amber-50',
    'pink-700': 'amber-800',
    'pink-800': 'amber-900',
}

for filepath in glob.glob('/Users/nghiadv/Projects/video-learning-demo/*.html'):
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()
        
    for old, new in replacements.items():
        content = content.replace(old, new)
        
    with open(filepath, 'w', encoding='utf-8') as f:
        f.write(content)

print("Theme changed to amber globally.")
