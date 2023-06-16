<div>
    <input type="text" name="media_titles[]" hidden>
    <input type="text" name="media_labels[]" hidden>
    <input type="text" name="media_ids[]" hidden>
    <div
        class="modal fade"
        id="media-modal-attribute"
        tabindex="-1"
        role="dialog"
        aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Image Attribute</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <input id="media-module-label" type="text" placeholder="label" class="form-control">
                    </div>
                    <div class="form-group">
                        <input id="media-module-title" type="text" placeholder="title" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button id="btn-save-media-atribute" type="button" class="btn btn-primary">Save</button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        $(document).ready(function (){
            let listImg = @json(isset($item->media) ? $item->media : null);
            let idsClick = null
            let targerDom  = null

            $(document).on('click','img',function (e){
                $('#media-module-label').val('')
                $('#media-module-title').val('')
                let getChill = $(e.target).next()[0]
                targerDom = $(e.target)
                idsClick = $(getChill).val()
                $('#media-modal-attribute').modal('show');

                let findParent = $(targerDom).parent()
                if ($(findParent).find(`input[data-attr-img=title]`).length > 0){
                    $('#media-module-title').val($(findParent).find(`input[data-attr-img=title]`)[0].value)
                }

                if ($(findParent).find(`input[data-attr-img=label]`).length > 0){
                    $('#media-module-label').val($(findParent).find(`input[data-attr-img=label]`)[0].value)
                }

                listImg.forEach(function (value, key){
                    console.log (value.id)
                    let findParent = $(targerDom).parent()
                    console.log($(findParent).find(`input[value=${value.id}]`))
                    $('#media-module-title').val()
                    $('#media-module-label').val()
                    if ($(findParent).find(`input[value=${value.id}]`).length > 0){
                        $('#media-module-title').val(value.media_tags.title)
                        $('#media-module-label').val(value.media_tags.label)
                    }

                })

            })
            $('#btn-save-media-atribute').on('click',function (){
                let valueTitle = `  <input data-attr-img="title" type="text" name="media_titles[]" value=" ${$('#media-module-title').val()}" hidden>`
                let valueLabel =  `<input data-attr-img="label" type="text" name="media_labels[]" value="${$('#media-module-label').val()}" hidden>`
                let valueIdImg = `<input data-attr-img="ids" type="text" name="media_ids[]" value="${idsClick}" hidden>`

                let findParent = $(targerDom).parent()
                if ($(findParent).find(`input[data-attr-img=title]`).length > 0){
                    $(findParent).find(`input[data-attr-img=title]`).remove()
                    $(targerDom).after(valueTitle)
                }else{
                    $(targerDom).after(valueTitle)
                }

                if ($(findParent).find(`input[data-attr-img=label]`).length > 0){
                    $(findParent).find(`input[data-attr-img=label]`).remove()
                    $(targerDom).after(valueLabel)
                }else{
                    $(targerDom).after(valueLabel)
                }

                if ($(findParent).find(`input[data-attr-img=ids]`).length > 0){
                    $(findParent).find(`input[data-attr-img=ids]`).remove()
                    $(targerDom).after(valueIdImg)
                }else{
                    $(targerDom).after(valueIdImg)
                }
                $('#media-module-title').val('')
                $('#media-module-label').val('')
                $('#media-modal-attribute').modal('hide');
            })
        })
    </script>
@endpush
