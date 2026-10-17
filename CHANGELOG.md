- update file manager
- update gallery file
- ``@mediamanager(['media_type'  =>  'image', 'name' => 'image', 'label' => __('cms::post.image')]) ``

    + Nếu `media_type` = `gallery`  dùng cho gallery
    + Còn mặc đinh không có media_type sẽ upload lên được 1 hình ảnh
- `media_type` = `gallery`: modal thư viện cho chọn NHIỀU file
    + Bấm file để chọn / bấm lần nữa để bỏ; số thứ tự hiện trên dấu check = thứ tự thêm vào gallery
    + Chân modal: "Đã chọn N file" + "Bỏ chọn"; Lưu → thêm tất cả vào cuối gallery, bỏ qua file đã có
    + Lựa chọn giữ nguyên khi tìm/lọc/cuộn tải thêm; mở lại modal thì bắt đầu trống
    + Upload trong modal: file mới được thêm sau các file đang chọn rồi tự đóng modal
    + Chế độ 1 file và `media_type` = `editor` (TinyMCE) không đổi
