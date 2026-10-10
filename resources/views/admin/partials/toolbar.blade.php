{{--
    Toolbar có 2 trạng thái dùng chung 1 chỗ: mặc định (tìm kiếm/sắp xếp/view/
    upload) và khi đang chọn nhiều item thì thay bằng các nút bulk action —
    đổi NỘI DUNG trong cùng 1 dòng, không chèn thêm khối mới, để tránh đẩy
    danh sách bên dưới nhảy xuống khi chọn/bỏ chọn item.
--}}
<div class="media-toolbar">
    <form id="mediaFilterForm" method="GET" action="{{ route('media.admin.media.index') }}" class="media-toolbar__row">
        <div class="media-toolbar__search">
            <i class="fas fa-search"></i>
            <input type="text" name="name" value="{{ $filters['name'] ?? '' }}" placeholder="{{ __('media::media.filter.name_placeholder') }}">
        </div>

        <input type="hidden" name="sort" id="filterSort" value="{{ $filters['sort'] ?? 'id-desc' }}">

        {{-- Giữ nguyên chiều lọc sidebar đang bật khi submit tìm kiếm/sắp xếp --}}
        <input type="hidden" name="type" value="{{ $filters['type'] ?? 'all' }}">
        <input type="hidden" name="model" value="{{ $filters['model'] ?? 'all' }}">
        <input type="hidden" name="month" value="{{ $filters['month'] ?? '' }}">
        <input type="hidden" name="unattached" value="{{ $filters['unattached'] ?? '' ? 1 : '' }}">
        <input type="hidden" name="mode" id="filterMode" value="{{ $filters['mode'] ?? 'grid' }}">

        {{-- Nhóm bên phải: sort (icon) kế bên view-toggle grid/list, rồi nút upload --}}
        <div class="media-toolbar__right">
            <div class="dropdown media-sort-dropdown">
                <button type="button" class="media-icon-btn dropdown-toggle" id="mediaSortButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="{{ __('media::media.filter.sort') }}">
                    <i class="fas fa-sort-amount-down"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="mediaSortButton">
                    @foreach($sortOptions as $option)
                        <a href="#" class="dropdown-item media-sort-option {{ ($filters['sort'] ?? 'id-desc') === $option['value'] ? 'active' : '' }}" data-value="{{ $option['value'] }}">
                            <i class="fas {{ $option['icon'] }}"></i> {{ $option['label'] }}
                        </a>
                    @endforeach
                </div>
            </div>

            <div class="media-toolbar__view-toggle" role="group">
                <button type="button" class="{{ ($filters['mode'] ?? 'grid') === 'grid' ? 'is-active' : '' }}" data-mode="grid" title="{{ __('media::media.view.grid') }}">
                    <i class="fas fa-th"></i>
                </button>
                <button type="button" class="{{ ($filters['mode'] ?? 'grid') === 'list' ? 'is-active' : '' }}" data-mode="list" title="{{ __('media::media.view.list') }}">
                    <i class="fas fa-list"></i>
                </button>
            </div>
        </div>

        @admincan('media.admin.media.create')
            <button type="button" class="media-btn media-btn--primary" id="mediaUploadButton">
                <i class="fas fa-cloud-upload-alt"></i> {{ __('media::media.upload.title') }}
            </button>
        @endadmincan
    </form>

    <div class="media-toolbar__row media-toolbar__bulk d-none" id="mediaBulkToolbar">
        <span class="media-toolbar__bulk-count" id="mediaBulkCount"></span>
        <div class="media-toolbar__bulk-actions">
            @admincan('media.admin.media.bulk_destroy')
                <button type="button" class="media-btn media-btn--danger" id="mediaBulkDelete">
                    <i class="far fa-trash-alt"></i> {{ __('media::media.bulk.delete') }}
                </button>
            @endadmincan
            @admincan('media.admin.media.bulk_download')
                <button type="button" class="media-btn" id="mediaBulkDownload">
                    <i class="fas fa-file-archive"></i> {{ __('media::media.bulk.download') }}
                </button>
            @endadmincan
            <button type="button" class="media-btn" id="mediaBulkCopyUrls">
                <i class="fas fa-copy"></i> {{ __('media::media.bulk.copy_urls') }}
            </button>
        </div>
        <button type="button" class="media-btn media-btn--link" id="mediaBulkDeselect">
            {{ __('media::media.bulk.deselect') }}
        </button>
    </div>
</div>

@admincan('media.admin.media.create')
    <input type="file" id="mediaFileInput" multiple hidden>
@endadmincan

<div id="mediaChipRow" class="media-chip-row {{ count($activeChips) ? '' : 'd-none' }}">
    @foreach($activeChips as $chip)
        <a href="{{ $chip['clearUrl'] }}" class="media-chip media-filter-link {{ !empty($chip['isClearAll']) ? 'media-chip--clear-all' : '' }}">
            <span>{{ $chip['label'] }}</span>
            <i class="fas fa-times"></i>
        </a>
    @endforeach
</div>
