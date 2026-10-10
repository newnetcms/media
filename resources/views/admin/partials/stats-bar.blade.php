{{--
    Thanh thống kê bên dưới danh sách: số file đang hiển thị (tăng dần theo
    infinite scroll)/tổng số file khớp bộ lọc hiện tại, và tổng dung lượng.
    data-total để media-manager.js tự cập nhật lại phần "đang hiển thị" mỗi khi
    tải thêm trang (không cần round-trip server).
--}}
<div class="media-stats-bar" id="mediaStatsBar">
    <span id="mediaStatsShowing" data-total="{{ $stats['count'] }}">
        {{ __('media::media.stats.showing', ['loaded' => $loadedCount, 'total' => $stats['count']]) }}
    </span>
    <span class="media-stats-bar__sep">&middot;</span>
    <span>{{ __('media::media.stats.total_size') }}: <strong>{{ \Newnet\Media\Models\Media::humanSize($stats['totalSize']) }}</strong></span>
</div>
