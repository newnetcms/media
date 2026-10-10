{{--
    Panel chi tiết file: slide-in từ bên phải (thay cho modal cũ) để xem/lướt
    qua nhiều file liên tiếp mà không phải đóng/mở từng popup.
--}}
<div class="media-drawer-backdrop" id="mediaDetailBackdrop"></div>
<div class="media-drawer" id="mediaDetailDrawer">
    <div class="media-drawer__header">
        <div class="media-drawer__title">{{ __('media::media.detail.title') }}</div>
        <button type="button" class="media-drawer__close" id="mediaDetailClose">
            <i class="fas fa-times"></i>
        </button>
    </div>

    <div class="media-drawer__body">
        <div id="mediaDetailPreview" class="media-detail-preview"></div>
        <div class="media-detail-dimensions" id="mediaDetailDimensions"></div>

        <dl class="media-detail-meta">
            <div class="media-detail-meta--full">
                <dt>{{ __('media::media.detail.file_name') }}</dt>
                <dd id="mediaDetailFileName"></dd>
            </div>
            <div>
                <dt>{{ __('media::media.detail.size') }}</dt>
                <dd id="mediaDetailSize"></dd>
            </div>
            <div>
                <dt>{{ __('media::media.detail.uploaded_by') }}</dt>
                <dd id="mediaDetailAuthor"></dd>
            </div>
            <div>
                <dt>{{ __('media::media.detail.uploaded_at') }}</dt>
                <dd id="mediaDetailDate"></dd>
            </div>
        </dl>

        <form id="mediaDetailForm">
            <div class="media-field">
                <label>{{ __('media::media.detail.name') }}</label>
                <input type="text" id="mediaDetailName" name="name">
            </div>
            <div class="media-field">
                <label>{{ __('media::media.detail.alt') }}</label>
                <input type="text" id="mediaDetailAlt" name="alt">
            </div>
            <div class="media-field">
                <label>{{ __('media::media.detail.caption') }}</label>
                <textarea id="mediaDetailCaption" name="caption" rows="2"></textarea>
            </div>
            <button type="submit" class="media-btn media-btn--primary">
                {{ __('media::media.detail.save') }}
            </button>
            <span class="media-detail-saved d-none" id="mediaDetailSaved"><i class="fas fa-check"></i></span>
        </form>

        <div class="media-detail-usage">
            <div class="media-detail-usage__heading">{{ __('media::media.detail.usage') }}</div>
            <ul id="mediaDetailUsage"></ul>
        </div>
    </div>

    <div class="media-drawer__footer">
        <button type="button" class="media-btn" id="mediaDetailCopyUrl">
            <i class="fas fa-link"></i> {{ __('media::media.detail.copy_url') }}
        </button>
        <a href="#" target="_blank" class="media-btn" id="mediaDetailDownload">
            <i class="fas fa-download"></i> {{ __('media::media.detail.download') }}
        </a>
        @admincan('media.admin.media.bulk_destroy')
            <button type="button" class="media-btn media-btn--danger" id="mediaDetailDelete">
                <i class="far fa-trash-alt"></i> {{ __('media::media.detail.delete') }}
            </button>
        @endadmincan
    </div>
</div>
