@extends('core::admin.master')

@assetadd('media-admin-css', asset('vendor/media/css/admin/media-manager.css'))
@assetadd('media-admin-js', asset('vendor/media/js/admin/media-manager.js'), ['jquery'])

@section('meta_title', __('media::media.index.page_title'))

@section('page_title', __('media::media.index.page_title'))

@section('page_subtitle', __('media::media.index.page_subtitle'))

{{-- Bỏ content-header (breadcrumb + page-title) để trang gọn hơn, có thêm chỗ
     cho sidebar/grid mà không cần cuộn cả trang. --}}
@section('content-header')
@endsection

@section('content')
    <script>
        window.mediaManagerConfig = {
            indexUrl: @json(route('media.admin.media.index')),
            uploadUrl: @json(route('media.admin.media.storeAjax')),
            bulkDestroyUrl: @json(route('media.admin.media.bulk_destroy')),
            bulkDownloadUrl: @json(route('media.admin.media.bulk_download')),
            resourceUrlBase: @json(url(config('core.admin_prefix') . '/media')),
            acceptExtensions: @json(config('cms.media.accept_upload_extension')),
            mode: @json($filters['mode'] ?? 'grid'),
            pagination: @json($pagination),
            lang: {
                selected: @json(__('media::media.bulk.selected', ['count' => ':count'])),
                selectAtLeastOne: @json(__('media::media.notification.select_at_least_one')),
                copyUrlSuccess: @json(__('media::media.notification.copy_url_success')),
                updateSuccess: @json(__('media::media.notification.updated')),
                uploadError: @json(__('media::media.upload.error')),
                noUsage: @json(__('media::media.detail.no_usage')),
                deleteTitle: @json(__('media::media.confirm.delete_title')),
                deleteText: @json(__('media::media.confirm.delete_text')),
                deleteYes: @json(__('media::media.confirm.yes')),
                deleteNo: @json(__('media::media.confirm.no')),
                loadingMore: @json(__('media::media.loading_more')),
                statsShowing: @json(__('media::media.stats.showing', ['loaded' => ':loaded', 'total' => ':total'])),
            },
        };
    </script>

    <div class="media-app" id="mediaApp">
        @admincan('media.admin.media.create')
            <div class="media-drop-overlay" id="mediaDropOverlay">
                <div class="media-drop-overlay__inner">
                    <i class="fas fa-cloud-upload-alt"></i>
                    <div>{{ __('media::media.upload.drop_hint') }}</div>
                    <div class="media-drop-overlay__hint">{{ config('cms.media.messageMax') }}</div>
                </div>
            </div>
        @endadmincan

        @include('media::admin.partials.sidebar', ['sidebar' => $sidebar])

        <main class="media-main">
            @include('media::admin.partials.toolbar', [
                'filters' => $filters,
                'sortOptions' => $sortOptions,
                'activeChips' => $activeChips,
            ])

            <div id="mediaUploadList" class="media-upload-list"></div>

            <div id="mediaListContainer">
                @include('media::admin.partials.media-grid', ['medias' => $medias, 'mode' => $filters['mode'] ?? 'grid'])
            </div>

            {!! $statsView !!}
        </main>
    </div>

    @include('media::admin.partials.detail-drawer')
@stop
