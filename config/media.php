<?php
return [
    /*
     * The disk where files should be uploaded.
     */
    'disk'  => env('MEDIA_DISK', env('FILESYSTEM_DRIVER', 'public')),

    /*
     * The queue used to perform image conversions.
     * Leave empty to use the default queue driver.
     */
    'queue' => env('MEDIA_QUEUE'),

    /*
     * The fully qualified class name of the media model.
     */
    'model' => Newnet\Media\Models\Media::class,

    /*
     * The Guard for author of the media file.
     */
    'guard' => 'admin',

    /*
     * Default thumbsize conversion
     * Leave null if not create thumb conversion
     * thumbsize = [width, height]
     * thumbsize = null
     */
    'thumbsize' => [300, 300],

    'validatorImage' => 'max:100000',
    'messageMax' => 'Max upload 100MB',
    'itemOnPage' => 24,
    'type_search' => [
        'all' => 'All type',
        'video' => 'Video',
        'image' => 'Image',
        'audio' => 'Audio',
        'text' => 'File',
        'application' => 'Other'
    ],
    'sort_by' => [
        'created_at-desc' => 'Date upload decrease',
        'created_at-asc' => 'Date upload increase',
        'size-desc' => 'Size less to large',
        'size-asc' => 'Size large to less'
    ],
    'imageproxy' => [
        'enable' => false,
        'server' => env('MEDIA_IMAGEPROXY_SERVER', 'https://img.cdn2n.net'),
        'quality' => 80,
    ]
];
