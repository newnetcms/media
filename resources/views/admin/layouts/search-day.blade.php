<div class="col-md-2">
    <div class="form-group">
        <select class="form-control" name="search_by_day" id="search_by_day" data-type="created_at">
            <option value="all">All days</option>
            @foreach($allMonths as $moth)
            <option value="{{$moth}}">{{$moth}}</option>
            @endforeach
        </select>
    </div>
</div>
