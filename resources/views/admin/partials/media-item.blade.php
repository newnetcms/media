@php
    $isImage = $media->isDisplayableImage();
    $isList = ($mode ?? 'grid') === 'list';
    $firstUsage = $media->relationLoaded('mediables') ? $media->mediables->first() : null;
@endphp
<div class="media-item" data-id="{{ $media->id }}" data-url="{{ $media->getUrl() }}" data-name="{{ $media->name }}">
    <label class="media-item__select">
        <input type="checkbox" class="media-item-checkbox" value="{{ $media->id }}">
    </label>

    <a href="#" class="media-item__preview" data-action="open-detail">
        @if($isImage)
            <img src="{{ Img::url($media->getUrl(), 300, 300) }}" alt="{{ $media->alt ?: $media->name }}" loading="lazy">
        @else
            @include('media::form.partials.file-type-icon', ['media' => $media])
        @endif
    </a>

    <div class="media-item__info">
        {{-- Hover xem tên file gốc trên đĩa (khác tên hiển thị có thể sửa); bấm
             vào tên cũng mở được Chi tiết file, không chỉ bấm vào ảnh. --}}
        <div class="media-item__name" title="{{ $media->file_name }}" data-action="open-detail">{{ $media->name }}</div>

        @if($isList)
            <div class="media-item__filename text-muted">{{ $media->file_name }}</div>
        @endif

        @unless($isList)
            <div class="media-item__meta text-muted">
                <span>{{ $media->human_size }}</span>
                <span>&middot; {{ $media->created_at?->format('d/m/Y') }}</span>
            </div>
        @endunless
    </div>

    @if($isList)
        <div class="media-item__col media-item__col--type">{{ strtoupper($media->extension) }}</div>
        <div class="media-item__col media-item__col--size">{{ $media->human_size }}</div>
        <div class="media-item__col media-item__col--date">{{ $media->created_at?->format('d/m/Y') }}</div>
        <div class="media-item__col media-item__col--author">{{ optional($media->author)->name ?: '—' }}</div>
        <div class="media-item__col media-item__col--usage">
            @if($firstUsage)
                <span class="media-item__usage-badge">{{ class_basename($firstUsage->mediable_type) }}</span>
            @else
                <span class="text-muted">{{ __('media::media.sidebar.unattached') }}</span>
            @endif
        </div>
    @endif
</div>
