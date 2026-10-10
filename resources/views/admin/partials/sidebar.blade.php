{{--
    Sidebar điều hướng của trang Danh sách media: thư viện nhanh theo loại file,
    theo năm/tháng upload, theo nơi sử dụng (model gắn vào) và dung lượng đã dùng.
    Mỗi mục là 1 link thật (route index kèm query), nên vẫn hoạt động được khi
    JS lỗi/chưa load (full page reload) — media-manager.js chỉ chặn click để
    gọi AJAX và cập nhật cả #mediaListContainer + chính sidebar này.
--}}
<aside class="media-sidebar" id="mediaSidebar">

    <div class="media-sidebar__section">
        <div class="media-sidebar__heading">{{ __('media::media.sidebar.library') }}</div>
        @foreach($sidebar['quickViews'] as $view)
            <a href="{{ $view['url'] }}" class="media-sidebar__link media-filter-link {{ $view['active'] ? 'is-active' : '' }}">
                <span>{{ $view['label'] }}</span>
                <span class="media-sidebar__count">{{ $view['count'] }}</span>
            </a>
        @endforeach
    </div>

    @if(count($sidebar['yearGroups']))
        <div class="media-sidebar__section">
            <div class="media-sidebar__heading">{{ __('media::media.sidebar.by_time') }}</div>

            <a href="{{ $sidebar['allMonthsLink']['url'] }}" class="media-sidebar__link media-filter-link {{ $sidebar['allMonthsLink']['active'] ? 'is-active' : '' }}">
                <span>{{ $sidebar['allMonthsLink']['label'] }}</span>
                <span class="media-sidebar__count">{{ $sidebar['allMonthsLink']['count'] }}</span>
            </a>

            @foreach($sidebar['yearGroups'] as $group)
                <div class="media-sidebar__year">
                    <button type="button" class="media-sidebar__year-toggle" data-year-toggle>
                        <i class="fas fa-chevron-{{ $group['expanded'] ? 'down' : 'right' }}"></i>
                        {{ $group['year'] }}
                    </button>
                    <div class="media-sidebar__months" style="{{ $group['expanded'] ? '' : 'display:none;' }}">
                        @foreach($group['months'] as $month)
                            <a href="{{ $month['url'] }}" class="media-sidebar__link media-sidebar__link--sub media-filter-link {{ $month['active'] ? 'is-active' : '' }}">
                                <span>{{ $month['label'] }}</span>
                                <span class="media-sidebar__count">{{ $month['count'] }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <div class="media-sidebar__section">
        <div class="media-sidebar__heading">{{ __('media::media.sidebar.by_usage') }}</div>

        <a href="{{ $sidebar['allModelsLink']['url'] }}" class="media-sidebar__link media-filter-link {{ $sidebar['allModelsLink']['active'] ? 'is-active' : '' }}">
            <span>{{ $sidebar['allModelsLink']['label'] }}</span>
            <span class="media-sidebar__count">{{ $sidebar['allModelsLink']['count'] }}</span>
        </a>

        {{-- Toggle riêng, kết hợp được với type/month nhưng loại trừ với chọn model cụ thể --}}
        <a href="{{ $sidebar['unattachedToggle']['url'] }}" class="media-sidebar__link media-sidebar__toggle media-filter-link {{ $sidebar['unattachedToggle']['active'] ? 'is-active' : '' }}">
            <span>{{ $sidebar['unattachedToggle']['label'] }}</span>
            <span class="media-sidebar__count">{{ $sidebar['unattachedToggle']['count'] }}</span>
        </a>

        @foreach($sidebar['modelList'] as $model)
            <a href="{{ $model['url'] }}" class="media-sidebar__link media-filter-link {{ $model['active'] ? 'is-active' : '' }}">
                <span>{{ $model['label'] }}</span>
                <span class="media-sidebar__count">{{ $model['count'] }}</span>
            </a>
        @endforeach
    </div>

</aside>
