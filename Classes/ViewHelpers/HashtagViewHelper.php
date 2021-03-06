<?php

namespace Hardanders\Instagram\ViewHelpers;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class HashtagViewHelper extends AbstractViewHelper
{
    public function initializeArguments(): void
    {
        $this->registerArgument('tags', 'string', 'The email address to resolve the gravatar for', true);
    }

    /**
     * @param string|null $tags comma-separated string of hashtags
     *
     * @return string[] array of hashtags and links
     */
    public function render(?string $tags = null): array
    {
        $tagsArr = explode(',', $tags);
        $return = [];

        foreach ($tagsArr as $key => $tag) {
            $return[$key]['tag'] = $tag;
            $return[$key]['link'] = 'https://www.instagram.com/explore/tags/' . str_replace('#', '', $tag) . '/';
        }

        return $return;
    }
}
