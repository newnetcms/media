<div class="col-md-2">
    <div class="form-group">
        <select class="form-control" name="search_by_type" id="search_by_type" data-type="mime_type">
            @foreach(config('cms.media.type_search') as $key => $type)
                <option value="{{$key}}">{{ $type }}</option>
            @endforeach
        </select>
    </div>
</div>
