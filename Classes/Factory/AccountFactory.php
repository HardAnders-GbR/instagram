<?php

declare(strict_types=1);

namespace Hardanders\Instagram\Factory;

use Hardanders\Instagram\Domain\Model\Account;

class AccountFactory
{
    public function createNew($userId): Account
    {
        return new Account($userId);
    }

    public function createFromAPIResponse(array $apiData): Account
    {
        return ($this->createNew($apiData['id']))
            ->setUsername($apiData['username'])
            ->setLastupdate(time())
        ;
    }
}
