import os
import urllib.request

video_url = "https://www.w3schools.com/html/mov_bbb.mp4"
video_path = "/Users/nghiadv/Projects/video-learning-demo/images/demo_video.mp4"

print("Downloading video...")
req = urllib.request.Request(video_url, headers={'User-Agent': 'Mozilla/5.0'})
with urllib.request.urlopen(req) as response, open(video_path, 'wb') as out_file:
    out_file.write(response.read())

with open('/Users/nghiadv/Projects/video-learning-demo/video-player.html', 'r', encoding='utf-8') as f:
    content = f.read()

content = content.replace(video_url, "images/demo_video.mp4")

with open('/Users/nghiadv/Projects/video-learning-demo/video-player.html', 'w', encoding='utf-8') as f:
    f.write(content)

print("Video downloaded and HTML updated.")
