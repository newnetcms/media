<div class="modal fade" id="edit" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content" style="width: 860px; right: 140px;">
            <div class="modal-header">
                <h5 class="modal-title">Edit image <span class="title"></span></h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <img class="imageSelected img-thumbnail">
                    </div>
                    <div class="col-lg-6">
                        <form role="form" action="{{ route('media.admin.media.index') }}" method="post">
                            @csrf
                            <div>
                                <p>File name: <span id="nameImage"></span></p>
                                <p>Date uploaded: <span id="dateUpload"></span></p>
                                <p>File size: <span id="sizeImage"></span>KB</p>
                                <p>Model attached: <span id="modelAttached"></span></p>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" id="btnDeleteImage" data-toggle="modal" data-target="#delete" class="btn btn-danger">Delete</button>
                <button class="btn btn-secondary" type="button" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
