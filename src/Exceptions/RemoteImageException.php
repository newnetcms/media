<?php

namespace Newnet\Media\Exceptions;

/**
 * Lỗi khi tải ảnh từ URL bên ngoài (RemoteImageImporter) — message đã là câu
 * thông báo hiển thị được cho người dùng, không chứa chi tiết nội bộ (IP phân
 * giải, lỗi mạng...).
 */
class RemoteImageException extends \RuntimeException
{
}
