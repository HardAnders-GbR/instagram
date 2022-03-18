<?php

if (! defined('TYPO3')) {
    die('Access denied.');
}

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'Hardanders.Instagram',
    'Pi1',
    [
        \Hardanders\Instagram\Controller\ImageController::class => 'list, show',
    ],
    // non-cacheable actions
    [
        \Hardanders\Instagram\Controller\ImageController::class => '',
    ]
);
