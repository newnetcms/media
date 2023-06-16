<script>
    function checkMediaItem(baseId, itemClass) {
        var baseCheck = $('#' + baseId).is(":checked");
        $('.' + itemClass).each(function() {
            if (!$(this).is(':disabled')) {
                $(this).prop('checked', baseCheck);
            }
        });
    }
    function deleteCheckedMediaItem() {
        let arrayMediaIds = [];
        $('input:checkbox.itemImage').each(function () {
            var sThisVal = (this.checked ? $(this).val() : "");
            if (sThisVal) {
                arrayMediaIds.push(sThisVal);
            }
        });
        if (arrayMediaIds.length > 0) {
            $.ajax({
                url: adminPath + '/media/delete',
                method: 'DELETE',
                data: {
                    ids: arrayMediaIds
                },
                success: function (response) {
                    location.reload();
                },
                error: function (e) {
                    console.log(e)
                }
            });
        } else {
            alert('Please choose at least a item.')
        }
    }
</script>
<table class="table table-hover">
    <thead>
    <tr>
        <th>STT</th>
        <th>
            <input type="checkbox" id="group" name="group2"
                   onclick="checkMediaItem('group', 'itemImage');" />
        </th>
        <th>Thumbnail</th>
        <th>Name</th>
        <th>
            <a href="#" data-toggle="modal" data-target="#deleteImage" style="height: 32px;" class="btn btn-danger">
                Delete
            </a>
        </th>
    </tr>
    </thead>
    <tbody>
    @foreach($medias as $media)
        <tr>
            <td>{{$media->id}}</td>
            <td><input type="checkbox" class="itemImage" value="{{$media->id}}"></td>
            <td>
                <a href="#" class="icon-menu-item col-4 editImage" data-toggle="modal" data-target="#edit" data-id="{{$media->id}}">
                    @php
                        $type = explode('/', $media->mime_type)
                    @endphp
                    @if ($type[0] == 'image')
                        <img width="70px" height="70px" style="margin: 10px;" src="{{ $media->thumb }}">
                    @elseif ($type[0] == 'audio')
                        <img width="70px" height="70px" style="margin: 10px;" src="{{ asset('vendor/media/images/types/mp3.png') }}">
                    @elseif ($type[0] == 'video')
                        <img width="70px" height="70px" style="margin: 10px;" src="{{ asset('vendor/media/images/types/video.png')}}">
                    @else
                        <img width="70px" height="70px" style="margin: 10px;" src="{{ asset('vendor/media/images/types/text.png') }}">
                    @endif
                </a>
            </td>
            <td>
                <a href="{{$media->getUrl()}}" target="_blank">
                    {{$media->file_name}}
                </a>
            </td>
            <td>
            </td>
        </tr>
    @endforeach
    </tbody>
</table>
<div class="modal fade" id="deleteImage" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Are you sure delete the items?</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body" style="margin-left: 183px;">
                <a href="#" class="btn btn-success deleteImageListView" id="deleteImageListView" onclick="deleteCheckedMediaItem()">Yes</a>
                <button class="btn btn-secondary" type="button" data-dismiss="modal">No</button>
                <div>
                </div>
            </div>
        </div>
    </div>
</div>
