@push('styles')
    <style>
        /*
         * Bảng màu/kiểu bo góc khớp với trang Danh sách media (media-main):
         * primary #007bff, viền #e4e5e7, chữ #353c4e/#8a93a6, bo góc 6-8px.
         * .editImageSelected/.media-file-type-preview là class DÙNG CHUNG cho
         * rất nhiều form khác (field preview ảnh/gallery ngoài modal này) nên
         * chỉ chỉnh màu sắc/bo góc, không đổi cấu trúc — còn phần style riêng
         * của modal "File manager" thì khoanh vùng trong .modal-media-file-{{$name}}
         * để không ảnh hưởng tới modal khác trên cùng trang. Toàn bộ class/
         * data-attribute JS đang dùng (.editImageSelected, data-id/data-src/
         * data-type/data-ext, .active-img, .js-save-*, .media-preview-*,
         * .remove-media...) giữ nguyên, chỉ đổi giao diện.
         */
        .editImageSelected {
            display: block;
            cursor: pointer;
        }

        /*
         * Cấu trúc + số đo bám sát y hệt .media-items--grid .media-item ở trang
         * Danh sách media (media-manager.css): .card là khung thẻ (viền/bo góc
         * 8px), bên trong gồm 2 khối xếp dọc — .media-picker-item__preview (ảnh/
         * icon phủ kín khung cao 110px bằng object-fit:cover, không đệm trắng)
         * và .media-picker-item__info (tên file + dung lượng · ngày upload),
         * cùng kích thước chữ/màu/khoảng cách với .media-item__name/__meta.
         */
        .editImageSelected .card {
            border: 1.5px solid #eaecf0;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: none;
            background: #fff;
            margin-bottom: 0;
            transition: border-color .12s ease;
        }

        .editImageSelected:hover .card {
            border-color: #007bff;
        }

        .editImageSelected.active-img .card {
            border-color: #007bff;
            border-width: 2px;
        }

        /*
         * .icon-menu-item là flex item trong .card (Bootstrap .card luôn là
         * display:flex;flex-direction:column). Khai báo width:100% tường minh
         * thay vì trông chờ align-items:stretch mặc định — tránh lặp lại lỗi đã
         * gặp (class .col-4 cũ của Bootstrap grid từng ghi đè max-width:33% lên
         * item này, làm ảnh/tên file bị bóp hẹp còn 1/3 khung thẻ).
         */
        .editImageSelected .icon-menu-item {
            display: block;
            width: 100%;
        }

        .media-picker-item__preview {
            display: block;
            position: relative;
            width: 100%;
            height: 110px;
            background: #f3f3f3;
        }

        .media-picker-item__preview img {
            display: block;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .media-picker-item__preview .media-file-type-preview {
            width: 100%;
            height: 100%;
        }

        /* Modal không dùng checkbox (chọn 1 ảnh rồi Save) nên cần một dấu hiệu
           "đã chọn" rõ ràng thay thế — dấu check tròn góc trên phải, cùng màu
           primary với trạng thái is-selected/is-checked ở trang Danh sách. */
        .media-picker-item__check {
            display: none;
            align-items: center;
            justify-content: center;
            position: absolute;
            top: 6px;
            right: 6px;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: #007bff;
            color: #fff;
            font-size: 10px;
        }

        .editImageSelected.active-img .media-picker-item__check {
            display: flex;
        }

        .media-picker-item__info {
            display: block;
            width: 100%;
            box-sizing: border-box;
            padding: 6px 7px;
        }

        .media-picker-item__name {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #353c4e;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .media-picker-item__meta {
            display: block;
            margin-top: 2px;
            font-size: 11px;
            color: #8a93a6;
        }

        .media-file-type-preview {
            display: inline-flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            width: 100px;
            height: 100px;
            border: none;
            border-radius: 0;
            background: #f3f3f3;
            color: #8a93a6;
        }

        .media-file-type-preview i {
            font-size: 28px;
        }

        .media-file-type-preview .media-file-ext {
            margin-top: 6px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .gallery-item .media-file-type-preview {
            width: 100%;
            height: auto;
            padding: 15px 0;
        }

        /* Trong lưới Library của modal thì khớp đúng nền #eef1f5 của
           .media-file-type-preview ở trang Danh sách media (nơi khác — preview
           1 ảnh đã chọn ở field, gallery item — vẫn giữ nền #f3f3f3 cũ, không
           phải phần cần "tương đồng với media-main" theo yêu cầu). */
        .media-picker-item__preview .media-file-type-preview {
            background: #eef1f5;
        }

        /* Riêng modal "File manager" của field {{$name}} này */
        /* Mở từ dialog Chèn ảnh/Link/Media của TinyMCE (.tox-tinymce-aux có
           z-index 1300) nên phải nổi trên dialog đó; backdrop chỉnh trong JS
           lúc shown.bs.modal (Bootstrap tự tạo backdrop với z-index 1040). */
        .modal-media-file-{{$name}}.media-picker--editor {
            z-index: 1310;
        }

        .modal-media-file-{{$name}} .modal-dialog {
            max-width: 1680px;
            width: 96%;
        }

        .modal-media-file-{{$name}} .modal-content {
            position: relative;
            border: none;
            border-radius: 10px;
            overflow: hidden;
        }

        /*
         * Kéo-thả file vào bất kỳ đâu trong modal (không chỉ vùng Upload file) —
         * cấu trúc/cơ chế bật-tắt bám theo .media-drop-overlay/.is-drag-over ở
         * trang Danh sách media, chỉ thêm hiệu ứng mượt hơn cho modal: overlay
         * fade+scale vào thay vì bật/tắt đột ngột (display:none không transition
         * được nên chuyển sang opacity/visibility), viền nhấp nháy dạng "ripple"
         * và icon nảy nhẹ để nhấn mạnh rõ đang ở trạng thái thả file.
         */
        .modal-media-file-{{$name}} .media-picker-drop-overlay {
            position: absolute;
            inset: 0;
            z-index: 20;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(53, 60, 78, .65);
            border: 3px dashed #353c4e;
            border-radius: 10px;
            pointer-events: none;
            opacity: 0;
            visibility: hidden;
            transform: scale(.97);
            transition: opacity .18s ease, transform .18s ease, visibility 0s linear .18s;
        }

        .modal-media-file-{{$name}}.is-drag-over .media-picker-drop-overlay {
            opacity: 1;
            visibility: visible;
            transform: scale(1);
            transition: opacity .18s ease, transform .18s ease, visibility 0s linear 0s;
            animation: media-picker-drop-pulse 1.6s ease-out infinite;
        }

        @keyframes media-picker-drop-pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(53, 60, 78, .3);
            }
            70% {
                box-shadow: 0 0 0 14px rgba(53, 60, 78, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(53, 60, 78, 0);
            }
        }

        .modal-media-file-{{$name}} .media-picker-drop-overlay__inner {
            text-align: center;
            color: #fff;
            font-weight: 700;
        }

        .modal-media-file-{{$name}} .media-picker-drop-overlay__inner i {
            font-size: 34px;
            margin-bottom: 10px;
            display: block;
            animation: media-picker-drop-bounce 1s ease-in-out infinite;
        }

        @keyframes media-picker-drop-bounce {
            0%, 100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-6px);
            }
        }

        /*
         * Layout 2 cột giống hệt .media-app (sidebar + main) ở trang Danh sách
         * media: sidebar cuộn riêng, phần chính (toolbar + lưới) cuộn riêng —
         * nên vùng cuộn phân trang vô hạn là .js-picker-scroll-{{$name}} (riêng
         * biệt, không phải khối bọc ngoài — khối đó bọc CẢ sidebar lẫn main nên
         * không thể để cả khối đó cuộn chung được).
         */
        .modal-media-file-{{$name}} .media-picker-layout {
            display: flex;
            align-items: stretch;
            height: 100%;
        }

        .modal-media-file-{{$name}} .media-picker-sidebar {
            flex: 0 0 210px;
            width: 210px;
            height: 100%;
            overflow-y: auto;
            border-right: 1px solid #e4e5e7;
            padding: 2px 14px 2px 2px;
        }

        .modal-media-file-{{$name}} .media-picker-main {
            flex: 1 1 auto;
            min-width: 0;
            height: 100%;
            display: flex;
            flex-direction: column;
            padding-left: 18px;
        }

        .modal-media-file-{{$name}} .media-picker-scroll {
            flex: 1 1 auto;
            min-height: 0;
            overflow-y: auto;
        }

        /*
         * Library tab trước đây dùng lưới Bootstrap .row/.col-md-2 (luôn đúng
         * 6 cột, modal càng rộng thì card càng to chứ không hiện thêm cột) —
         * đổi sang CSS grid auto-fill giống hệt .media-items--grid ở trang
         * Danh sách media: card giữ kích thước ~140px, modal rộng ra thì tự
         * xếp thêm cột. Specificity 2 class (.modal-media-file-x .js-height-popup-x)
         * cao hơn .row/.col-md-2 của Bootstrap (1 class) nên không cần !important.
         */
        .modal-media-file-{{$name}} .js-height-popup-{{$name}} {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 14px;
            margin: 0;
        }

        .modal-media-file-{{$name}} .editImageSelected {
            width: auto;
            max-width: none;
            flex: none;
            padding: 0;
        }

        /*
         * Thanh search/sort/chuyển grid-list — giờ nằm NGOÀI vùng cuộn
         * (.media-picker-scroll), trong .media-picker-main (flex-column), nên
         * luôn cố định phía trên mà không cần position:sticky nữa.
         */
        .modal-media-file-{{$name}} .media-picker-toolbar {
            flex: 0 0 auto;
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 2px 2px 12px;
        }

        .modal-media-file-{{$name}} .media-picker-toolbar__search {
            position: relative;
            flex: 1 1 240px;
            max-width: 320px;
        }

        .modal-media-file-{{$name}} .media-picker-toolbar__search i {
            position: absolute;
            left: 11px;
            top: 50%;
            transform: translateY(-50%);
            color: #b0b7c3;
            font-size: 12px;
        }

        .modal-media-file-{{$name}} .media-picker-toolbar__search input {
            width: 100%;
            padding: 7px 10px 7px 32px;
            border: 1px solid #e4e5e7;
            border-radius: 6px;
            font-size: 13px;
        }

        .modal-media-file-{{$name}} .media-picker-toolbar__search input:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 .15rem rgba(0, 123, 255, .15);
        }

        .modal-media-file-{{$name}} .media-picker-toolbar__right {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-left: auto;
        }

        .modal-media-file-{{$name}} .media-picker-icon-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border: 1px solid #e4e5e7;
            border-radius: 6px;
            background: #fff;
            color: #8a93a6;
            cursor: pointer;
        }

        .modal-media-file-{{$name}} .media-picker-icon-btn:hover,
        .modal-media-file-{{$name}} .media-picker-icon-btn[aria-expanded="true"] {
            background: #e7f1ff;
            color: #007bff;
            border-color: #e7f1ff;
        }

        .modal-media-file-{{$name}} .media-picker-icon-btn:focus:not(:focus-visible) {
            outline: none;
        }

        .modal-media-file-{{$name}} .media-picker-icon-btn:focus-visible {
            outline: 2px solid #007bff;
            outline-offset: 1px;
        }

        .modal-media-file-{{$name}} .media-picker-sort-menu {
            min-width: 190px;
            padding: 6px;
            font-size: 13px;
        }

        .modal-media-file-{{$name}} .media-picker-sort-option {
            display: flex;
            align-items: center;
            gap: 8px;
            border-radius: 5px;
            padding: 7px 9px;
            color: #353c4e;
        }

        .modal-media-file-{{$name}} .media-picker-sort-option i {
            width: 13px;
            color: #8a93a6;
            text-align: center;
            font-size: 12px;
        }

        .modal-media-file-{{$name}} .media-picker-sort-option.is-active,
        .modal-media-file-{{$name}} .media-picker-sort-option:active {
            background: #e7f1ff;
            color: #007bff;
        }

        .modal-media-file-{{$name}} .media-picker-sort-option.is-active i {
            color: #007bff;
        }

        .modal-media-file-{{$name}} .media-picker-view-toggle {
            display: flex;
            border: 1px solid #e4e5e7;
            border-radius: 6px;
            overflow: hidden;
        }

        .modal-media-file-{{$name}} .media-picker-view-toggle button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border: none;
            background: #fff;
            color: #8a93a6;
            cursor: pointer;
        }

        .modal-media-file-{{$name}} .media-picker-view-toggle button + button {
            border-left: 1px solid #e4e5e7;
        }

        .modal-media-file-{{$name}} .media-picker-view-toggle button:focus:not(:focus-visible) {
            outline: none;
        }

        .modal-media-file-{{$name}} .media-picker-view-toggle button:focus-visible {
            outline: 2px solid #007bff;
            outline-offset: -2px;
        }

        .modal-media-file-{{$name}} .media-picker-view-toggle button.is-active {
            background: #e7f1ff;
            color: #007bff;
        }

        /*
         * Chế độ list — bật bằng cách thêm .is-list-view lên .js-height-popup-{{$name}}.
         * Số đo/khoảng cách bám theo đúng .media-items--list .media-item ở trang
         * Danh sách media: hàng phẳng không viền thẻ, chỉ có border-bottom ngăn
         * cách, thumbnail nhỏ 48px bên trái, và 5 cột loại/dung lượng/ngày/người
         * tải lên/nơi dùng bên phải tên — đúng những cột trang Danh sách media
         * đang hiển thị ở chế độ list.
         */
        .modal-media-file-{{$name}} .js-height-popup-{{$name}}.is-list-view {
            display: flex;
            flex-direction: column;
            gap: 0;
        }

        .modal-media-file-{{$name}} .js-height-popup-{{$name}}.is-list-view .editImageSelected .card {
            border: none;
            border-radius: 0;
            background: transparent;
        }

        .modal-media-file-{{$name}} .js-height-popup-{{$name}}.is-list-view .icon-menu-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 7px 4px;
            border-bottom: 1px solid #eaecf0;
            border-radius: 0;
        }

        .modal-media-file-{{$name}} .js-height-popup-{{$name}}.is-list-view .editImageSelected:hover .icon-menu-item {
            background: #fafbfc;
        }

        .modal-media-file-{{$name}} .js-height-popup-{{$name}}.is-list-view .editImageSelected.active-img .icon-menu-item {
            background: #f5f9ff;
        }

        .modal-media-file-{{$name}} .js-height-popup-{{$name}}.is-list-view .media-picker-item__preview {
            flex: 0 0 48px;
            width: 48px;
            height: 48px;
            border-radius: 6px;
        }

        .modal-media-file-{{$name}} .js-height-popup-{{$name}}.is-list-view .media-picker-item__info {
            flex: 1 1 auto;
            min-width: 0;
            padding: 0;
        }

        .modal-media-file-{{$name}} .js-height-popup-{{$name}}.is-list-view .media-picker-item__meta {
            display: none;
        }

        .modal-media-file-{{$name}} .js-height-popup-{{$name}}.is-list-view .media-picker-item__filename {
            display: block;
        }

        .modal-media-file-{{$name}} .js-height-popup-{{$name}}.is-list-view .media-picker-item__check {
            width: 15px;
            height: 15px;
            font-size: 7px;
            top: 1px;
            right: 1px;
        }

        .modal-media-file-{{$name}} .js-height-popup-{{$name}}.is-list-view .media-picker-item__col {
            display: block;
        }

        /* Tên file gốc dưới tên hiển thị — chỉ hiện ở chế độ list, giống .media-item__filename */
        .media-picker-item__filename {
            display: none;
            font-size: 11px;
            color: #b0b7c3;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        /* 5 cột loại/dung lượng/ngày/người tải lên/nơi dùng — ẩn ở chế độ grid,
           chỉ .is-list-view ở trên mới bật lại thành flex item. */
        .media-picker-item__col {
            display: none;
            flex: 0 0 auto;
            font-size: 12px;
            color: #8a93a6;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .media-picker-item__col--type {
            width: 54px;
            text-transform: uppercase;
            font-weight: 700;
        }

        .media-picker-item__col--size {
            width: 76px;
        }

        .media-picker-item__col--date {
            width: 92px;
        }

        .media-picker-item__col--author {
            width: 130px;
        }

        .media-picker-item__col--usage {
            width: 150px;
        }

        .media-picker-item__usage-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 10px;
            background: #f3f3f3;
            color: #5b5a55;
            font-size: 11px;
            font-weight: 600;
        }

        /* Hàng tiêu đề cột, chỉ hiện khi bật chế độ list (toggle qua JS, không
           phụ thuộc được vào .is-list-view vì hàng này nằm NGOÀI .js-height-popup). */
        .modal-media-file-{{$name}} .media-picker-list-head {
            display: none;
            align-items: center;
            gap: 12px;
            padding: 0 4px 8px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .03em;
            color: #8a93a6;
            border-bottom: 2px solid #e4e5e7;
            margin-bottom: 4px;
        }

        .modal-media-file-{{$name}} .media-picker-list-head.is-active {
            display: flex;
        }

        .modal-media-file-{{$name}} .media-picker-list-head.is-active .media-picker-item__col {
            display: block;
        }

        .modal-media-file-{{$name}} .media-picker-list-head__spacer {
            flex: 0 0 48px;
        }

        .modal-media-file-{{$name}} .media-picker-list-head__name {
            flex: 1 1 auto;
            min-width: 0;
        }

        /* Chip hiển thị các bộ lọc đang bật (loại file/tháng/nơi sử dụng) —
           bám đúng .media-chip-row/.media-chip ở trang Danh sách media. */
        .modal-media-file-{{$name}} .media-picker-chip-row {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 12px;
        }

        .modal-media-file-{{$name}} .media-picker-chip {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            background: #e7f1ff;
            color: #007bff;
            border-radius: 20px;
            padding: 5px 9px 5px 12px;
            font-size: 12px;
            font-weight: 700;
            text-decoration: none;
        }

        .modal-media-file-{{$name}} .media-picker-chip i {
            font-size: 10px;
        }

        .modal-media-file-{{$name}} .media-picker-chip--clear-all {
            background: #f3f3f3;
            color: #5b5a55;
        }

        /* Danh sách progress bar khi đang upload (kéo-thả hoặc chọn file) —
           bám đúng .media-upload-list/.media-upload-item ở trang Danh sách media. */
        .modal-media-file-{{$name}} .media-picker-upload-list:not(:empty) {
            margin-bottom: 12px;
        }

        .modal-media-file-{{$name}} .media-picker-upload-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 5px 2px;
            font-size: 12.5px;
        }

        .modal-media-file-{{$name}} .media-picker-upload-item__name {
            flex: 0 0 180px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            color: #353c4e;
        }

        .modal-media-file-{{$name}} .media-picker-upload-item__bar {
            flex: 1;
            height: 6px;
            background: #eaecf0;
            border-radius: 3px;
            overflow: hidden;
        }

        .modal-media-file-{{$name}} .media-picker-upload-item__bar-fill {
            /* Là <span> (inline mặc định) — CSS width không có tác dụng trên
               phần tử inline, nên trước giờ set width qua JS vẫn không thấy
               chạy. display:block để width thực sự áp dụng được. */
            display: block;
            height: 100%;
            width: 0;
            background: #007bff;
            transition: width .2s ease;
        }

        .modal-media-file-{{$name}} .media-picker-upload-item.is-error .media-picker-upload-item__bar-fill {
            background: #dc3545;
        }

        .modal-media-file-{{$name}} .media-picker-upload-item__error {
            flex: 0 1 auto;
            color: #dc3545;
            font-size: 12px;
            font-weight: 600;
            white-space: nowrap;
        }

        .modal-media-file-{{$name}} .media-picker-upload-item.is-done .media-picker-upload-item__bar-fill {
            background: #28a745;
        }

        /*
         * Sidebar của modal — số đo/màu bám theo đúng .media-sidebar ở trang
         * Danh sách media, chỉ hẹp hơn (210px so với 240px) cho vừa trong modal.
         * Nội dung (đếm theo loại/tháng/nơi dùng) do picker-sidebar.blade.php
         * render lại mỗi lần đổi bộ lọc (xem reloadImg() bên dưới).
         */
        .modal-media-file-{{$name}} .media-picker-sidebar__section {
            margin-bottom: 20px;
        }

        .modal-media-file-{{$name}} .media-picker-sidebar__heading {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .05em;
            text-transform: uppercase;
            color: #8a93a6;
            padding: 0 8px;
            margin-bottom: 6px;
        }

        .modal-media-file-{{$name}} .media-picker-sidebar__link {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 6px 8px;
            border-radius: 5px;
            font-size: 13px;
            font-weight: 600;
            color: #353c4e;
            text-decoration: none;
        }

        .modal-media-file-{{$name}} .media-picker-sidebar__link:hover {
            background: #f3f3f3;
            color: #353c4e;
        }

        .modal-media-file-{{$name}} .media-picker-sidebar__link.is-active {
            background: #e7f1ff;
            color: #007bff;
        }

        .modal-media-file-{{$name}} .media-picker-sidebar__link--sub {
            padding-left: 20px;
            font-weight: 500;
        }

        .modal-media-file-{{$name}} .media-picker-sidebar__toggle {
            margin-bottom: 4px;
        }

        .modal-media-file-{{$name}} .media-picker-sidebar__count {
            font-size: 11px;
            font-weight: 700;
            color: #b0b7c3;
        }

        .modal-media-file-{{$name}} .media-picker-sidebar__link.is-active .media-picker-sidebar__count {
            color: #007bff;
        }

        .modal-media-file-{{$name}} .media-picker-sidebar__year-toggle {
            display: flex;
            align-items: center;
            gap: 7px;
            width: 100%;
            background: none;
            border: none;
            padding: 6px 8px;
            font-size: 12px;
            font-weight: 700;
            color: #8a93a6;
            cursor: pointer;
        }

        .modal-media-file-{{$name}} .media-picker-sidebar__year-toggle i {
            font-size: 10px;
            width: 10px;
        }

        .modal-media-file-{{$name}} .media-picker__header {
            align-items: center;
            padding: 16px 22px;
            border-bottom: 1px solid #e4e5e7;
        }

        .modal-media-file-{{$name}} .media-picker__title {
            margin: 0;
            font-size: 16px;
            font-weight: 700;
            color: #353c4e;
        }

        .modal-media-file-{{$name}} .media-picker__title i {
            color: #007bff;
            margin-right: 8px;
            font-size: 15px;
        }

        .modal-media-file-{{$name}} .media-picker__close {
            border: none;
            background: none;
            color: #8a93a6;
            font-size: 16px;
            opacity: 1;
            text-shadow: none;
        }

        .modal-media-file-{{$name}} .media-picker__close:hover {
            color: #353c4e;
        }

        .modal-media-file-{{$name}} .media-picker__body {
            padding: 18px 22px;
            background: #fafbfc;
        }

        /* Nút "Thêm file" trong toolbar — bám đúng .media-btn/.media-btn--primary
           ở trang Danh sách media, thay cho tab "Upload file" riêng (đã bỏ). */
        .modal-media-file-{{$name}} .media-picker-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border: 1px solid #e4e5e7;
            background: #fff;
            color: #353c4e;
            border-radius: 6px;
            padding: 7px 13px;
            font-size: 12.5px;
            font-weight: 600;
            cursor: pointer;
            white-space: nowrap;
        }

        .modal-media-file-{{$name}} .media-picker-btn--primary {
            background: #007bff;
            border-color: #007bff;
            color: #fff;
        }

        .modal-media-file-{{$name}} .media-picker-btn--primary:hover {
            background: #0069d9;
            border-color: #0062cc;
            color: #fff;
        }

        .modal-media-file-{{$name}} .media-picker__footer {
            border-top: 1px solid #e4e5e7;
            padding: 14px 22px;
        }

        .modal-media-file-{{$name}} .js-save-img-media-{{$name}},
        .modal-media-file-{{$name}} .js-save-gallery-media-{{$name}} {
            background: #007bff;
            border-color: #007bff;
            border-radius: 6px;
            font-weight: 600;
            padding: 8px 20px;
        }

        .modal-media-file-{{$name}} .js-save-img-media-{{$name}}:hover,
        .modal-media-file-{{$name}} .js-save-gallery-media-{{$name}}:hover {
            background: #0069d9;
            border-color: #0062cc;
        }
    </style>
