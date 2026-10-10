<?php

return [
    'model_name' => 'Media',

    'index' => [
        'page_title'    => 'List Media',
        'page_subtitle' => 'List Media',
    ],

    'create' => [
        'page_title'    => 'Create Media',
        'page_subtitle' => 'Create Media',
    ],

    'edit' => [
        'page_title'    => 'Edit Media',
        'page_subtitle' => 'Edit Media',
    ],

    'filter' => [
        'name' => 'File name',
        'name_placeholder' => 'Search by file name...',
        'sort' => 'Sort by',
        'clear_all' => 'Clear all filters',
    ],

    'sort' => [
        'created_at_desc' => 'Upload date: newest first',
        'created_at_asc' => 'Upload date: oldest first',
        'size_desc' => 'File size: largest first',
        'size_asc' => 'File size: smallest first',
    ],

    'sidebar' => [
        'library' => 'Library',
        'all' => 'All',
        'image' => 'Images',
        'video' => 'Videos',
        'audio' => 'Audio',
        'document' => 'Documents',
        'unattached' => 'Unattached',
        'by_time' => 'By date',
        'month' => 'Month',
        'by_usage' => 'Used in',
        'storage' => 'Storage',
    ],

    'view' => [
        'grid' => 'Grid view',
        'list' => 'List view',
    ],

    // "File manager" picker modal (form.media) — reused by many other forms
    // (@mediamanager/@gallery), kept separate from the groups above since the
    // media list page (admin/index.blade.php) doesn't use these keys.
    'picker' => [
        'title' => 'File manager',
        'image_required' => 'Please choose an image file',
        'media_required' => 'Please choose a video or audio file',
    ],

    'list' => [
        'type' => 'Type',
        'size' => 'Size',
        'date' => 'Uploaded',
        'author' => 'Uploaded by',
        'usage' => 'Used in',
    ],

    'empty' => 'No media found.',
    'loading_more' => 'Loading more...',

    'stats' => [
        'showing' => 'Showing :loaded / :total files',
        'total_size' => 'Total size',
    ],

    'upload' => [
        'title' => 'Add file',
        'drop_hint' => 'Drop files here to upload',
        'error' => 'Upload failed',
        'unsupported_type' => 'File type not allowed',
        'single_only' => 'Only one file can be uploaded here',
        'uploading' => 'Uploading :name',
        'uploading_many' => 'Uploading :count files',
    ],

    'bulk' => [
        'selected' => ':count item(s) selected',
        'delete' => 'Delete',
        'download' => 'Download (zip)',
        'copy_urls' => 'Copy URLs',
        'deselect' => 'Deselect',
    ],

    'detail' => [
        'title' => 'File details',
        'usage' => 'Used in',
        'no_usage' => 'Not attached to any content.',
        'file_name' => 'Original file name',
        'name' => 'Display name',
        'alt' => 'Alt text',
        'caption' => 'Caption',
        'save' => 'Save',
        'delete' => 'Delete',
        'download' => 'Download',
        'copy_url' => 'Copy URL',
        'open_original' => 'Open original',
        'uploaded_by' => 'Uploaded by',
        'uploaded_at' => 'Uploaded at',
        'size' => 'File size',
        'dimensions' => 'Dimensions',
    ],

    'confirm' => [
        'delete_title' => 'Delete selected media?',
        'delete_text' => 'Deleted files cannot be recovered.',
        'yes' => 'Delete',
        'no' => 'Cancel',
    ],

    'notification' => [
        'created' => 'Media successfully created!',
        'updated' => 'Media successfully updated!',
        'deleted' => 'Media successfully deleted!',
        'copy_url_success' => 'URL copied to clipboard.',
        'select_at_least_one' => 'Please select at least one item.',
    ],
];
