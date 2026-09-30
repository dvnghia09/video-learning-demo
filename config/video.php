<?php

return [
    /*
    | Các mức chất lượng HLS: cạnh ngắn của video (px) => bitrate tối đa (kbps).
    | Chỉ tạo các mức <= độ phân giải gốc. Ít mức hơn = tốn ít dung lượng hơn.
    */
    'renditions' => [
        1080 => 4000,
        720 => 2200,
        480 => 1100,
    ],

    /*
    | Bitrate theo từng video (per-title): đo độ phức tạp của video (cảnh tĩnh hay chuyển động nhiều)
    | rồi nhân bitrate tối đa của mỗi mức với hệ số, kẹp trong [factor_min, factor_max].
    | reference_kbps: bitrate video (kbps) mà một video "bình thường" cần ở 480p với CRF hiện tại.
    */
    'per_title' => (bool) env('VIDEO_PER_TITLE', true),
    'reference_kbps' => (int) env('VIDEO_REFERENCE_KBPS', 900),
    'factor_min' => 0.6,
    'factor_max' => 1.5,

    // Chất lượng x264 (CRF): số càng lớn file càng nhẹ, hình càng kém. 23 = mặc định, 24-26 hợp video ít chuyển động.
    'crf' => (int) env('VIDEO_CRF', 24),

    // Xoá file gốc sau khi đã mã hoá HLS thành công (giải phóng dung lượng)
    'delete_original' => (bool) env('VIDEO_DELETE_ORIGINAL', true),

    // Ảnh xem trước khi rê thanh tua: tối đa bấy nhiêu ảnh, mỗi ảnh cách nhau >= 2 giây
    'thumbs_max' => 100,
];
