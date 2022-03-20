<?php

if (! defined('TYPO3')) {
    die('Access denied.');
}

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'Hardanders.Instagram',
    'Pi1',
    'Instagram'
);

$pluginSignature = str_replace('_', '', 'instagram') . '_pi1';
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
    $pluginSignature,
    'FILE:EXT:instagram/Configuration/FlexForms/pi1.xml'
);
