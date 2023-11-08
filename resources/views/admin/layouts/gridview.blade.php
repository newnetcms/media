@foreach($medias as $media)
    <div class="col-md-2 editImageSelected">
        <div class="card h-100 shadow-sm border">
            <a href="#" class="icon-menu-item text-center editImage" data-toggle="modal" data-target="#edit" data-id="{{$media->id}}">
                @php
                    $type = explode('/', $media->mime_type)
                @endphp
                @if ($type[0] == 'image')
                    <img width="130px" height="130px" style="padding: 15px;" src="{{ Img::url($media->getUrl(), 300, 300) }}">
                @elseif ($type[0] == 'audio')
                    <img width="130px" height="130px" style="padding: 15px;" src="{{ asset('vendor/media/images/types/mp3.png') }}">
                @elseif ($type[0] == 'video')
                    <img width="130px" height="130px" style="padding: 15px;" src="{{ asset('vendor/media/images/types/video.png') }}">
                @else
                    <img width="130px" height="130px" style="padding: 15px;" src="{{ asset('vendor/media/images/types/text.png')}}">
                @endif
            </a>
            <a class="text-center p-1" href="{{ $media->getUrl() }}" target="_blank">
                {{$media->name}}
            </a>
        </div>
    </div>
@endforeach
