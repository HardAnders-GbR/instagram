<?php

declare(strict_types=1);

namespace Hardanders\Instagram\Controller;

use Hardanders\Instagram\Domain\Repository\PostRepository;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

final class PostController extends ActionController
{
    protected PostRepository $postRepository;

    public function __construct(PostRepository $postRepository)
    {
        $this->postRepository = $postRepository;
    }

    public function listAction(): void
    {
        $settings = [
            'types' => [],
            'hashtags' => [
                'tags' => [],
                'logicalConstraint' => $this->settings['hashtags']['logicalConstraint'],
            ],
        ];

        if ($this->settings['limit']) {
            $settings['limit'] = (int)$this->settings['limit'];
        }

        if (\strlen($this->settings['hashtags']['tags']) > 0) {
            $settings['hashtags']['tags'] = explode(',', str_replace(' ', '', $this->settings['hashtags']['tags']));
        }

        foreach ($this->settings['types'] as $key => $value) {
            if ($value) {
                $settings['types'][] = $key;
            }
        }

        $posts = $this->postRepository->findBySettings($settings);
        DebuggerUtility::var_dump($settings);
        DebuggerUtility::var_dump($posts);

        foreach ($posts as $post) {
            DebuggerUtility::var_dump($post->getType() . ' : ' . $post->getText());
        }
        die("hier");

        $this->view->assign('posts', $posts);
    }
}
