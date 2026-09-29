import os
import glob

css = """
    <style>
        .zalo-floating-btn {
            position: fixed;
            bottom: 24px;
            right: 24px;
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
        }

        @media (max-width: 768px) {
            .zalo-floating-btn {
                bottom: 16px;
                right: 16px;
            }
        }

        .zalo-icon-wrap {
            width: 60px;
            height: 60px;
            background-color: #0068ff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 10px rgba(0,0,0,0.3);
            animation: zalo-pulse 2s infinite;
        }

        .zalo-icon-wrap img {
            width: 35px;
            height: 35px;
            object-fit: contain;
            animation: zalo-shake 2s infinite ease-in-out;
        }

        @keyframes zalo-pulse {
            0% { box-shadow: 0 0 0 0 rgba(0, 104, 255, 0.7); }
            70% { box-shadow: 0 0 0 15px rgba(0, 104, 255, 0); }
            100% { box-shadow: 0 0 0 0 rgba(0, 104, 255, 0); }
        }

        @keyframes zalo-shake {
            0%, 100% { transform: rotate(0deg); }
            10%, 30%, 50%, 70%, 90% { transform: rotate(-10deg) scale(1.1); }
            20%, 40%, 60%, 80% { transform: rotate(10deg) scale(1.1); }
        }
    </style>
"""

html = """
    <!-- Floating Zalo -->
    <a href="https://zalo.me/0123456789" target="_blank" class="zalo-floating-btn">
        <div class="zalo-icon-wrap">
            <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/9/91/Icon_of_Zalo.svg/1024px-Icon_of_Zalo.svg.png" alt="Zalo">
        </div>
    </a>
"""

for filepath in glob.glob('/Users/nghiadv/Projects/video-learning-demo/*.html'):
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()
    
    if "zalo-floating-btn" not in content:
        content = content.replace("</head>", css + "</head>")
        content = content.replace("</body>", html + "</body>")
        
        with open(filepath, 'w', encoding='utf-8') as f:
            f.write(content)
            print(f"Updated {filepath}")

