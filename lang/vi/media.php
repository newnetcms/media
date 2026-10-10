<?php

return [
    'model_name' => 'Media',

    'index' => [
        'page_title'    => 'Danh sách media',
        'page_subtitle' => 'Danh sách media',
    ],

    'create' => [
        'page_title'    => 'Tạo mới Media',
        'page_subtitle' => 'Tạo mới Media',
    ],

    'edit' => [
        'page_title'    => 'Sửa Media',
        'page_subtitle' => 'Sửa Media',
    ],

    'filter' => [
        'name' => 'Tên file',
        'name_placeholder' => 'Tìm theo tên file...',
        'sort' => 'Sắp xếp',
        'clear_all' => 'Bỏ lọc',
    ],

    'sort' => [
        'created_at_desc' => 'Ngày tải lên: mới nhất',
        'created_at_asc' => 'Ngày tải lên: cũ nhất',
        'size_desc' => 'Dung lượng: lớn nhất',
        'size_asc' => 'Dung lượng: nhỏ nhất',
    ],

    'sidebar' => [
        'library' => 'Thư viện',
        'all' => 'Tất cả',
        'image' => 'Ảnh',
        'video' => 'Video',
        'audio' => 'Âm thanh',
        'document' => 'Tài liệu',
        'unattached' => 'Chưa gắn vào đâu',
        'by_time' => 'Theo thời gian',
        'month' => 'Tháng',
        'by_usage' => 'Nơi sử dụng',
        'storage' => 'Dung lượng',
    ],

    'view' => [
        'grid' => 'Dạng lưới',
        'list' => 'Dạng danh sách',
    ],

    // Modal "File manager" (form.media) — dùng chung cho rất nhiều form khác
    // (@mediamanager/@gallery), tách riêng khỏi các nhóm trên vì trang Danh
    // sách media (admin/index.blade.php) không dùng các key này.
    'picker' => [
        'title' => 'Quản lý tệp',
    ],

    'list' => [
        'type' => 'Loại',
        'size' => 'Kích thước',
        'date' => 'Ngày tải lên',
        'author' => 'Người tải lên',
        'usage' => 'Dùng ở đâu',
    ],

    'empty' => 'Không tìm thấy media nào.',
    'loading_more' => 'Đang tải thêm...',

    'stats' => [
        'showing' => 'Hiển thị :loaded / :total file',
        'total_size' => 'Tổng dung lượng',
    ],

    'upload' => [
        'title' => 'Thêm file',
        'drop_hint' => 'Thả file vào đây để tải lên',
        'error' => 'Upload thất bại',
        'unsupported_type' => 'Định dạng file không được phép',
        'single_only' => 'Chỉ được tải lên 1 file cho mục này',
    ],

    'bulk' => [
        'selected' => 'Đã chọn :count mục',
        'delete' => 'Xoá',
        'download' => 'Tải xuống (zip)',
        'copy_urls' => 'Copy URL',
        'deselect' => 'Bỏ chọn',
    ],

    'detail' => [
        'title' => 'Chi tiết file',
        'usage' => 'Dùng ở đâu',
        'no_usage' => 'Chưa gắn vào nội dung nào.',
        'file_name' => 'Tên file',
        'name' => 'Tên hiển thị',
        'alt' => 'Alt text',
        'caption' => 'Mô tả (caption)',
        'save' => 'Lưu',
        'delete' => 'Xoá',
        'download' => 'Tải xuống',
        'copy_url' => 'Copy URL',
        'open_original' => 'Mở file gốc',
        'uploaded_by' => 'Người upload',
        'uploaded_at' => 'Ngày upload',
        'size' => 'Dung lượng',
        'dimensions' => 'Kích thước',
    ],

    'confirm' => [
        'delete_title' => 'Xoá media đã chọn?',
        'delete_text' => 'Các file đã xoá không thể khôi phục.',
        'yes' => 'Xoá',
        'no' => 'Huỷ',
    ],

    'notification' => [
        'created' => 'Tạo mới media thành công!',
        'updated' => 'Cập nhật media thành công!',
        'deleted' => 'Xoá media thành công!',
        'copy_url_success' => 'Đã copy URL vào clipboard.',
        'select_at_least_one' => 'Vui lòng chọn ít nhất một mục.',
    ],
];
