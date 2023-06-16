<?php

namespace Newnet\Media;

class ImgSupport
{
    public function html($file, $options = [])
    {
        $class = $options['class'] ?? '';
        $alt = $options['alt'] ?? '';

        $url_1 = self::url($file, 300);
        $url_2 = self::url($file, 720);
        $url_3 = self::url($file, 960);
        $url_4 = self::url($file, 1200);

        return sprintf('<img class="%s"
            srcset="%s 300w,
                    %s 720w,
                    %s 960w,
                    %s 1200w"
            sizes="(max-width: 960px) 300vw, 1200px"
            src="%s"
            alt="%s"
            loading="lazy"
        />', $class, $url_1, $url_2, $url_3, $url_4, $file, $alt);
    }

    public function url($file, $width, $height = null)
    {
        if (preg_match('/^http/', $file)) {
            if (parse_url($file, PHP_URL_HOST) == parse_url(url('/'), PHP_URL_HOST)) {
                $file = ltrim(parse_url($file, PHP_URL_PATH), '/');
            } else {
                return $file;
            }
        }

        $size = 'w'.$width;

        if ($height) {
            $size .= 'h'.$height;
        }

        return asset("images/size/{$size}/{$file}");
    }
}