@endpush
{{-- media_type=editor: chỉ có modal, không có field (preview/hidden input) —
     file chọn được trả về cho TinyMCE qua window.NewnetMediaPicker.open() --}}
{{-- Dạng block, không dùng dạng inline: Blade ghép block php/endphp từ chỗ
     "php" ĐẦU TIÊN trong file, trộn 2 dạng sẽ nuốt cả đoạn markup ở giữa. --}}
@php
    $isEditorPicker = isset($media_type) && $media_type == 'editor';
@endphp
@if(isset($media_type) && $media_type == 'gallery')
    @include('media::form.gallery')
@elseif(!$isEditorPicker)
    @include('media::form.file')
@endif

<div class="modal fade modal-media-file-{{$name}} {{ $isEditorPicker ? 'media-picker--editor' : '' }}"
     tabindex="-1"
     role="dialog"
     aria-labelledby="myLargeModalLabel"
     aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="media-picker-drop-overlay">
                <div class="media-picker-drop-overlay__inner">
                    <i class="fas fa-cloud-upload-alt"></i>
                    <div>{{ __('media::media.upload.drop_hint') }}</div>
                </div>
            </div>
            <div class="modal-header media-picker__header">
                <h3 class="media-picker__title" id="myLargeModalLabel">
                    <i class="fas fa-photo-video"></i>{{ __('media::media.picker.title') }}</h3>
                <button type="button" class="close media-picker__close" data-dismiss="modal" aria-label="Close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body media-picker__body">
                <div style="height: calc(100vh - 250px); min-height: 420px; max-height: 680px; overflow: hidden">
                    <div class="media-picker-layout">
                        <aside class="media-picker-sidebar js-picker-sidebar-{{$name}}"></aside>
                        <div class="media-picker-main">
                            <div class="media-picker-toolbar">
                                <div class="media-picker-toolbar__search">
                                    <i class="fas fa-search"></i>
                                    <input type="text" class="js-search-media-{{$name}}" placeholder="{{ __('media::media.filter.name_placeholder') }}">
                                </div>
                                <div class="media-picker-toolbar__right">
                                    <div class="dropdown">
                                        <button type="button" class="media-picker-icon-btn js-sort-toggle-{{$name}}"
                                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="{{ __('media::media.filter.sort') }}">
                                            <i class="fas fa-sort-amount-down"></i>
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-right media-picker-sort-menu">
                                            <a href="#" class="media-picker-sort-option js-sort-option-{{$name}} is-active"
                                               data-sort-field="created_at" data-sort-dir="desc"><i class="fas fa-arrow-down"></i> {{ __('media::media.sort.created_at_desc') }}</a>
                                            <a href="#" class="media-picker-sort-option js-sort-option-{{$name}}"
                                               data-sort-field="created_at" data-sort-dir="asc"><i class="fas fa-arrow-up"></i> {{ __('media::media.sort.created_at_asc') }}</a>
                                            <a href="#" class="media-picker-sort-option js-sort-option-{{$name}}"
                                               data-sort-field="size" data-sort-dir="desc"><i class="fas fa-arrow-down"></i> {{ __('media::media.sort.size_desc') }}</a>
                                            <a href="#" class="media-picker-sort-option js-sort-option-{{$name}}"
                                               data-sort-field="size" data-sort-dir="asc"><i class="fas fa-arrow-up"></i> {{ __('media::media.sort.size_asc') }}</a>
                                        </div>
                                    </div>
                                    <div class="media-picker-view-toggle">
                                        <button type="button" class="js-view-grid-{{$name}} is-active" title="{{ __('media::media.view.grid') }}"><i class="fas fa-th-large"></i></button>
                                        <button type="button" class="js-view-list-{{$name}}" title="{{ __('media::media.view.list') }}"><i class="fas fa-list"></i></button>
                                    </div>
                                </div>
                                <button type="button" class="media-picker-btn media-picker-btn--primary js-upload-trigger-{{$name}}">
                                    <i class="fas fa-cloud-upload-alt"></i> {{ __('media::media.upload.title') }}
                                </button>
                                <input type="file" id="image-upload-{{$name}}" name="image-upload[]" style="display: none"
                                       accept="{{ collect(config('cms.media.accept_upload_extension', []))->map(fn ($ext) => '.' . strtolower($ext))->implode(',') }}"
                                       @if(isset($media_type) && $media_type == 'gallery') multiple @endif>
                            </div>
                            <div class="js-picker-chip-row-{{$name}}"></div>
                            <div class="media-picker-upload-list js-picker-upload-list-{{$name}}"></div>
                            <div class="media-picker-list-head js-list-head-{{$name}}">
                                <span class="media-picker-list-head__spacer"></span>
                                <span class="media-picker-list-head__name">{{ __('media::media.filter.name') }}</span>
                                <span class="media-picker-item__col media-picker-item__col--type">{{ __('media::media.list.type') }}</span>
                                <span class="media-picker-item__col media-picker-item__col--size">{{ __('media::media.list.size') }}</span>
                                <span class="media-picker-item__col media-picker-item__col--date">{{ __('media::media.list.date') }}</span>
                                <span class="media-picker-item__col media-picker-item__col--author">{{ __('media::media.list.author') }}</span>
                                <span class="media-picker-item__col media-picker-item__col--usage">{{ __('media::media.list.usage') }}</span>
                            </div>
                            <div class="media-picker-scroll js-picker-scroll-{{$name}}">
                                <div class="row js-height-popup-{{$name}} js-modal-html-{{$name}}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer media-picker__footer">
                @if(isset($media_type) && $media_type == 'gallery')
                    <button class="btn btn-success js-save-gallery-media-{{$name}}" type="button">{{ __('media::media.detail.save') }}</button>
                @else
                    <button class="btn btn-success js-save-img-media-{{$name}}" type="button">{{ __('media::media.detail.save') }}</button>
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
    @php
        // Tính sẵn ra biến rồi mới đưa vào @json: directive @json tách tham số
        // bằng dấu phẩy (regex), truyền thẳng mảng/biểu thức nhiều dấu phẩy sẽ
        // bị cắt cụt thành PHP lỗi cú pháp.
        $pickerIsGallery = isset($media_type) && $media_type == 'gallery';
        $pickerAllowedExtensions = array_values(array_map('strtolower', config('cms.media.accept_upload_extension', [])));
        $pickerImageExtensions = \Newnet\Media\Models\Media::DISPLAYABLE_IMAGE_EXTENSIONS;
        $pickerUploadMessages = [
            'error' => __('media::media.upload.error'),
            'unsupportedType' => __('media::media.upload.unsupported_type'),
            'singleOnly' => __('media::media.upload.single_only'),
            'imageRequired' => __('media::media.picker.image_required'),
            'mediaRequired' => __('media::media.picker.media_required'),
        ];
    @endphp
    <script>
        $(document).ready(function () {
            var paginate = 1;
            var currentSearch = '';
            var currentSortField = 'created_at';
            var currentSortDir = 'desc';
            var currentType = 'all';
            var currentModel = 'all';
            var currentMonth = '';
            var currentUnattached = 0;
            var currentHasMore = false;
            var isLoadingMore = false;
            var searchDebounce = null;
            let dataId = null;
            let dataImg = null
            let dataType = null
            let dataExt = null
            let dataUrl = null
            let dataName = null
            let dataKind = null

            // Mirrors lib/media/resources/views/form/partials/file-type-icon.blade.php - keep the two in sync
            function fileIconClass(ext) {
                var map = {
                    PDF: 'fa-file-pdf',
                    DOC: 'fa-file-word', DOCX: 'fa-file-word',
                    XLS: 'fa-file-excel', XLSX: 'fa-file-excel', CSV: 'fa-file-excel',
                    PPT: 'fa-file-powerpoint', PPTX: 'fa-file-powerpoint',
                    ZIP: 'fa-file-archive', RAR: 'fa-file-archive', '7Z': 'fa-file-archive', TAR: 'fa-file-archive', GZ: 'fa-file-archive',
                    MP3: 'fa-file-audio', WAV: 'fa-file-audio', OGG: 'fa-file-audio',
                    MP4: 'fa-file-video', MOV: 'fa-file-video', AVI: 'fa-file-video', WMV: 'fa-file-video', MKV: 'fa-file-video',
                    TXT: 'fa-file-alt'
                }
                return map[(ext || '').toUpperCase()] || 'fa-file'
            }

            function fileTypePreviewHtml(ext) {
                return `
                    <div class="media-file-type-preview">
                        <i class="fas ${fileIconClass(ext)}"></i>
                        <span class="media-file-ext">${ext || ''}</span>
                    </div>
                `
            }

            function mediaQueryParams(page) {
                var params = {
                    name: currentSearch,
                    type: currentType,
                    model: currentModel,
                    month: currentMonth,
                    unattached: currentUnattached,
                    sort_field: currentSortField,
                    sort_dir: currentSortDir
                }
                if (page) {
                    params.page = page
                }
                return params
            }

            function reloadImg() {
                $.ajax({
                    url: '{{ route('media.admin.media.ajaxMedia') }}',
                    type: 'GET',
                    data: $.extend({with_sidebar: 1}, mediaQueryParams()),
                    success: function (res) {
                        $('.js-modal-html-{{$name}}').html(res.result)
                        if (typeof res.sidebar !== 'undefined') {
                            $('.js-picker-sidebar-{{$name}}').html(res.sidebar)
                        }
                        if (typeof res.chips !== 'undefined') {
                            $('.js-picker-chip-row-{{$name}}').html(res.chips)
                        }
                        currentHasMore = !!res.hasMore
                        paginate = 1
                        maybeFillViewport()
                    },
                    error: function (err) {

                    }
                })
            }

            function appendImg(page) {
                if (isLoadingMore) {
                    return
                }
                isLoadingMore = true
                $.ajax({
                    url: '{{ route('media.admin.media.ajaxMedia') }}',
                    type: 'GET',
                    data: mediaQueryParams(page),
                    success: function (res) {
                        $('.js-modal-html-{{$name}}').append(res.result)
                        currentHasMore = !!res.hasMore
                    },
                    error: function (err) {

                    },
                    complete: function () {
                        isLoadingMore = false
                        maybeFillViewport()
                    }
                })
            }

            // Modal mở lần đầu (hoặc màn hình cao) có thể chưa đủ item để tạo
            // scrollbar trong .js-picker-scroll-{{$name}} — khi đó cuộn không bao
            // giờ "chạm đáy" để kích hoạt phân trang, nên tự tải thêm luôn nếu còn
            // trang kế tiếp. Cùng cơ chế với maybeFillViewport() ở media-manager.js.
            function maybeFillViewport() {
                if (!currentHasMore || isLoadingMore) {
                    return
                }
                var el = $('.js-picker-scroll-{{$name}}')[0]
                if (el && el.scrollHeight <= el.clientHeight + 10) {
                    paginate = paginate + 1
                    appendImg(paginate)
                }
            }

            $('.js-click-modal-media-{{$name}}').click(function () {
                if (paginate == 1 && !$('.js-modal-html-{{$name}}').html()) {
                    reloadImg()
                }
            })

            $('.js-search-media-{{$name}}').on('input', function () {
                var val = $(this).val()
                clearTimeout(searchDebounce)
                searchDebounce = setTimeout(function () {
                    currentSearch = val
                    reloadImg()
                }, 350)
            })

            $('.js-sort-option-{{$name}}').click(function (e) {
                e.preventDefault()
                currentSortField = $(this).data('sort-field')
                currentSortDir = $(this).data('sort-dir')
                $('.js-sort-option-{{$name}}').removeClass('is-active')
                $(this).addClass('is-active')
                reloadImg()
            })

            $('.js-view-grid-{{$name}}').click(function () {
                $('.js-height-popup-{{$name}}').removeClass('is-list-view')
                $('.js-list-head-{{$name}}').removeClass('is-active')
                $(this).addClass('is-active')
                $('.js-view-list-{{$name}}').removeClass('is-active')
            })

            $('.js-view-list-{{$name}}').click(function () {
                $('.js-height-popup-{{$name}}').addClass('is-list-view')
                $('.js-list-head-{{$name}}').addClass('is-active')
                $(this).addClass('is-active')
                $('.js-view-grid-{{$name}}').removeClass('is-active')
            })

            // Sidebar (loại file / theo tháng / theo nơi sử dụng) render lại
            // qua AJAX mỗi khi đổi bộ lọc (xem reloadImg()), nên dùng delegation
            // trên .js-picker-sidebar-{{$name}} (gốc tĩnh, không bị thay thế)
            // thay vì bind trực tiếp lên nội dung sẽ bị .html() ghi đè.
            $('.js-picker-sidebar-{{$name}}').on('click', '.js-picker-filter', function (e) {
                e.preventDefault()
                var filter = $(this).data('filter')
                var value = $(this).data('value')

                if (filter === 'type') {
                    currentType = value || 'all'
                } else if (filter === 'month') {
                    currentMonth = value || ''
                } else if (filter === 'model') {
                    currentModel = value || 'all'
                    currentUnattached = 0
                } else if (filter === 'unattached') {
                    currentUnattached = value ? 1 : 0
                    currentModel = 'all'
                }

                reloadImg()
            })

            $('.js-picker-sidebar-{{$name}}').on('click', '.js-picker-year-toggle', function () {
                var $months = $(this).siblings('.media-picker-sidebar__months')
                var isOpen = $months.is(':visible')
                $months.toggle(!isOpen)
                $(this).find('i').toggleClass('fa-chevron-down', !isOpen).toggleClass('fa-chevron-right', isOpen)
            })

            // Chip hiển thị bộ lọc đang bật — bấm để bỏ đúng 1 chiều (hoặc bỏ
            // hết, chip "clearFilter=all"), cùng cơ chế data-attribute + reload
            // như sidebar ở trên, không điều hướng rời trang form.
            $('.js-picker-chip-row-{{$name}}').on('click', '.js-picker-clear-chip', function (e) {
                e.preventDefault()
                var clearFilter = $(this).data('clear-filter')

                if (clearFilter === 'type') {
                    currentType = 'all'
                } else if (clearFilter === 'month') {
                    currentMonth = ''
                } else if (clearFilter === 'model') {
                    currentModel = 'all'
                } else if (clearFilter === 'unattached') {
                    currentUnattached = 0
                } else if (clearFilter === 'all') {
                    currentType = 'all'
                    currentModel = 'all'
                    currentMonth = ''
                    currentUnattached = 0
                }

                reloadImg()
            })

            function escapeHtml(value) {
                return $('<div>').text(value).html()
            }

            // Field đã mở modal: mặc định giữ 1 file, media_type=gallery giữ nhiều
            // file — quyết định số file được upload mỗi lượt và cách tự chọn file
            // vừa upload vào field (xem finishUploadBatch()). Lưu ý: không viết tên
            // directive Blade (dạng @tên) trong comment ở file này — Blade vẫn biên
            // dịch nó kể cả trong comment JS, gây include đệ quy chính view này.
            var isGalleryMode = @json($pickerIsGallery);
            // media_type=editor: 1 modal dùng chung cho mọi TinyMCE trên trang (nhúng
            // từ form/editor.blade.php của admin-ui), file chọn được trả về qua
            // onSelect của window.NewnetMediaPicker.open() thay vì ghi vào field.
            var isEditorMode = @json($isEditorPicker);
            // Cùng allowlist mà MediaUploader::verifyExtension() kiểm tra ở server —
            // chặn sớm ở client để khỏi tải cả file lên rồi mới bị từ chối.
            var allowedExtensions = @json($pickerAllowedExtensions);
            var uploadMessages = @json($pickerUploadMessages);

            // Theo loại dialog TinyMCE gọi tới (meta.filetype): "image" chỉ nhận ảnh
            // trình duyệt hiển thị được (dùng làm src của <img>), "media" nhận
            // video/audio, "file" (dialog Link) nhận mọi file được phép.
            var editorExtensionGroups = {
                image: @json($pickerImageExtensions),
                media: ['mp4', 'm4v', 'mpg', 'mov', 'avi', 'ogv', 'wmv', '3gp', '3g2', 'mp3', 'ogg', 'wav']
            }
            var editorRequiredKinds = {image: ['image'], media: ['video', 'audio']}
            var editorInitialType = {image: 'image', media: 'video'}
            var editorRequest = null

            function currentAllowedExtensions() {
                var group = editorRequest && editorExtensionGroups[editorRequest.filetype]
                if (!group) {
                    return allowedExtensions
                }
                return allowedExtensions.filter(function (ext) {
                    return group.indexOf(ext) !== -1
                })
            }

            function fileExtension(fileName) {
                var parts = (fileName || '').split('.')
                return parts.length > 1 ? parts.pop().toLowerCase() : ''
            }

            function createUploadItem(label) {
                var $item = $(
                    '<div class="media-picker-upload-item">' +
                        '<span class="media-picker-upload-item__name" title="' + escapeHtml(label) + '">' + escapeHtml(label) + '</span>' +
                        '<span class="media-picker-upload-item__bar"><span class="media-picker-upload-item__bar-fill"></span></span>' +
                    '</div>'
                )
                $('.js-picker-upload-list-{{$name}}').append($item)
                return $item
            }

            function removeUploadItemLater($item, delay) {
                setTimeout(function () {
                    $item.fadeOut(300, function () {
                        $(this).remove()
                    })
                }, delay)
            }

            // Dòng lỗi giữ lâu hơn dòng thành công (4s so với 1.5s) để kịp đọc lý do.
            function markUploadItemError($item, message) {
                $item.addClass('is-error')
                $item.find('.media-picker-upload-item__bar-fill').css('width', '100%')
                $item.append('<span class="media-picker-upload-item__error">' + escapeHtml(message || uploadMessages.error) + '</span>')
                removeUploadItemLater($item, 4000)
            }

            function parseUploadResponse(xhr) {
                try {
                    return JSON.parse(xhr.responseText)
                } catch (e) {
                    return null
                }
            }

            // Raw XMLHttpRequest thay vì $.ajax để đọc được xhr.upload.onprogress
            // (jQuery không expose progress event của chiều upload) — mỗi file có
            // 1 dòng progress bar riêng, bám đúng uploadOneFile() ở media-manager.js.
            function uploadOneFile(file, batch, index) {
                var $item = createUploadItem(file.name)

                var formData = new FormData()
                formData.append('image-upload[]', file)

                var xhr = new XMLHttpRequest()
                xhr.open('POST', '{{ route('media.admin.media.storeAjax') }}', true)
                xhr.setRequestHeader('X-CSRF-TOKEN', $('meta[name="csrf-token"]').attr('content'))

                xhr.upload.onprogress = function (e) {
                    if (e.lengthComputable) {
                        var percent = Math.round((e.loaded / e.total) * 100)
                        $item.find('.media-picker-upload-item__bar-fill').css('width', percent + '%')
                    }
                }

                xhr.onload = function () {
                    var res = parseUploadResponse(xhr)
                    if (xhr.status >= 200 && xhr.status < 300 && res && res.media) {
                        $item.find('.media-picker-upload-item__bar-fill').css('width', '100%')
                        $item.addClass('is-done')
                        removeUploadItemLater($item, 1500)
                        batch.uploaded[index] = res.media
                    } else {
                        markUploadItemError($item, res && res.message)
                        batch.failed = true
                    }
                    settleUpload(batch)
                }

                xhr.onerror = function () {
                    markUploadItemError($item, uploadMessages.error)
                    batch.failed = true
                    settleUpload(batch)
                }

                xhr.send(formData)
            }

            function settleUpload(batch) {
                batch.pending--
                if (batch.pending === 0) {
                    finishUploadBatch(batch)
                }
            }

            // Tự chọn file vừa upload vào field đã mở modal, như bấm Lưu: field 1
            // file thì thay file đang chọn, gallery thì thêm vào cuối (đúng thứ tự
            // đã chọn/kéo-thả, không theo thứ tự file nào upload xong trước). Chỉ
            // đóng modal khi cả lượt đều thành công — có file lỗi thì giữ modal mở
            // để còn đọc được lý do ở dòng lỗi.
            function finishUploadBatch(batch) {
                var uploaded = batch.uploaded.filter(Boolean)

                if (isEditorMode) {
                    reloadImg()
                    if (uploaded.length && !batch.failed) {
                        setTimeout(function () {
                            deliverToEditor(uploaded[0])
                        }, 600)
                    }
                    return
                }

                if (uploaded.length) {
                    if (isGalleryMode) {
                        uploaded.forEach(appendGalleryItem)
                    } else {
                        applySingleSelection(uploaded[0])
                    }
                }

                reloadImg()

                if (uploaded.length && !batch.failed) {
                    setTimeout(function () {
                        $('.modal-media-file-{{$name}}').modal('hide')
                    }, 600)
                }
            }

            function uploadFiles(fileList) {
                var files = Array.prototype.slice.call(fileList || [])
                if (!files.length) {
                    return
                }

                if (!isGalleryMode && files.length > 1) {
                    var names = files.map(function (file) {
                        return file.name
                    }).join(', ')
                    markUploadItemError(createUploadItem(names), uploadMessages.singleOnly)
                    return
                }

                var batch = {pending: 0, failed: false, uploaded: []}
                var uploadExtensions = currentAllowedExtensions()
                var accepted = files.filter(function (file) {
                    if (uploadExtensions.indexOf(fileExtension(file.name)) === -1) {
                        markUploadItemError(createUploadItem(file.name), uploadMessages.unsupportedType)
                        batch.failed = true
                        return false
                    }
                    return true
                })

                batch.pending = accepted.length
                accepted.forEach(function (file, index) {
                    uploadOneFile(file, batch, index)
                })
            }

            $('.js-upload-trigger-{{$name}}').click(function () {
                $('#image-upload-{{$name}}').trigger('click')
            })

            $('#image-upload-{{$name}}').change(function () {
                // uploadFiles() copy FileList sang mảng ngay, nên reset value ngay
                // sau đó vẫn an toàn (và cho phép chọn lại đúng file vừa chọn).
                uploadFiles(this.files)
                $(this).val('')
            })

            // Kéo-thả file vào bất kỳ đâu trong modal (không chỉ nút "Thêm file")
            // — cùng cơ chế dragCounter + .is-drag-over như #mediaApp ở trang Danh
            // sách media, để dragenter/dragleave trên các phần tử con bên trong
            // không làm overlay nhấp nháy tắt/mở liên tục.
            var dragCounter = 0
            var $modalRoot = $('.modal-media-file-{{$name}}')

            $modalRoot.on('dragenter', function (e) {
                e.preventDefault()
                dragCounter++
                $modalRoot.addClass('is-drag-over')
            })

            $modalRoot.on('dragover', function (e) {
                e.preventDefault()
            })

            $modalRoot.on('dragleave', function (e) {
                e.preventDefault()
                dragCounter = Math.max(0, dragCounter - 1)
                if (dragCounter === 0) {
                    $modalRoot.removeClass('is-drag-over')
                }
            })

            $modalRoot.on('drop', function (e) {
                e.preventDefault()
                dragCounter = 0
                $modalRoot.removeClass('is-drag-over')
                var files = e.originalEvent.dataTransfer.files
                if (files && files.length) {
                    uploadFiles(files)
                }
            })

            $('.js-picker-scroll-{{$name}}').scroll(function () {
                if (!currentHasMore || isLoadingMore) {
                    return
                }
                let heightfirst = $(this).scrollTop() + $(this).height()
                let heightSecond = $(this).get(0).scrollHeight
                if (Math.round(heightfirst) == Math.round(heightSecond)) {
                    paginate = paginate + 1;
                    appendImg(paginate)
                }
                // appendImg(2)
            })

            // Khoanh vùng trong đúng modal của instance này: trước đây bind trên
            // document nên 1 trang có nhiều picker (vd field ảnh + picker của
            // TinyMCE) thì bấm chọn ở modal này cũng ghi đè lựa chọn của modal kia.
            $('.js-modal-html-{{$name}}').on('click', '.editImageSelected', function () {
                $('.js-modal-html-{{$name}} .editImageSelected').removeClass('active-img')
                $(this).addClass('active-img')
                dataId = $(this).attr('data-id');
                dataImg = $(this).attr('data-src')
                dataType = $(this).attr('data-type')
                dataExt = $(this).attr('data-ext')
                dataUrl = $(this).attr('data-url')
                dataName = $(this).attr('data-name')
                dataKind = $(this).attr('data-kind')
            })

            // Dùng chung cho nút Lưu (file đang chọn trong thư viện) và tự chọn
            // sau khi upload (finishUploadBatch) — media: {id, src, type, ext}.
            function applySingleSelection(media) {
                var $preview = $('.media-preview-{{$name}}')
                $preview.find('input').remove()
                $preview.find('img').remove()
                $preview.find('.media-file-type-preview').remove()

                $preview.append('<input type="hidden" name="{{ $name }}" value="' + escapeHtml(media.id) + '">')
                if (media.type === 'image') {
                    $preview.append('<img src="' + escapeHtml(media.src) + '" alt="Image" class="img-thumbnail">')
                } else {
                    $preview.append(fileTypePreviewHtml(escapeHtml(media.ext)))
                }

                // form/file.blade.php luôn render sẵn nút xoá trong preview; chỉ
                // thêm khi chưa có (vd đã bấm xoá — media.js xoá trắng cả preview),
                // để mỗi lần chọn lại không sinh thêm 1 nút xoá trùng.
                if (!$preview.find('.remove-media').length) {
                    $preview.append('<a href="#" class="remove-media"><i class="fas fa-times-circle"></i></a>')
                }
            }

            function appendGalleryItem(media) {
                var preview = media.type === 'image'
                    ? '<img src="' + escapeHtml(media.src) + '" alt="Image">'
                    : fileTypePreviewHtml(escapeHtml(media.ext))

                $('.gallery-list-{{$name}}').append(
                    '<div class="gallery-item ui-sortable-handle">' + preview +
                        '<input type="hidden" name="{{ $name }}[]" value="' + escapeHtml(media.id) + '">' +
                        '<a href="#" title="Delete Image" class="remove-media"><i class="fas fa-times-circle"></i></a>' +
                    '</div>'
                )
            }

            function selectedMedia() {
                return dataId ? {
                    id: dataId,
                    src: dataImg,
                    type: dataType,
                    ext: dataExt,
                    url: dataUrl,
                    name: dataName,
                    kind: dataKind
                } : null
            }

            // Trả file đã chọn/vừa upload về cho TinyMCE — chặn sai loại so với
            // dialog đang mở (vd dialog Chèn ảnh không nhận PDF làm src ảnh), báo
            // lỗi ngay trong modal và giữ modal mở để chọn lại.
            function deliverToEditor(media) {
                if (!editorRequest) {
                    return
                }

                var requiredKinds = editorRequiredKinds[editorRequest.filetype]
                if (requiredKinds && requiredKinds.indexOf(media.kind) === -1) {
                    var message = editorRequest.filetype === 'image' ? uploadMessages.imageRequired : uploadMessages.mediaRequired
                    markUploadItemError(createUploadItem(media.name || media.ext || ''), message)
                    return
                }

                var request = editorRequest
                editorRequest = null
                request.onSelect(media)
                $('.modal-media-file-{{$name}}').modal('hide')
            }

            $('.js-save-img-media-{{$name}}').click(function () {
                var media = selectedMedia()

                if (isEditorMode) {
                    if (media) {
                        deliverToEditor(media)
                    } else {
                        $('.modal-media-file-{{$name}}').modal('hide')
                    }
                    return
                }

                if (media) {
                    applySingleSelection(media)
                }
                $('.modal-media-file-{{$name}}').modal('hide');
            })

            $('.js-save-gallery-media-{{$name}}').click(function () {
                var media = selectedMedia()
                if (media) {
                    appendGalleryItem(media)
                }
                $('.modal-media-file-{{$name}}').modal('hide');
            })

            if (isEditorMode) {
                // Đưa modal ra thẳng <body>: tránh bị kẹt trong stacking context của
                // phần tử cha (khi đó z-index 1310 không vượt được dialog TinyMCE
                // vốn gắn ở body), và để input file bên trong không bị submit theo
                // form chứa editor.
                var $editorModal = $('.modal-media-file-{{$name}}').appendTo('body')

                window.NewnetMediaPicker = {
                    // options: {filetype: 'image' | 'media' | 'file', onSelect: function (media) {}}
                    // media: {id, url, name, kind, src, type, ext} — url là link file gốc.
                    open: function (options) {
                        editorRequest = options
                        dataId = null
                        $('.js-modal-html-{{$name}} .editImageSelected').removeClass('active-img')
                        $('#image-upload-{{$name}}').attr('accept', currentAllowedExtensions().map(function (ext) {
                            return '.' + ext
                        }).join(','))

                        var initialType = editorInitialType[options.filetype] || 'all'
                        if (currentType !== initialType || !$('.js-modal-html-{{$name}}').html()) {
                            currentType = initialType
                            reloadImg()
                        }

                        $editorModal.modal('show')
                    }
                }

                $editorModal.on('shown.bs.modal', function () {
                    $('.modal-backdrop').last().css('z-index', 1305)
                })

                $editorModal.on('hidden.bs.modal', function () {
                    editorRequest = null
                })
            }
            })
    </script>
@endpush
