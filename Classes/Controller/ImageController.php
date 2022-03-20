<?php

declare(strict_types=1);

namespace Hardanders\Instagram\Controller;

use Hardanders\Instagram\Domain\Repository\ImageRepository;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

final class ImageController extends ActionController
{
    protected ImageRepository $imageRepository;

    public function __construct(ImageRepository $imageRepository)
    {
        $this->imageRepository = $imageRepository;
    }

    public function listAction(): void
    {
        $hashtagArray = [];
        $images = [];

        if (\strlen($this->settings['showImagesByHashtag']) > 0) {
            $hashtagArray[] = $this->settings['showImagesByHashtag'];

            if (false !== strpos($this->settings['showImagesByHashtag'], ',')) {
                $hashtagArray = explode(',', str_replace(' ', '', $this->settings['showImagesByHashtag']));
            }

            $images = $this->imageRepository->findImagesByHashtags(
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
            $images = $this->imageRepository->findAll();
        }

        if (\count($types) > 0) {
            $images = $this->imageRepository->findByTypes($types);
        }

        if ($this->settings['maxImagesToDisplay']) {
            $images = array_slice($images->toArray(), 0, $this->settings['maxImagesToDisplay']);
        }

        $this->view->assign('images', $images);
    }
}
