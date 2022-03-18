<?php

declare(strict_types=1);

namespace Hardanders\Instagram\Domain\Repository;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

final class ImageRepository extends Repository
{
    protected $defaultOrderings = [
        'createdtime' => QueryInterface::ORDER_DESCENDING,
    ];

    public function initializeObject()
    {
        $querySettings = GeneralUtility::makeInstance(Typo3QuerySettings::class);
        $querySettings->setRespectStoragePage(false);

        $this->setDefaultQuerySettings($querySettings);
    }

    /**
     * @param string[] $types
     */
    public function findByTypes(array $types): QueryResultInterface
    {
        $query = $this->createQuery();

        $constrains = [];
        foreach ($types as $type) {
            $constrains[] = $query->equals('type', $type);
        }

        $query->matching($query->logicalOr($constrains));

        return $query->execute();
    }

    public function findImagesByHashtags(array $hashtags, string $logicalConstraint): QueryResultInterface
    {
        $constraints = [];
        $query = $this->createQuery();

        foreach ($hashtags as $tag) {
            $constraints[] = $query->like('tags', '%,' . $tag . ',%');
        }

        $query->matching($query->{$logicalConstraint}($constraints));

        return $query->execute();
    }
}
