
@if ($mode == 'list')
    <table class="table table-hover">
        <thead>
        <tr>
            <th>STT</th>
            <th><input type="checkbox"></th>
            <th>Thumbnail</th>
            <th>Name</th>
            <th>Action</th>
        </tr>
        </thead>
        <tbody>
        @foreach($medias as $media)
            <tr>
                <td>{{$media->id}}</td>
                <td><input type="checkbox"></td>
                <td>
                    <a href="#" class="icon-menu-item col-4 editImage" data-toggle="modal" data-target="#edit" data-id="{{$media->id}}">
                        @php
                            $type = explode('/', $media->mime_type)
                        @endphp
                        @if ($type[0] == 'image')
                            <img width="50px" height="50px" style="margin: 10px;" src="{{ $media->thumb }}">
                        @elseif ($type[0] == 'text')
                            <img width="50px" height="50px" style="margin: 10px;" src="{{ Storage::url('system/text.png')}}">
                        @elseif ($type[0] == 'audio')
                            <img width="50px" height="50px" style="margin: 10px;" src="{{ Storage::url('system/mp3.png') }}">
                        @elseif ($type[0] == 'video')
                            <img width="50px" height="50px" style="margin: 10px;" src="{{ Storage::url('system/video.png') }}">
                        @else
                            <img width="50px" height="50px" style="margin: 10px;" src="{{ Storage::url('system/text.png')}}">
                        @endif
                    </a>
                </td>
                <td>
                    <a href="{{$media->getUrl()}}" target="_blank">
                        {{$media->file_name}}
                    </a>
                </td>
                <td>
                    <a href="#" data-toggle="modal" data-target="#delete" data-id="{{$media->id}}" class="icon-menu-item col-4 deleteImageSelect">
                        <i style="font-size: 20px; color: red" class="typcn typcn-delete-outline d-block"></i>
                    </a>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
@else
    @foreach($medias as $media)
        <div class="col-md-2 editImageSelected">
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
                    <a style="text-align: center !important;" href="{{ $media->getUrl() }}" target="_blank">{{$media->name}}</a>
                </a>

            </div>
        </div>
    @endforeach
@endif
