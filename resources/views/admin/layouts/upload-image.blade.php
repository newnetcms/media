<div class="row uploadFile" style="margin-bottom: 15px; display: none; padding: 30px; border: 1px dotted black">
    <div class="col-md-4"></div>
    <div class="col-md-4">
        <div>
            <a onclick="document.getElementById('image-upload').click()" class="btn btn-outline-secondary"> Upload Image</a>
            <p>{{config('cms.media.messageMax')}}</p>
        </div>
    </div>
    <div class="col-md-4">
        <input type="file" multiple id="image-upload" name="image-upload[]" style="display: none">
    </div>
</div>
