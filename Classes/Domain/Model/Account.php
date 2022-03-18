<?php

declare(strict_types=1);

namespace Hardanders\Instagram\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

final class Account extends AbstractEntity
{
    /**
     * @var int
     */
    protected $_languageUid;

    protected string $userid = '';

    protected string $username = '';

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\Hardanders\Instagram\Domain\Model\Image>
     */
    protected ?ObjectStorage $images = null;

    protected int $lastupdate = 0;

    public function __construct(string $userId)
    {
        $this->userid = $userId;
        $this->images = new ObjectStorage();
    }

    public function setSysLanguageUid(int $_languageUid): self
    {
        $this->_languageUid = $_languageUid;

        return $this;
    }

    public function getSysLanguageUid(): int
    {
        return $this->_languageUid;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getUserid(): string
    {
        return $this->userid;
    }

    public function addImage(Image $image): self
    {
        $this->images->attach($image);

        return $this;
    }

    /**
     * @param Image $imageToRemove The Image to be removed
     */
    public function removeImage(Image $imageToRemove): self
    {
        $this->images->detach($imageToRemove);

        return $this;
    }

    public function getImages(): ObjectStorage
    {
        return $this->images;
    }

    public function setImages(ObjectStorage $images): self
    {
        $this->images = $images;

        return $this;
    }

    public function getLastupdate(): int
    {
        return $this->lastupdate;
    }

    public function setLastupdate(int $lastupdate): self
    {
        $this->lastupdate = $lastupdate;

        return $this;
    }
}
