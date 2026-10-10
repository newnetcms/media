<?php

namespace Newnet\Media;

use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\Psr7\UriResolver;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Newnet\Media\Exceptions\RemoteImageException;
use Newnet\Media\Models\Media;
use Psr\Http\Message\ResponseInterface;
use Throwable;

/**
 * Tải ảnh từ URL bên ngoài vào thư viện media — dùng khi dán nội dung copy từ
 * website khác vào TinyMCE.
 *
 * Khác MediaUploader::uploadFromUrl() (dành cho crawler/seeder với URL tin cậy):
 * URL ở đây do người dùng đưa vào nên phải chặn SSRF — chỉ http/https cổng
 * 80/443, MỌI IP phân giải được phải là IP public, ghim đúng IP đã kiểm tra khi
 * kết nối (chống DNS rebinding), tự theo redirect và kiểm tra lại từng chặng —
 * đồng thời giới hạn thời gian/dung lượng và chỉ nhận nội dung ảnh. Kiểm tra
 * đuôi file/nội dung/làm sạch payload vẫn do MediaUploader::upload() đảm nhận.
 */
class RemoteImageImporter
{
    public const MAX_REDIRECTS = 3;

    /** Dung lượng tối đa của 1 ảnh tải về (byte). */
    protected int $maxBytes = 20 * 1024 * 1024;

    protected const MIME_EXTENSIONS = [
        'image/jpeg' => 'jpg',
        'image/pjpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/webp' => 'webp',
        'image/svg+xml' => 'svg',
        'image/bmp' => 'bmp',
    ];

    public function __construct(protected MediaUploader $uploader)
    {
    }

    public function import(string $url): Media
    {
        [$response, $finalUrl] = $this->download($url);
        [$name, $ext] = $this->resolveFileName($finalUrl, $response);

        $dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'newnet_import_' . Str::random(16);
        File::makeDirectory($dir, 0700, true);
        // Lưu tạm đúng tên + đuôi để MediaUploader::setFile() nhận đúng tên file/ext/mime.
        $path = $dir . DIRECTORY_SEPARATOR . $name . '.' . $ext;

        try {
            File::put($path, $response->body());
            $media = $this->uploader->setFile($path)->upload();
        } finally {
            File::deleteDirectory($dir);
        }

        $media->attrs = array_merge($media->attrs ?? [], ['source_url' => $url]);
        $media->save();

        return $media;
    }

    /**
     * @return array{0: Response, 1: string} response ảnh + URL cuối cùng sau redirect
     */
    protected function download(string $url): array
    {
        for ($hop = 0; $hop <= self::MAX_REDIRECTS; $hop++) {
            [$host, $port, $pinnedIp] = $this->assertPublicUrl($url);

            try {
                $response = Http::timeout(15)
                    ->connectTimeout(5)
                    ->withHeaders(['Accept' => 'image/*'])
                    ->withOptions([
                        // Tự theo redirect bên dưới để kiểm tra lại host của từng chặng.
                        'allow_redirects' => false,
                        // Kết nối tới đúng IP vừa kiểm tra, không để curl phân giải DNS lại.
                        'curl' => [CURLOPT_RESOLVE => ["{$host}:{$port}:{$pinnedIp}"]],
                        'on_headers' => function (ResponseInterface $headers) {
                            if ((int) $headers->getHeaderLine('Content-Length') > $this->maxBytes) {
                                throw $this->tooLarge();
                            }
                        },
                        'progress' => function ($total, $downloaded) {
                            if ($downloaded > $this->maxBytes) {
                                throw $this->tooLarge();
                            }
                        },
                    ])
                    ->get($url);
            } catch (RemoteImageException $exception) {
                throw $exception;
            } catch (Throwable $exception) {
                // Guzzle bọc exception ném trong on_headers/progress lại — lấy ra
                // thông báo gốc nếu có, còn lại là lỗi mạng chung chung.
                for ($previous = $exception->getPrevious(); $previous; $previous = $previous->getPrevious()) {
                    if ($previous instanceof RemoteImageException) {
                        throw $previous;
                    }
                }

                throw new RemoteImageException(__('media::media.import.download_failed'));
            }

            if ($response->redirect()) {
                $location = $response->header('Location');
                if (!$location) {
                    throw new RemoteImageException(__('media::media.import.download_failed'));
                }
                $url = (string) UriResolver::resolve(new Uri($url), new Uri($location));
                continue;
            }

            if (!$response->successful()) {
                throw new RemoteImageException(__('media::media.import.download_failed'));
            }

            if (!Str::startsWith($this->mimeType($response), 'image/')) {
                throw new RemoteImageException(__('media::media.import.not_image'));
            }

            if (strlen($response->body()) > $this->maxBytes) {
                throw $this->tooLarge();
            }

            return [$response, $url];
        }

        throw new RemoteImageException(__('media::media.import.download_failed'));
    }

