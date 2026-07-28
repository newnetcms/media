@php
    $ext = strtoupper($media->extension ?? '');
    $iconMap = [
        'PDF' => 'fa-file-pdf',
        'DOC' => 'fa-file-word', 'DOCX' => 'fa-file-word',
        'XLS' => 'fa-file-excel', 'XLSX' => 'fa-file-excel', 'CSV' => 'fa-file-excel',
        'PPT' => 'fa-file-powerpoint', 'PPTX' => 'fa-file-powerpoint',
        'ZIP' => 'fa-file-archive', 'RAR' => 'fa-file-archive', '7Z' => 'fa-file-archive', 'TAR' => 'fa-file-archive', 'GZ' => 'fa-file-archive',
        'MP3' => 'fa-file-audio', 'WAV' => 'fa-file-audio', 'OGG' => 'fa-file-audio',
        'MP4' => 'fa-file-video', 'MOV' => 'fa-file-video', 'AVI' => 'fa-file-video', 'WMV' => 'fa-file-video', 'MKV' => 'fa-file-video',
        'TXT' => 'fa-file-alt',
    ];
    $icon = $iconMap[$ext] ?? 'fa-file';
@endphp
<div class="media-file-type-preview">
    <i class="fas {{ $icon }}"></i>
    <span class="media-file-ext">{{ $ext }}</span>
</div>
