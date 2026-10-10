{{--
    Partial duy nhất cho khu vực danh sách media, dùng chung cho cả lần tải
    trang đầu (index.blade.php) và mỗi lần refresh qua AJAX (MediaController::index)
    khi đổi filter/sort/mode. Infinite scroll tải thêm trang dùng riêng
    media-items-only.blade.php để nối vào #mediaItemsWrapper, không render lại
    cả wrapper này.
--}}
<div class="media-items {{ $mode === 'list' ? 'media-items--list' : 'media-items--grid' }}" id="mediaItemsWrapper" data-mode="{{ $mode }}">
    @if($mode === 'list' && $medias->isNotEmpty())
        <div class="media-item media-item--head">
            <span class="media-item__select"></span>
            <span class="media-item__preview"></span>
            <div class="media-item__info">{{ __('media::media.filter.name') }}</div>
            <div class="media-item__col media-item__col--type">{{ __('media::media.list.type') }}</div>
            <div class="media-item__col media-item__col--size">{{ __('media::media.list.size') }}</div>
            <div class="media-item__col media-item__col--date">{{ __('media::media.list.date') }}</div>
            <div class="media-item__col media-item__col--author">{{ __('media::media.list.author') }}</div>
            <div class="media-item__col media-item__col--usage">{{ __('media::media.list.usage') }}</div>
        </div>
    @endif
    @include('media::admin.partials.media-items-only', ['medias' => $medias, 'mode' => $mode])
</div>
