<div class="col-md-2">
    <div class="form-group">
        <select class="form-control" name="search_by_attach_model" id="search_by_attach_model">
            <option value="all">All models</option>
            @foreach($mediables as $m)
                <option value="{{$m}}">{{$m}}</option>
            @endforeach
        </select>
    </div>
</div>
