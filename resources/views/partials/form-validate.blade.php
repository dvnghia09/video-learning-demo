{{-- Kiểm tra ô nhập ngay trên trình duyệt với thông báo TIẾNG VIỆT (thay cho câu tiếng Anh mặc định của trình duyệt).
     Ô có data-vn-phone sẽ được kiểm tra số điện thoại di động Việt Nam. Máy chủ vẫn kiểm tra lại đầy đủ. --}}
<script>
(function () {
    const PHONE_MSG = 'Số điện thoại chưa đúng. Hãy nhập số di động 10 chữ số, bắt đầu bằng 03, 05, 07, 08 hoặc 09 (ví dụ: 0912345678).';
    const cap = (s) => s.charAt(0).toUpperCase() + s.slice(1);

    function labelOf(el) {
        let t = '';
        if (el.id) { const l = document.querySelector('label[for="' + el.id + '"]'); if (l) t = l.textContent; }
        if (!t) { const l = el.closest('label'); if (l) t = l.textContent; }
        if (!t) t = el.getAttribute('aria-label') || el.placeholder || '';
        t = t.replace(/\([^)]*\)/g, ' ').replace(/[*:]/g, ' ').replace(/\s+/g, ' ').trim().toLowerCase();
        return t || 'thông tin này';
    }
    function phoneOk(v) {
        v = v.replace(/[\s.\-()]+/g, '').replace(/^\+?84(\d{9})$/, '0$1');
        return /^0[35789]\d{8}$/.test(v);
    }
    function check(el) {
        el.setCustomValidity('');
        const v = el.value;
        if (el.hasAttribute('data-vn-phone') && v.trim() !== '' && !phoneOk(v)) el.setCustomValidity(PHONE_MSG);
        // Trình duyệt chỉ báo "quá ngắn" khi người dùng tự gõ; kiểm tra thêm để cả giá trị điền sẵn/tự điền cũng được báo
        else if (el.minLength > 0 && v !== '' && v.length < el.minLength) el.setCustomValidity(cap(labelOf(el)) + ' phải có ít nhất ' + el.minLength + ' ký tự (hiện mới có ' + v.length + ').');
        // Ô nhập lại phải khớp ô gốc (data-match="#password")
        else if (el.dataset.match && v !== '' && v !== (document.querySelector(el.dataset.match) || {}).value) el.setCustomValidity('Mật khẩu nhập lại chưa khớp. Vui lòng nhập lại cho đúng.');
    }

    const refresh = (e) => {
        const el = e.target;
        if (el && el.setCustomValidity) { check(el); document.querySelectorAll('[data-match]').forEach(check); }
    };
    document.addEventListener('input', refresh, true);
    document.addEventListener('change', refresh, true);
    document.querySelectorAll('[data-vn-phone], [minlength], [data-match]').forEach(check);   // ô đã có sẵn giá trị

    // Trình duyệt sắp hiện bong bóng cảnh báo: thay bằng tiếng Việt
    document.addEventListener('invalid', (e) => {
        const el = e.target, v = el.validity;
        if (!v) return;
        check(el);
        if (v.customError) return;                       // đã có thông báo tuỳ chỉnh (số điện thoại...)
        const label = labelOf(el);
        let m = 'Giá trị chưa hợp lệ.';
        if (v.valueMissing) m = el.type === 'checkbox' ? 'Vui lòng tích vào ô này để tiếp tục.' : (el.tagName === 'SELECT' || el.type === 'file' ? 'Vui lòng chọn ' + label + '.' : (label.startsWith('nhập') ? 'Vui lòng ' + label + '.' : 'Vui lòng nhập ' + label + '.'));
        else if (v.typeMismatch) m = el.type === 'email' ? 'Email chưa đúng định dạng (ví dụ: ten@gmail.com).' : 'Vui lòng nhập đường link đầy đủ, bắt đầu bằng https://';
        else if (v.tooShort) m = cap(label) + ' phải có ít nhất ' + el.minLength + ' ký tự (hiện mới có ' + el.value.length + ').';
        else if (v.tooLong) m = cap(label) + ' tối đa ' + el.maxLength + ' ký tự.';
        else if (v.patternMismatch) m = el.title || 'Định dạng chưa đúng.';
        else if (v.rangeUnderflow) m = 'Giá trị phải từ ' + el.min + ' trở lên.';
        else if (v.rangeOverflow) m = 'Giá trị không được lớn hơn ' + el.max + '.';
        el.setCustomValidity(m);
    }, true);
})();
</script>
