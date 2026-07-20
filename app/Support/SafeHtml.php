<?php

namespace App\Support;

use Illuminate\Support\HtmlString;

class SafeHtml
{
    public static function plainText(?string $html): string
    {
        $decodedHtml = html_entity_decode((string) $html, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return trim((string) preg_replace('/\s+/u', ' ', strip_tags($decodedHtml)));
    }

    public static function richText(?string $html): HtmlString
    {
        if (! filled($html)) {
            return new HtmlString('');
        }

        $cachePath = storage_path('framework/cache/htmlpurifier');

        if (! is_dir($cachePath)) {
            mkdir($cachePath, 0755, true);
        }

        $decodedHtml = html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        $config = \HTMLPurifier_Config::createDefault();
        $config->set('Cache.SerializerPath', $cachePath);
        $config->set('Attr.AllowedFrameTargets', ['_blank']);

        return new HtmlString((new \HTMLPurifier($config))->purify($decodedHtml));
    }
}
