function MediaCustom() {

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $('#addFile').click(function (e) {
        e.preventDefault();
        $('.uploadFile').toggle();
    });

    $('body').on('change', '#image-upload',function (e) {
        $('#upload-form').submit();
    });

    $('body').on('click', '.editImage', function (e) {
        e.preventDefault();
        $('.editImageSelected').removeClass('selectedImage');
        $(this).closest('.editImageSelected').addClass('selectedImage');
        let id = $(this).data('id');
        $.ajax({
            url: adminPath + '/media/' + id + '/edit',
            method: 'GET',
            success: function (response) {
                $('.imageSelected').attr('src', response.src);
                $('#nameImage').text(response.file.file_name);
                $('#dateUpload').text(response.file.created_at);
                $('#sizeImage').text(response.file.size + ' ');
                $('#modelAttached').text(typeof response.modelAttached[0] != 'undefined' ? response.modelAttached[0].mediable_type : 'No model attached!');
                $('#btnDeleteImage').attr('data-id', response.file.id);
            },
            error: function (e) {
                console.log(e)
            }
        });
    });

    $('body').on('click', '.deleteImageSelect', function (e) {
        let id = $(this).data('id');
        $('.deletedImage').val(id);
    });

    $('body').on('click', '.deletedImage', function (e) {
        $.ajax({
            url: adminPath + '/media/delete',
            type: 'POST',
            data: {
                _method: 'DELETE',
                ids: [$('#btnDeleteImage').data('id')]
            },
            success: function (response) {
                location.reload();
            },
            error: function (e) {
                console.log(e)
            }
        });
    });

    $('body').on('keyup', '#search', function (e) {
        let value = $(this).val();
        let field = $(this).data('type');
        if (value.length > 1) {
            $.ajax({
                url: adminPath + '/media/search',
                data: {
                    mode: $('#mode').val(),
                    field: field,
                    value: value
                },
                success: function (response) {
                    handleResponse(response);
                },
                error: function (e) {
                    console.log(e)
                }
            });
        }
        if (value.length == 0) {
            $.ajax({
                url: adminPath + '/media',
                data: {
                    mode: $('#mode').val()
                },
                success: function (response) {
                    handleResponse(response);
                },
                error: function (e) {
                    console.log(e)
                }
            });
        }
    });

    $('body').on('change', '#search_by_day, #search_by_attach_model, #search_by_type', function (e) {
        let value = $(this).val();
        let field = $(this).data('type');
        let url = adminPath + '/media/search';
        if (value == 'all') {
            url = adminPath + '/media'
        }
        $.ajax({
            url: url,
            data: {
                mode: $('#mode').val(),
                value: value,
                field: field
            },
            success: function (response) {
                handleResponse(response);
            },
            error: function (e) {
                console.log(e)
            }
        });
    });

    $('body').on('change', '#sort_by', function (e) {
        let value = $(this).val();
        if (value != 0) {
            $.ajax({
                url: adminPath + '/media/sort',
                data: {
                    mode: $('#mode').val(),
                    value: value
                },
                success: function (response) {
                    handleResponse(response);
                },
                error: function (e) {
                    console.log(e)
                }
            });
        }
    });

    var url = location.href;
    $('body').on('click', '#modeList', function (e) {
        if (url.indexOf('mode=list') > -1) {
            e.preventDefault();
        }
    });
    $('body').on('click', '#modeGrid', function (e) {
        if (url.indexOf('mode=grid') > -1) {
            e.preventDefault();
        }
    });

    if (url.indexOf('mode=list') > -1 ) {
        $('#modeGrid').removeClass('selectedView');
        $('#modeList').addClass('selectedView');
    } else {
        $('#modeGrid').addClass('selectedView');
        $('#modeList').removeClass('selectedView');
    }

    if (url.indexOf('media') > 0) {
        $('#media').addClass('mm-active');
    }

    function handleResponse(response) {
        if (response.status == 'C200') {
            $('.viewImage').html(response.result);
        } else {
            $('.viewImage').html('<h3>Not found data!</h3>')
        }
    }

}

$(document).ready(function() {
    new MediaCustom();
});
