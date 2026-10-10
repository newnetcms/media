{{--
    Sidebar cho modal "File manager" (form.media) — dùng lại nguyên $sidebar từ
    MediaController::buildSidebarData() (không tính lại số liệu), nhưng render
    link dạng data-filter/data-value thay vì href điều hướng như
    admin/partials/sidebar.blade.php (trang Danh sách media): trong modal này
    bấm sidebar chỉ nên cập nhật bộ lọc rồi gọi lại ajaxMedia() qua AJAX, không
    được rời khỏi trang form đang sửa. Không gắn {{$name}} vào class/id ở đây vì
    partial này render từ controller (không có $name) — việc khoanh vùng theo
    từng field/modal instance nằm ở JS (event delegation trên
    .js-picker-sidebar-{{$name}} tĩnh trong media.blade.php), nên chỉ cần class
    chung .js-picker-filter/.js-picker-year-toggle ở đây.
--}}
<div class="media-picker-sidebar__section">
    <div class="media-picker-sidebar__heading">{{ __('media::media.sidebar.library') }}</div>
    @foreach($sidebar['quickViews'] as $view)
        <a href="#" class="media-picker-sidebar__link js-picker-filter {{ $view['active'] ? 'is-active' : '' }}"
           data-filter="type" data-value="{{ $view['value'] }}">
            <span>{{ $view['label'] }}</span>
            <span class="media-picker-sidebar__count">{{ $view['count'] }}</span>
        </a>
    @endforeach
</div>

@if(count($sidebar['yearGroups']))
    <div class="media-picker-sidebar__section">
        <div class="media-picker-sidebar__heading">{{ __('media::media.sidebar.by_time') }}</div>

        <a href="#" class="media-picker-sidebar__link js-picker-filter {{ $sidebar['allMonthsLink']['active'] ? 'is-active' : '' }}"
           data-filter="month" data-value="">
            <span>{{ $sidebar['allMonthsLink']['label'] }}</span>
            <span class="media-picker-sidebar__count">{{ $sidebar['allMonthsLink']['count'] }}</span>
        </a>

        @foreach($sidebar['yearGroups'] as $group)
            <div class="media-picker-sidebar__year">
                <button type="button" class="media-picker-sidebar__year-toggle js-picker-year-toggle">
                    <i class="fas fa-chevron-{{ $group['expanded'] ? 'down' : 'right' }}"></i>
                    {{ $group['year'] }}
                </button>
                <div class="media-picker-sidebar__months" style="{{ $group['expanded'] ? '' : 'display:none;' }}">
                    @foreach($group['months'] as $month)
                        <a href="#" class="media-picker-sidebar__link media-picker-sidebar__link--sub js-picker-filter {{ $month['active'] ? 'is-active' : '' }}"
                           data-filter="month" data-value="{{ $month['value'] }}">
                            <span>{{ $month['label'] }}</span>
                            <span class="media-picker-sidebar__count">{{ $month['count'] }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
@endif

<div class="media-picker-sidebar__section">
    <div class="media-picker-sidebar__heading">{{ __('media::media.sidebar.by_usage') }}</div>

    <a href="#" class="media-picker-sidebar__link js-picker-filter {{ $sidebar['allModelsLink']['active'] ? 'is-active' : '' }}"
       data-filter="model" data-value="{{ $sidebar['allModelsLink']['value'] }}">
        <span>{{ $sidebar['allModelsLink']['label'] }}</span>
        <span class="media-picker-sidebar__count">{{ $sidebar['allModelsLink']['count'] }}</span>
    </a>

    {{-- Toggle riêng, kết hợp được với type/month nhưng loại trừ với chọn model cụ thể --}}
    <a href="#" class="media-picker-sidebar__link media-picker-sidebar__toggle js-picker-filter {{ $sidebar['unattachedToggle']['active'] ? 'is-active' : '' }}"
       data-filter="unattached" data-value="{{ $sidebar['unattachedToggle']['value'] }}">
        <span>{{ $sidebar['unattachedToggle']['label'] }}</span>
        <span class="media-picker-sidebar__count">{{ $sidebar['unattachedToggle']['count'] }}</span>
    </a>

    @foreach($sidebar['modelList'] as $model)
        <a href="#" class="media-picker-sidebar__link js-picker-filter {{ $model['active'] ? 'is-active' : '' }}"
           data-filter="model" data-value="{{ $model['value'] }}">
            <span>{{ $model['label'] }}</span>
            <span class="media-picker-sidebar__count">{{ $model['count'] }}</span>
        </a>
    @endforeach
</div>
