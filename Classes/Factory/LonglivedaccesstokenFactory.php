<?php

declare(strict_types=1);

namespace Hardanders\Instagram\Factory;

use DateTime;
use Hardanders\Instagram\Domain\Model\Longlivedaccesstoken;

final class LonglivedaccesstokenFactory
{
    public function create(string $token, string $type, DateTime $expiresAt, string $userId): Longlivedaccesstoken
    {
        return (new Longlivedaccesstoken())
            ->setToken($token)
            ->setExpiresAt($expiresAt)
            ->setType($type)
            ->setUserid($userId)
        ;
    }
}
