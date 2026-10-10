{{--
    Chỉ phần item (không bọc wrapper .media-items) — dùng khi infinite scroll
    tải thêm trang kế tiếp để nối vào #mediaItemsWrapper hiện có, khác với
    media-grid.blade.php (bọc cả wrapper, dùng cho lần render đầu/khi đổi filter).
--}}
@forelse($medias as $media)
    @include('media::admin.partials.media-item', ['media' => $media, 'mode' => $mode ?? 'grid'])
@empty
    <div class="media-empty text-center text-muted py-5">
        {{ __('media::media.empty') }}
    </div>
@endforelse
