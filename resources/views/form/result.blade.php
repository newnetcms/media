
    @foreach($medias as $media)
        <div class="col-md-2 editImageSelected" data-id="{{ $media->id }}" data-src="{{ $media->thumb }}">
            <div class="card">
                <a href="#" class="icon-menu-item col-4 editImage" data-toggle="modal" data-target="#edit" data-id="{{$media->id}}">
                    @php
                        $type = explode('/', $media->mime_type)
                    @endphp
                    @if ($type[0] == 'image')
                        <img width="130px" height="130px" style="padding: 15px;" src="{{ $media->thumb }}">
                    @elseif ($type[0] == 'audio')
                        <img width="130px" height="130px" style="padding: 15px;" src="{{ asset('vendor/media/images/types/mp3.png') }}">
                    @elseif ($type[0] == 'video')
                        <img width="130px" height="130px" style="padding: 15px;" src="{{ asset('vendor/media/images/types/video.png') }}">
                    @else
                        <img width="130px" height="130px" style="padding: 15px;" src="{{ asset('vendor/media/images/types/text.png')}}">
                    @endif
{{--                    <a style="text-align: center !important;" href="{{ $media->getUrl() }}" target="_blank">{{$media->name}}</a>--}}
                </a>

            </div>
        </div>
    @endforeach