    /**
     * @return array{0: string, 1: int, 2: string} host, port, IP để ghim (IPv6 trong [])
     */
    protected function assertPublicUrl(string $url): array
    {
        $parts = parse_url($url);
        $scheme = strtolower($parts['scheme'] ?? '');
        $host = strtolower(rtrim($parts['host'] ?? '', '.'));

        if (!in_array($scheme, ['http', 'https'], true) || $host === '' || isset($parts['user']) || isset($parts['pass'])) {
            throw new RemoteImageException(__('media::media.import.invalid_url'));
        }

        $port = (int) ($parts['port'] ?? ($scheme === 'https' ? 443 : 80));
        if (!in_array($port, [80, 443], true)) {
            throw new RemoteImageException(__('media::media.import.invalid_url'));
        }

        $ips = $this->resolveHost(trim($host, '[]'));
        if (!$ips) {
            throw new RemoteImageException(__('media::media.import.download_failed'));
        }

        // Chỉ cần 1 IP nội bộ là chặn cả host (tránh host có lẫn bản ghi public + nội bộ).
        foreach ($ips as $ip) {
            if (!$this->isPublicIp($ip)) {
                throw new RemoteImageException(__('media::media.import.invalid_url'));
            }
        }

        $ip = $ips[0];

        return [$host, $port, Str::contains($ip, ':') ? "[{$ip}]" : $ip];
    }

    /**
     * @return string[] IP phân giải được của host (IPv4 trước), rỗng nếu không phân giải được
     */
    protected function resolveHost(string $host): array
    {
        if (filter_var($host, FILTER_VALIDATE_IP)) {
            return [$host];
        }

        $v4 = [];
        $v6 = [];
        foreach (@dns_get_record($host, DNS_A | DNS_AAAA) ?: [] as $record) {
            if (!empty($record['ip'])) {
                $v4[] = $record['ip'];
            }
            if (!empty($record['ipv6'])) {
                $v6[] = $record['ipv6'];
            }
        }

        // dns_get_record không đọc /etc/hosts (vd "localhost") — thử thêm resolver hệ thống.
        if (!$v4 && !$v6) {
            $v4 = gethostbynamel($host) ?: [];
        }

        return array_values(array_unique(array_merge($v4, $v6)));
    }

    protected function isPublicIp(string $ip): bool
    {
        if (defined('FILTER_FLAG_GLOBAL_RANGE')) {
            return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_GLOBAL_RANGE) !== false;
        }

        // PHP < 8.2: cờ cũ không chặn dải CGNAT 100.64.0.0/10.
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
            return false;
        }

        $long = ip2long($ip);

        return $long === false || ($long & 0xFFC00000) !== (ip2long('100.64.0.0') & 0xFFC00000);
    }

    /**
     * Tên lấy từ đường dẫn URL (bỏ query, vd "photo.jpg?w=800" → photo.jpg); đuôi
     * lấy từ URL nếu là đuôi ảnh, không thì suy từ Content-Type (nhiều CDN trả ảnh
     * qua URL không có đuôi).
     *
     * @return array{0: string, 1: string}
     */
    protected function resolveFileName(string $url, Response $response): array
    {
        $basename = rawurldecode(basename((string) parse_url($url, PHP_URL_PATH)));
        $ext = strtolower(pathinfo($basename, PATHINFO_EXTENSION));
        $name = Str::limit(Str::slug(pathinfo($basename, PATHINFO_FILENAME)), 80, '') ?: 'image';

        if (!in_array($ext, array_values(self::MIME_EXTENSIONS), true) && $ext !== 'jpeg') {
            $ext = self::MIME_EXTENSIONS[$this->mimeType($response)] ?? '';
        }

        if ($ext === '') {
            throw new RemoteImageException(__('media::media.upload.unsupported_type'));
        }

        return [$name, $ext];
    }

    protected function mimeType(Response $response): string
    {
        return strtolower(trim(explode(';', (string) $response->header('Content-Type'))[0]));
    }

    protected function tooLarge(): RemoteImageException
    {
        return new RemoteImageException(__('media::media.import.too_large', ['max' => intdiv($this->maxBytes, 1024 * 1024) . 'MB']));
    }
}
