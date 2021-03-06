<?php

declare(strict_types=1);

namespace Hardanders\Instagram\Service;

class EmojiService
{
    public static function remove_emoji(string $string = ''): string
    {
        $clear_string = preg_replace('%(?:
          \xF0[\x90-\xBF][\x80-\xBF]{2}      # planes 1-3
        | [\xF1-\xF3][\x80-\xBF]{3}          # planes 4-15
        | \xF4[\x80-\x8F][\x80-\xBF]{2}      # plane 16
    )%xs', '', $string);

        return str_replace(' # ', ' ', $clear_string);
    }
}
