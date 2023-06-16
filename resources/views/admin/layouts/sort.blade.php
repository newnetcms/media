<div class="col-md-2">
    <div class="form-group">
        <select class="form-control" name="sort_by" id="sort_by">
            <option value="0">Please choose sort</option>
            @foreach(config('cms.media.sort_by') as $key => $value)
                <option value="{{$key}}">{{ $value }}</option>
            @endforeach
        </select>
    </div>
</div>
