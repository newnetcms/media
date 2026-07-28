
    @foreach($medias as $media)
        <div class="col-md-2 editImageSelected"
             data-id="{{ $media->id }}"
             data-src="{{ $media->isOfType('image') ? Img::url($media->getUrl(), 300, 300) : '' }}"
             data-type="{{ $media->isOfType('image') ? 'image' : 'other' }}"
             data-ext="{{ strtoupper($media->extension) }}">
            <div class="card">
                <a href="#" class="icon-menu-item col-4 editImage" data-toggle="modal" data-target="#edit" data-id="{{$media->id}}">
                    @if ($media->isOfType('image'))
                        <img width="130px" height="130px" style="padding: 15px;" src="{{ Img::url($media->getUrl(), 300, 300) }}">
                    @else
                        @include('media::form.partials.file-type-icon', ['media' => $media])
                    @endif
{{--                    <a style="text-align: center !important;" href="{{ $media->getUrl() }}" target="_blank">{{$media->name}}</a>--}}
                </a>

            </div>
        </div>
    @endforeach
