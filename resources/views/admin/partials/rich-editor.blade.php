{{-- Trình soạn thảo TinyMCE (miễn phí, GPL). Ảnh chèn vào bài được tải lên server và lưu thành LINK, không nhúng base64.
     Dùng: @include('admin.partials.rich-editor', ['selector' => '#content']) --}}
@once
<script src="{{ asset('vendor/tinymce/tinymce.min.js') }}"></script>
@endonce
<script>
document.addEventListener('DOMContentLoaded', () => {
    const uploadUrl = @js(route('admin.uploads.image'));
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || @js(csrf_token());

    tinymce.init({
        selector: @js($selector),
        license_key: 'gpl',
        language: 'vi',
        base_url: @js(asset('vendor/tinymce')), suffix: '.min',
        language_url: @js(asset('vendor/tinymce/langs/vi.js')),
        height: {{ $height ?? 520 }},
        menubar: false,
        branding: false,
        promotion: false,
        plugins: 'lists link image table code fullscreen autoresize wordcount charmap',
        toolbar: 'undo redo | blocks | bold italic underline | alignleft aligncenter alignright | bullist numlist | link image table | blockquote hr | removeformat | fullscreen code',
        block_formats: 'Đoạn văn=p; Tiêu đề lớn=h2; Tiêu đề nhỏ=h3; Tiêu đề phụ=h4',
        toolbar_mode: 'wrap',
        min_height: 420,
        max_height: 900,
        // Đường link ảnh/trang lưu nguyên dạng tương đối (/storage/...), không thêm tên miền
        relative_urls: false, remove_script_host: true, convert_urls: false,
        // Ảnh: tự động tải lên server, trong bài chỉ còn đường link
        automatic_uploads: true,
        paste_data_images: true,
        images_file_types: 'jpeg,jpg,png,gif,webp',
        image_caption: true,
        image_advtab: false,
        image_dimensions: false,
        images_upload_handler: (blobInfo) => new Promise((resolve, reject) => {
            if (blobInfo.blob().size > 5 * 1024 * 1024) return reject({ message: 'Ảnh quá lớn (tối đa 5MB).', remove: true });
            const fd = new FormData();
            fd.append('file', blobInfo.blob(), blobInfo.filename());
            fetch(uploadUrl, { method: 'POST', body: fd, credentials: 'same-origin', headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' } })
                .then(async (r) => {
                    const data = await r.json().catch(() => ({}));
                    if (!r.ok) throw new Error(data.errors?.file?.[0] || data.message || 'Tải ảnh thất bại');
                    resolve(data.location);
                })
                .catch((e) => reject({ message: e.message, remove: true }));
        }),
        content_style: "body { font-family: Inter, system-ui, sans-serif; font-size: 16px; line-height: 1.75; color: #292524; padding: 12px 16px; } img { max-width: 100%; height: auto; border-radius: 12px; } blockquote { border-left: 4px solid #d97706; margin-left: 0; padding-left: 16px; color: #57534e; } table { border-collapse: collapse; } td, th { border: 1px solid #d6d3d1; padding: 6px 10px; }",
        setup: (editor) => { editor.on('change input undo redo', () => editor.save()); },
    });
});
</script>
