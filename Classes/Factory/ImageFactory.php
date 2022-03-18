<?php

declare(strict_types=1);

namespace Hardanders\Instagram\Factory;

use DateTime;
use Hardanders\Instagram\Domain\Model\Image;
use Hardanders\Instagram\Service\EmojiService;

class ImageFactory
{
    public function create(): Image
    {
        return new Image();
    }

    public function createFromAPIResponse(array $apiData): Image
    {
        $image = ($this->create())
            ->setCreatedtime((int)(new DateTime($apiData['timestamp']))->format('U'))
            ->setType($apiData['media_type'])
            ->setInstagramid($apiData['id'])
            ->setLink($apiData['permalink'])
            ->setLastupdate(time())
        ;

        if ($apiData['caption']) {
            $image->setText(EmojiService::remove_emoji($apiData['caption']));

            preg_match_all('/#(\\w+)/', $apiData['caption'], $hashtags);

            if ($hashtags[0]) {
                $hashtagsString = implode(' ', $hashtags[0]);
                $image->setTags($hashtagsString);
            }
        }

        return $image;
    }
}
