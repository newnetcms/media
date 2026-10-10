
    @foreach($medias as $media)
        @php
            // filter() (xem MediaController::ajaxMedia) đã eager-load 'author' và
            // 'mediables.mediable' nên không tốn thêm query N+1 ở đây.
            $firstUsage = $media->relationLoaded('mediables') ? $media->mediables->first() : null;
        @endphp
        <div class="editImageSelected"
             data-id="{{ $media->id }}"
             data-src="{{ $media->isOfType('image') ? Img::url($media->getUrl(), 300, 300) : '' }}"
             data-type="{{ $media->isOfType('image') ? 'image' : 'other' }}"
             data-ext="{{ strtoupper($media->extension) }}"
             data-url="{{ $media->getUrl() }}"
             data-name="{{ $media->name }}"
             data-kind="{{ $media->insertableKind() }}">
            <div class="card">
                <a href="#" class="icon-menu-item editImage" data-toggle="modal" data-target="#edit" data-id="{{$media->id}}">
                    <span class="media-picker-item__preview">
                        @if ($media->isOfType('image'))
                            <img src="{{ Img::url($media->getUrl(), 300, 300) }}" alt="{{ $media->name }}">
                        @else
                            @include('media::form.partials.file-type-icon', ['media' => $media])
                        @endif
                        <span class="media-picker-item__check"><i class="fas fa-check"></i></span>
                    </span>
                    <span class="media-picker-item__info">
                        <span class="media-picker-item__name" title="{{ $media->file_name }}">{{ $media->name }}</span>
                        <span class="media-picker-item__filename">{{ $media->file_name }}</span>
                        <span class="media-picker-item__meta">
                            <span>{{ $media->human_size }}</span>
                            <span>&middot; {{ $media->created_at?->format('d/m/Y') }}</span>
                        </span>
                    </span>
                    <span class="media-picker-item__col media-picker-item__col--type">{{ strtoupper($media->extension) }}</span>
                    <span class="media-picker-item__col media-picker-item__col--size">{{ $media->human_size }}</span>
                    <span class="media-picker-item__col media-picker-item__col--date">{{ $media->created_at?->format('d/m/Y') }}</span>
                    <span class="media-picker-item__col media-picker-item__col--author">{{ optional($media->author)->name ?: '—' }}</span>
                    <span class="media-picker-item__col media-picker-item__col--usage">
                        @if($firstUsage)
                            <span class="media-picker-item__usage-badge">{{ class_basename($firstUsage->mediable_type) }}</span>
                        @else
                            <span class="text-muted">{{ __('media::media.sidebar.unattached') }}</span>
                        @endif
                    </span>
{{--                    <a style="text-align: center !important;" href="{{ $media->getUrl() }}" target="_blank">{{$media->name}}</a>--}}
                </a>

            </div>
        </div>
    @endforeach
