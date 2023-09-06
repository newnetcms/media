@extends('core::admin.master')

@assetadd('my-script', 'vendor/media/js/admin/custom.js', ['jquery'])

@assetadd('my-script', 'vendor/media/css/admin/custom.css')

@section('meta_title', __('media::media.index.page_title'))

@section('page_title', __('media::media.index.page_title'))

@section('page_subtitle', __('media::media.index.page_subtitle'))

@section('content')
    <div class="card mb-4">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="fs-17 font-weight-600 mb-0">
                        {{ __('media::media.index.page_title') }}
                        <a href="#" id="addFile" style="color: blue">Add file</a>
                    </h6>
                </div>
            </div>
        </div>
        <form id="upload-form" enctype="multipart/form-data" action="{{route('media.admin.media.store')}}" method="POST">
            @csrf
            <div class="card-body">

                @include('media::admin.layouts.upload-image')

                <div class="row">

                    @include('media::admin.layouts.view-system')

                    @include('media::admin.layouts.search-model')

                    @include('media::admin.layouts.search-type')

                    @include('media::admin.layouts.sort')

                    @include('media::admin.layouts.search-day')

                    @include('media::admin.layouts.search-name')

                </div>
                <input type="hidden" value="{{request()->mode}}" name="mode" id="mode">
                <div class="row viewImage">
                    @if (request()->mode == 'list')
                        @include('media::admin.layouts.listview')
                    @else
                        @include('media::admin.layouts.gridview')
                    @endif
                </div>

                <br>
                {{ $medias->appends(Request::all())->render() }}
            </div>
        </form>
    </div>

    @include('media::admin.modals.edit-modal')

    @include('media::admin.modals.delete-modal')
@stop
