(function ($) {
    'use strict';

    // QUAN TRỌNG: phải đợi tới $(document).ready, không bind ngay khi script này
    // chạy. Layout admin có `new Vue({el: '#app'})` thực thi ở cuối <body> — Vue
    // mount theo kiểu này (không có flag hydrate SSR) sẽ dựng lại toàn bộ DOM bên
    // trong #app từ đầu, nên bất kỳ handler jQuery nào gắn vào DOM gốc TRƯỚC thời
    // điểm đó sẽ bị "treo" vào node cũ đã bị Vue thay thế và không còn phản hồi click
    // nữa. $(document).ready chờ tới DOMContentLoaded, tức là sau khi mọi <script>
    // đồng bộ (gồm cả script mount Vue) đã chạy xong, nên bind vào đúng DOM cuối cùng.
    $(document).ready(function () {

        var config = window.mediaManagerConfig || {};
        var csrfToken = $('meta[name="csrf-token"]').attr('content');
        var $app = $('#mediaApp');
        var $listContainer = $('#mediaListContainer');
        // Vùng cuộn thật giờ là #mediaListContainer (flex:1 bên trong .media-main),
        // không phải .media-main nữa — xem CSS: chỉ danh sách cuộn, header/thanh
        // thống kê đứng yên để thanh thống kê luôn nằm đúng đáy card.
        var $scrollContainer = $('#mediaListContainer');
        var $filterForm = $('#mediaFilterForm');
        var currentMedia = null;
        var searchDebounce = null;

        // Infinite scroll: thay cho phân trang cũ, tải thêm trang khi cuộn gần
        // đáy vùng .media-main. hasMore/nextPage lấy từ response (ajax) hoặc
        // seed sẵn từ window.mediaManagerConfig.pagination (lần tải trang đầu).
        var pagination = config.pagination || {hasMore: false, nextPage: 2};
        var currentHasMore = pagination.hasMore;
        var currentNextPage = pagination.nextPage;
        var isLoadingMore = false;

        function t(key, fallback) {
            return (config.lang && config.lang[key]) || fallback || key;
        }

        function notifySuccess(message) {
            if (window.toastr) {
                window.toastr.success(message);
            }
        }

        function notifyError(message) {
            if (window.toastr) {
                window.toastr.error(message);
            }
        }

        function confirmDelete(onConfirm) {
            if (!window.swal) {
                if (window.confirm(t('deleteText'))) {
                    onConfirm();
                }
                return;
            }

            window.swal({
                title: t('deleteTitle'),
                text: t('deleteText'),
                type: 'warning',
                showCancelButton: true,
                cancelButtonText: t('deleteNo'),
                confirmButtonColor: '#DD6B55',
                confirmButtonText: t('deleteYes'),
                closeOnConfirm: true,
            }, function (confirmed) {
                if (confirmed) {
                    onConfirm();
                }
            });
        }

        /**
         * Tải lại danh sách + sidebar bằng AJAX, giữ nguyên mọi query param hiện
         * có trên URL (bao gồm page) để không mất trạng thái lọc/phân trang.
         */
        function refreshList(url) {
            url = url || buildCurrentUrl();

            return $.ajax({
                url: url,
                method: 'GET',
                headers: {'X-Requested-With': 'XMLHttpRequest'},
            }).done(function (response) {
                if (response.status === 'C200') {
                    $listContainer.html(response.result);

                    // replaceWith() tạo hẳn DOM mới cho sidebar nên mất luôn vị trí
                    // cuộn cũ (nhảy về đầu) — lưu lại scrollTop trước khi thay, rồi
                    // set lại ngay sau đó để click vào 1 mục không làm cả sidebar
                    // giật lên đầu, nhất là khi mục đó nằm sâu bên dưới (ví dụ năm/model xa).
                    var $oldSidebar = $('#mediaSidebar');
                    var sidebarScrollTop = $oldSidebar.length ? $oldSidebar[0].scrollTop : 0;
                    $oldSidebar.replaceWith(response.sidebar);
                    $('#mediaSidebar')[0].scrollTop = sidebarScrollTop;

                    $('#mediaStatsBar').replaceWith(response.stats);
                    renderChips(response.activeChips);
                    clearSelection();
                    currentHasMore = !!response.hasMore;
                    currentNextPage = response.nextPage;
                    maybeFillViewport();
                    updateStatsShowing();
                }
                window.history.pushState({}, '', url);
                syncFormFromUrl(url);
            }).fail(function () {
                notifyError(t('uploadError', 'Có lỗi xảy ra, vui lòng thử lại.'));
            });
        }

        function buildCurrentUrl() {
            var params = $filterForm.serialize();
            return config.indexUrl + (params ? '?' + params : '');
        }

        /**
         * Tải trang kế tiếp của bộ lọc hiện tại và nối vào cuối danh sách
         * (infinite scroll) — khác refreshList() ở chỗ không render lại cả
         * wrapper/sidebar, chỉ nối thêm item.
         */
        function loadMore() {
            if (!currentHasMore || isLoadingMore) {
                return;
            }

            isLoadingMore = true;
            var $indicator = $('<div>', {class: 'media-loading-more', text: t('loadingMore', 'Đang tải thêm...')});
            $listContainer.append($indicator);

            var url = buildCurrentUrl() + '&page=' + currentNextPage + '&append=1';

            $.ajax({
                url: url,
                method: 'GET',
                headers: {'X-Requested-With': 'XMLHttpRequest'},
            }).done(function (response) {
                if (response.status === 'C200') {
                    $('#mediaItemsWrapper').append(response.result);
                    currentHasMore = !!response.hasMore;
                    currentNextPage = response.nextPage;
                    updateStatsShowing();
                }
            }).fail(function () {
                notifyError(t('uploadError', 'Không tải được thêm media.'));
            }).always(function () {
                $indicator.remove();
                isLoadingMore = false;
                maybeFillViewport();
            });
        }

        /**
         * Số "Hiển thị X / Y" luôn lấy X = số item thật đang có trong DOM (đếm
         * trực tiếp, không cần server trả lại) — tự đúng dù xem từ refreshList()
         * (trang 1) hay loadMore() (đã nối thêm trang sau) gọi tới.
         */
        function updateStatsShowing() {
            var $showing = $('#mediaStatsShowing');
            if (!$showing.length) {
                return;
            }
            var loaded = $('#mediaItemsWrapper .media-item').length;
            var total = $showing.data('total');
            $showing.text(t('statsShowing', 'Hiển thị :loaded / :total file').replace(':loaded', loaded).replace(':total', total));
        }

        /**
         * Khi danh sách chưa đủ dài để có scrollbar (ví dụ ít item, màn hình
         * cao) mà vẫn còn trang kế tiếp, tự tải thêm luôn — nếu không, cuộn
         * trong .media-main sẽ không bao giờ chạm "gần đáy" để kích hoạt loadMore().
         */
        function maybeFillViewport() {
            if (!currentHasMore || isLoadingMore) {
                return;
            }
            var el = $scrollContainer[0];
            if (el && el.scrollHeight <= el.clientHeight + 10) {
                loadMore();
            }
        }

        $scrollContainer.on('scroll', function () {
            var el = this;
            if (el.scrollTop + el.clientHeight >= el.scrollHeight - 200) {
                loadMore();
            }
        });

        /**
         * Đồng bộ lại các field của form (type/model/month/unattached ẩn, sort,
         * mode, view-toggle) theo đúng URL vừa load — cần thiết vì link sidebar
         * đổi filter KHÔNG đi qua form, nên nếu không đồng bộ lại, lần gõ tìm
         * kiếm/đổi sort tiếp theo sẽ gửi kèm giá trị type/model/month cũ đã mất tác dụng.
         */
        function syncFormFromUrl(url) {
            var queryIndex = url.indexOf('?');
            var params = new URLSearchParams(queryIndex >= 0 ? url.slice(queryIndex) : '');

            ['type', 'model', 'month', 'unattached', 'sort', 'mode'].forEach(function (key) {
                var $field = $filterForm.find('[name="' + key + '"]');
                if ($field.length) {
                    $field.val(params.get(key) || (key === 'type' || key === 'model' ? 'all' : ''));
                }
            });

            syncViewToggle(params.get('mode') || 'grid');
            syncSortActive(params.get('sort') || 'id-desc');
        }

        function renderChips(chips) {
            var $row = $('#mediaChipRow');
            if (!chips || !chips.length) {
                $row.addClass('d-none').empty();
                return;
            }
            $row.empty();
            chips.forEach(function (chip) {
                var cls = 'media-chip media-filter-link' + (chip.isClearAll ? ' media-chip--clear-all' : '');
                var $link = $('<a>', {href: chip.clearUrl, class: cls});
                $link.append($('<span>').text(chip.label));
                $link.append('<i class="fas fa-times"></i>');
                $row.append($link);
            });
            $row.removeClass('d-none');
        }

        // ------------------------------------------------------------------
        // Toolbar: search (debounced), sort, view toggle
        // ------------------------------------------------------------------
        $filterForm.on('submit', function (e) {
            e.preventDefault();
            refreshList();
        });

        $filterForm.find('input[name="name"]').on('input', function () {
            clearTimeout(searchDebounce);
            searchDebounce = setTimeout(function () {
                refreshList();
            }, 350);
        });

        $app.on('click', '.media-sort-option', function (e) {
            e.preventDefault();
            var value = $(this).data('value');
            $('#filterSort').val(value);
            syncSortActive(value);
            refreshList();
        });

        function syncSortActive(value) {
            $('.media-sort-option').removeClass('active');
            $('.media-sort-option[data-value="' + value + '"]').addClass('active');
        }

        $('.media-toolbar__view-toggle').on('click', 'button', function () {
            var mode = $(this).data('mode');
            $('#filterMode').val(mode);
            syncViewToggle(mode);
            refreshList();
        });

        function syncViewToggle(mode) {
            $('.media-toolbar__view-toggle button').removeClass('is-active');
            $('.media-toolbar__view-toggle button[data-mode="' + mode + '"]').addClass('is-active');
        }

        // ------------------------------------------------------------------
        // Sidebar: quick views / năm-tháng / nơi sử dụng / chip xoá lọc đều là
        // link thật tới cùng route index kèm query — chặn click để load AJAX
        // thay vì full reload, nhưng vẫn hoạt động được nếu JS lỗi.
        // ------------------------------------------------------------------
        $app.on('click', '.media-filter-link', function (e) {
            e.preventDefault();
            refreshList($(this).attr('href'));
        });

        $app.on('click', '[data-year-toggle]', function () {
            var $months = $(this).siblings('.media-sidebar__months');
            var isOpen = $months.is(':visible');
            $months.toggle(!isOpen);
            $(this).find('i').toggleClass('fa-chevron-down', !isOpen).toggleClass('fa-chevron-right', isOpen);
        });

        // ------------------------------------------------------------------
        // Bulk select toolbar
        // ------------------------------------------------------------------
        function selectedCheckboxes() {
            return $listContainer.find('.media-item-checkbox:checked');
        }

        // Bulk row thế chỗ form tìm kiếm/sắp xếp trong CÙNG 1 dòng của toolbar
        // (xem toolbar.blade.php) — chỉ đổi cái nào đang hiện, không chèn thêm
        // dòng mới, nên chọn/bỏ chọn item không đẩy danh sách bên dưới nhảy.
        function showBulkToolbar() {
            $filterForm.addClass('d-none');
            $('#mediaBulkToolbar').removeClass('d-none');
        }

        function clearSelection() {
            $('#mediaBulkToolbar').addClass('d-none');
            $filterForm.removeClass('d-none');
            $('#mediaBulkCount').text('');
        }

        $listContainer.on('change', '.media-item-checkbox', function () {
            $(this).closest('.media-item').toggleClass('is-selected', this.checked);

            var count = selectedCheckboxes().length;

            if (count > 0) {
                showBulkToolbar();
                $('#mediaBulkCount').text(t('selected', ':count mục đã chọn').replace(':count', count));
            } else {
                clearSelection();
            }
        });

        $('#mediaBulkDeselect').on('click', function () {
            selectedCheckboxes().prop('checked', false).closest('.media-item').removeClass('is-selected');
            clearSelection();
        });

        $('#mediaBulkDelete').on('click', function () {
            var ids = selectedCheckboxes().map(function () {
                return this.value;
            }).get();

            if (!ids.length) {
                return;
            }

            confirmDelete(function () {
                $.ajax({
                    url: config.bulkDestroyUrl,
                    method: 'POST',
                    headers: {'X-CSRF-TOKEN': csrfToken},
                    data: {ids: ids},
                }).done(function () {
                    refreshList();
                }).fail(function () {
                    notifyError(t('uploadError', 'Xoá thất bại.'));
                });
            });
        });

        $('#mediaBulkDownload').on('click', function () {
            var ids = selectedCheckboxes().map(function () {
                return this.value;
            }).get();

            if (!ids.length) {
                notifyError(t('selectAtLeastOne', 'Vui lòng chọn ít nhất một mục.'));
                return;
            }

            submitHiddenForm(config.bulkDownloadUrl, ids);
        });

        $('#mediaBulkCopyUrls').on('click', function () {
            var urls = selectedCheckboxes().map(function () {
                return $(this).closest('.media-item').data('url');
            }).get();

            if (!urls.length) {
                notifyError(t('selectAtLeastOne', 'Vui lòng chọn ít nhất một mục.'));
                return;
            }

            copyToClipboard(urls.join('\n'));
        });

        function submitHiddenForm(url, ids) {
            var $form = $('<form>', {method: 'POST', action: url, target: '_blank'});
            $form.append($('<input>', {type: 'hidden', name: '_token', value: csrfToken}));
            ids.forEach(function (id) {
                $form.append($('<input>', {type: 'hidden', name: 'ids[]', value: id}));
            });
            $form.appendTo('body').submit().remove();
        }

        function copyToClipboard(text) {
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(text).then(function () {
                    notifySuccess(t('copyUrlSuccess', 'Đã copy vào clipboard.'));
                });
            }
        }

        // ------------------------------------------------------------------
        // Upload: nút "Tải lên" mở browse file, kéo-thả hoạt động trên toàn
        // bộ card (#mediaApp) thay vì một vùng dropzone riêng.
        // ------------------------------------------------------------------
        var $fileInput = $('#mediaFileInput');
        var $uploadList = $('#mediaUploadList');
        var dragCounter = 0;

        $('#mediaUploadButton').on('click', function () {
            $fileInput.trigger('click');
        });

        $fileInput.on('change', function () {
            uploadFiles(this.files);
            $fileInput.val('');
        });

        $app.on('dragenter', function (e) {
            e.preventDefault();
            dragCounter++;
            $app.addClass('is-drag-over');
        });

        $app.on('dragover', function (e) {
            e.preventDefault();
        });

        $app.on('dragleave', function (e) {
            e.preventDefault();
            dragCounter = Math.max(0, dragCounter - 1);
            if (dragCounter === 0) {
                $app.removeClass('is-drag-over');
            }
        });

        $app.on('drop', function (e) {
            e.preventDefault();
            dragCounter = 0;
            $app.removeClass('is-drag-over');
            var files = e.originalEvent.dataTransfer.files;
            if (files && files.length) {
                uploadFiles(files);
            }
        });

        function uploadFiles(fileList) {
            Array.prototype.forEach.call(fileList, uploadOneFile);
        }

        function uploadOneFile(file) {
            var itemId = 'upload-' + Date.now() + '-' + Math.random().toString(36).slice(2);
            var $item = $(
                '<div class="media-upload-item" id="' + itemId + '">' +
                    '<span class="media-upload-item__name" title="' + escapeHtml(file.name) + '">' + escapeHtml(file.name) + '</span>' +
                    '<span class="media-upload-item__bar"><span class="media-upload-item__bar-fill"></span></span>' +
                '</div>'
            );
            $uploadList.append($item);

            var formData = new FormData();
            formData.append('image-upload[]', file);

            var xhr = new XMLHttpRequest();
            xhr.open('POST', config.uploadUrl, true);
            xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken);

            xhr.upload.onprogress = function (e) {
                if (e.lengthComputable) {
                    var percent = Math.round((e.loaded / e.total) * 100);
                    $item.find('.media-upload-item__bar-fill').css('width', percent + '%');
                }
            };

            xhr.onload = function () {
                if (xhr.status >= 200 && xhr.status < 300) {
                    $item.addClass('is-done');
                    refreshList();
                } else {
                    $item.addClass('is-error');
                    notifyError(t('uploadError', 'Upload thất bại: ' + file.name));
                }
                setTimeout(function () {
                    $item.fadeOut(300, function () {
                        $(this).remove();
                    });
                }, 1500);
            };

            xhr.onerror = function () {
                $item.addClass('is-error');
                notifyError(t('uploadError', 'Upload thất bại: ' + file.name));
            };

            xhr.send(formData);
        }

        function escapeHtml(value) {
            return $('<div>').text(value).html();
        }

        // ------------------------------------------------------------------
        // Detail drawer (slide-in từ phải, thay cho modal cũ)
        // ------------------------------------------------------------------
        var $drawer = $('#mediaDetailDrawer');
        var $backdrop = $('#mediaDetailBackdrop');

        function openDrawer() {
            $backdrop.addClass('is-open');
            $drawer.addClass('is-open');
        }

        function closeDrawer() {
            $backdrop.removeClass('is-open');
            $drawer.removeClass('is-open');
            currentMedia = null;
        }

        $backdrop.on('click', closeDrawer);
        $('#mediaDetailClose').on('click', closeDrawer);

        $listContainer.on('click', '[data-action="open-detail"]', function (e) {
            e.preventDefault();
            var id = $(this).closest('.media-item').data('id');
            openDetail(id);
        });

        function openDetail(id) {
            $.ajax({
                url: config.resourceUrlBase + '/' + id + '/edit',
                method: 'GET',
            }).done(function (response) {
                currentMedia = response.file;
                renderDetail(response);
                openDrawer();
            }).fail(function () {
                notifyError(t('uploadError', 'Không tải được thông tin file.'));
            });
        }

        function renderDetail(response) {
            var file = response.file;

            var $preview = $('#mediaDetailPreview').empty();
            if (file.type === 'image') {
                $preview.append($('<img>', {src: response.src}));
            } else if (file.type === 'video') {
                $preview.append($('<video>', {src: file.url, controls: true}));
            } else if (file.type === 'audio') {
                $preview.append($('<audio>', {src: file.url, controls: true}));
            } else {
                $preview.append($('<img>', {src: response.src}));
            }

            $('#mediaDetailDimensions').text(
                file.dimensions ? (file.dimensions.width + ' × ' + file.dimensions.height + 'px') : ''
            );
            $('#mediaDetailFileName').text(file.file_name || '');
            $('#mediaDetailSize').text(file.human_size || '');
            $('#mediaDetailAuthor').text((file.author && file.author.name) || '—');
            $('#mediaDetailDate').text(file.created_at || '');

            $('#mediaDetailName').val(file.name || '');
            $('#mediaDetailAlt').val(file.alt || '');
            $('#mediaDetailCaption').val(file.caption || '');
            $('#mediaDetailSaved').addClass('d-none');

            var $usage = $('#mediaDetailUsage').empty();
            if (!response.usages || !response.usages.length) {
                $usage.append($('<li>', {class: 'text-muted', text: t('noUsage', 'Chưa gắn vào nội dung nào.')}));
            } else {
                response.usages.forEach(function (usage) {
                    var label = usage.type + ' #' + usage.id + ' — ' + usage.label;
                    if (usage.editUrl) {
                        $usage.append($('<li>').append($('<a>', {href: usage.editUrl, target: '_blank', text: label})));
                    } else {
                        $usage.append($('<li>', {text: label}));
                    }
                });
            }

            $('#mediaDetailCopyUrl').data('url', file.url);
            $('#mediaDetailDownload').attr('href', file.url);
        }

        // Ảnh/tài liệu (preview là <img>, kể cả icon tĩnh của video/audio/doc)
        // bấm vào để mở file gốc ở tab mới — chỉ bind vào thẻ img, không bind
        // cả vùng #mediaDetailPreview, để không đụng control play/pause/seek
        // của <video>/<audio> khi preview đang là trình phát thật.
        $('#mediaDetailPreview').on('click', 'img', function () {
            if (currentMedia && currentMedia.url) {
                window.open(currentMedia.url, '_blank');
            }
        });

        $('#mediaDetailForm').on('submit', function (e) {
            e.preventDefault();
            if (!currentMedia) {
                return;
            }

            $.ajax({
                url: config.resourceUrlBase + '/' + currentMedia.id,
                method: 'PATCH',
                headers: {'X-CSRF-TOKEN': csrfToken},
                data: {
                    name: $('#mediaDetailName').val(),
                    alt: $('#mediaDetailAlt').val(),
                    caption: $('#mediaDetailCaption').val(),
                },
            }).done(function () {
                $('#mediaDetailSaved').removeClass('d-none');
                notifySuccess(t('updateSuccess', 'Cập nhật thành công.'));
                refreshList();
            }).fail(function () {
                notifyError(t('uploadError', 'Cập nhật thất bại.'));
            });
        });

        $('#mediaDetailCopyUrl').on('click', function () {
            var url = $(this).data('url');
            if (url) {
                copyToClipboard(url);
            }
        });

        $('#mediaDetailDelete').on('click', function () {
            if (!currentMedia) {
                return;
            }

            confirmDelete(function () {
                $.ajax({
                    url: config.bulkDestroyUrl,
                    method: 'POST',
                    headers: {'X-CSRF-TOKEN': csrfToken},
                    data: {ids: [currentMedia.id]},
                }).done(function () {
                    closeDrawer();
                    refreshList();
                }).fail(function () {
                    notifyError(t('uploadError', 'Xoá thất bại.'));
                });
            });
        });

        // Lần tải trang đầu (không qua AJAX) không đi qua refreshList()/loadMore()
        // nên không ai gọi maybeFillViewport() cả — nếu 1 trang đầu (24 item) không
        // đủ cao để tạo scrollbar trong .media-main, infinite scroll sẽ không bao
        // giờ có cơ hội kích hoạt. Gọi 1 lần ở đây để tự tải thêm cho tới khi lấp
        // đầy viewport hoặc hết trang.
        maybeFillViewport();

    }); // $(document).ready
})(jQuery);
