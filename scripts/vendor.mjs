// Sao chép thư viện JS/CSS/font từ node_modules vào public/vendor để website tự phục vụ, không gọi CDN.
// Chạy: npm run assets   (kết quả được commit, nên server production không cần Node)
import { cpSync, mkdirSync, copyFileSync, rmSync, readdirSync } from 'node:fs';

const nm = 'node_modules', out = 'public/vendor';
rmSync(out, { recursive: true, force: true });
const put = (from, to) => { mkdirSync(`${out}/${to.split('/').slice(0, -1).join('/')}`, { recursive: true }); copyFileSync(`${nm}/${from}`, `${out}/${to}`); };

put('alpinejs/dist/cdn.min.js', 'alpine/alpine.min.js');
put('@alpinejs/collapse/dist/cdn.min.js', 'alpine/collapse.min.js');
put('chart.js/dist/chart.umd.js', 'chart/chart.umd.js');
put('axios/dist/axios.min.js', 'axios/axios.min.js');
put('sortablejs/Sortable.min.js', 'sortable/Sortable.min.js');
put('hls.js/dist/hls.min.js', 'hls/hls.min.js');
put('plyr/dist/plyr.polyfilled.min.js', 'plyr/plyr.polyfilled.min.js');
put('plyr/dist/plyr.css', 'plyr/plyr.css');
put('plyr/dist/plyr.svg', 'plyr/plyr.svg');

// TinyMCE: chỉ chép phần cần dùng (giao diện, giao diện nội dung, các plugin đang bật, tiếng Việt)
put('tinymce/tinymce.min.js', 'tinymce/tinymce.min.js');
put('tinymce-i18n/langs7/vi.js', 'tinymce/langs/vi.js');
for (const d of ['themes/silver', 'models/dom', 'icons/default', 'skins/ui/oxide', 'skins/content/default'])
    cpSync(`${nm}/tinymce/${d}`, `${out}/tinymce/${d}`, { recursive: true });
for (const p of ['lists', 'link', 'image', 'table', 'code', 'fullscreen', 'autoresize', 'wordcount', 'charmap'])
    cpSync(`${nm}/tinymce/plugins/${p}`, `${out}/tinymce/plugins/${p}`, { recursive: true });

// Font Inter (tự lưu, hỗ trợ tiếng Việt)
mkdirSync(`${out}/fonts`, { recursive: true });
for (const f of readdirSync(`${nm}/@fontsource/inter/files`))
    if (/^inter-(latin|latin-ext|vietnamese)-(400|500|600|700|800)-normal\.woff2$/.test(f)) copyFileSync(`${nm}/@fontsource/inter/files/${f}`, `${out}/fonts/${f}`);
console.log('Đã chép thư viện vào', out);
