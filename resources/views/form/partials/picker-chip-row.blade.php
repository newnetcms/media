{{--
    Chip row cho modal "File manager" (form.media) — dùng lại $activeChips từ
    MediaController::activeFilterChips() (không tính lại), nhưng mỗi chip bấm
    để BỎ đúng 1 chiều lọc qua data-clear-filter + JS, thay vì href điều hướng
    như toolbar.blade.php (trang Danh sách media). Không gắn {{$name}} ở đây vì
    partial này render từ controller — khoanh vùng theo field/modal instance
    nằm ở JS (xem .js-picker-chip-row-{{$name}} trong media.blade.php).
--}}
<div class="media-picker-chip-row {{ count($activeChips) ? '' : 'd-none' }}">
    @foreach($activeChips as $chip)
        <a href="#" class="media-picker-chip js-picker-clear-chip {{ !empty($chip['isClearAll']) ? 'media-picker-chip--clear-all' : '' }}"
           data-clear-filter="{{ $chip['clearFilter'] }}">
            <span>{{ $chip['label'] }}</span>
            <i class="fas fa-times"></i>
        </a>
    @endforeach
</div>
