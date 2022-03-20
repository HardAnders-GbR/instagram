<?php

declare(strict_types=1);

namespace Hardanders\Instagram\Controller;

use Hardanders\Instagram\Domain\Repository\PostRepository;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

final class PostController extends ActionController
{
    protected PostRepository $postRepository;

    public function __construct(PostRepository $postRepository)
    {
        $this->postRepository = $postRepository;
    }

    public function listAction(): void
    {
        $hashtagArray = [];
        $posts = [];

        if (\strlen($this->settings['showPostsByHashtag']) > 0) {
            $hashtagArray[] = $this->settings['showPostsByHashtag'];

            if (false !== strpos($this->settings['showPostsByHashtag'], ',')) {
                $hashtagArray = explode(',', str_replace(' ', '', $this->settings['showPostsByHashtag']));
            }

            $posts = $this->postRepository->findPostsByHashtags(
                $hashtagArray,
                $this->settings['logicalConstraint']
            );
        }

        $types = [];
        foreach ($this->settings['show'] as $key => $value) {
            if ($value) {
                $types[] = $key;
            }
        }

        if (0 === \count($types)) {
            $posts = $this->postRepository->findAll();
        }

        if (\count($types) > 0) {
            $posts = $this->postRepository->findByTypes($types);
        }

        if ($this->settings['maxPostsToDisplay']) {
            $posts = array_slice($posts->toArray(), 0, $this->settings['maxPostsToDisplay']);
        }

        $this->view->assign('posts', $posts);
    }
}
