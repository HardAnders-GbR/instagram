<?php

declare(strict_types=1);

namespace Hardanders\Instagram\Domain\Model;

use DateTime;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

final class Longlivedaccesstoken extends AbstractEntity
{
    protected ?string $userid = null;

    protected ?string $token = null;

    protected ?string $type = null;

    protected ?DateTime $expiresat = null;

    public function getUserid(): ?string
    {
        return $this->userid;
    }

    public function setUserid(?string $userid): self
    {
        $this->userid = $userid;

        return $this;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(?string $token): self
    {
        $this->token = $token;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getExpiresat(): ?DateTime
    {
        return $this->expiresat;
    }

    public function setExpiresat(?DateTime $expiresat): self
    {
        $this->expiresat = $expiresat;

        return $this;
    }
}
