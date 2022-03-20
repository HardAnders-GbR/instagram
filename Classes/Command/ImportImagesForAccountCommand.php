<?php

declare(strict_types=1);

namespace Hardanders\Instagram\Command;

use GuzzleHttp\Exception\ClientException;
use Hardanders\Instagram\Client\InstagramApiClient;
use Hardanders\Instagram\Domain\Model\Account;
use Hardanders\Instagram\Domain\Model\Image;
use Hardanders\Instagram\Domain\Model\Longlivedaccesstoken;
use Hardanders\Instagram\Domain\Repository\AccountRepository;
use Hardanders\Instagram\Domain\Repository\ImageRepository;
use Hardanders\Instagram\Domain\Repository\LonglivedaccesstokenRepository;
use Hardanders\Instagram\Factory\AccountFactory;
use Hardanders\Instagram\Factory\ImageFactory;
use Hardanders\Instagram\Service\InstagramService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\Folder;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;
use TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException;
use TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface;

class ImportImagesForAccountCommand extends Command
{
    private InstagramApiClient $instagramApiClient;

    private ImageRepository $imageRepository;

    private AccountRepository $accountRepository;

    private LonglivedaccesstokenRepository $longlivedaccesstokenRepository;

    private PersistenceManagerInterface $persistenceManager;

    private AccountFactory $accountFactory;

    private InstagramService $instagramService;
    private ImageFactory $imageFactory;

    public function __construct(
        InstagramApiClient             $instagramApiClient,
        ImageRepository                $imageRepository,
        AccountRepository              $accountRepository,
        LonglivedaccesstokenRepository $longlivedaccesstokenRepository,
        PersistenceManagerInterface    $persistenceManager,
        AccountFactory                 $accountFactory,
        InstagramService               $instagramService,
        ImageFactory                   $imageFactory,
                                       $name = null
    )
    {
        parent::__construct($name);

        $this->instagramApiClient = $instagramApiClient;
        $this->imageRepository = $imageRepository;
        $this->accountRepository = $accountRepository;
        $this->longlivedaccesstokenRepository = $longlivedaccesstokenRepository;
        $this->persistenceManager = $persistenceManager;
        $this->accountFactory = $accountFactory;
        $this->instagramService = $instagramService;
        $this->imageFactory = $imageFactory;
    }

    protected function configure()
    {
        $this
            ->setHelp('Imports images for a given instagram account')
            ->addArgument('userId', InputArgument::REQUIRED, 'Instagram User/Account ID to import images for')
            ->addArgument('storagePid', InputArgument::REQUIRED, 'The PID where to save the image records');
    }

    /**
     * creates or updates an Account to add images to
     * Returns the Account-Object.
     */
    public function upsertAccount(?Account $account, array $user, int $storagePid): Account
    {
        if (null === $account) {
            $account = $this->accountFactory->createFromAPIResponse($user);

            $this->accountRepository->add($account);
            $this->persistenceManager->persistAll();
        }

        $account->setSysLanguageUid(-1);
        $account->setPid($storagePid);

        $this->accountRepository->update($account);
        $this->persistenceManager->persistAll();

        return $account;
    }

    /**
     * Adds or updates an Image - depending on the given action - and adds it to a given account.
     *
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
    public function handleImage(?Image $image, int $imageId, Account $account, int $storagePid): Image
    {
        $imageData = [];

        $action = $image ? 'UPDATE' : 'NEW';

        if (null === $image) {
            $imageData = $this->instagramApiClient->getMedia($imageId);
            $image = $this->imageFactory->createFromAPIResponse($imageData);
        }

        echo "Handling image '" . $image->getText() . "' \n";

        $image->setPid($storagePid);
        $image->setSysLanguageUid(-1);
        $this->imageRepository->add($image);

        $account->addImage($image);
        $this->accountRepository->update($account);
        $this->persistenceManager->persistAll();

        if ('NEW' === $action) {
            switch ($image->getType()) {
                case 'CAROUSEL_ALBUM':
                    $childMediaIds = $this->instagramApiClient->getChildrenMediaIds($imageData['id']);
                    $childMedias = $this->instagramService->getCarouselMedia($childMediaIds);

                    foreach ($childMedias as $item) {
                        switch ($item['media_type']) {
                            case 'IMAGE':
                                $fileObject = $this->downloadFile(
                                    $item['media_url'],
                                    'jpg'
                                );

                                $this->addToFal($image, $fileObject, 'tx_instagram_domain_model_image', 'image');

                                break;
                            case 'VIDEO':
                                $fileObject = $this->downloadFile($item['media_url'], 'mp4');
                                $this->addToFal($image, $fileObject, 'tx_instagram_domain_model_image', 'videos');

                                break;
                        }
                    }

                    break;
                case 'VIDEO':
                    $fileObject = $this->downloadFile($imageData['media_url'], 'mp4');
                    $this->addToFal($image, $fileObject, 'tx_instagram_domain_model_image', 'videos');

                    $fileObject = $this->downloadFile($imageData['thumbnail_url'], 'jpg');
                    $this->addToFal($image, $fileObject, 'tx_instagram_domain_model_image', 'image');

                    break;
                case 'IMAGE':
                    $fileObject = $this->downloadFile($imageData['media_url'], 'jpg');
                    $this->addToFal($image, $fileObject, 'tx_instagram_domain_model_image', 'image');

                    break;
            }
        }

        $this->imageRepository->update($image);
        $this->persistenceManager->persistAll();

        return $image;
    }

    /**
     * Downloads a file from a given URL with the given fileextension
     * Return an fileObject of the downloaded file.
     *
     * @return File|Folder
     * @throws \TYPO3\CMS\Core\Resource\Exception\ResourceDoesNotExistException
     *
     */
    public function downloadFile(string $fileUrl, string $type)
    {
        $directory = Environment::getPublicPath() . '/fileadmin/instagram';
        GeneralUtility::mkdir_deep($directory);

        $directory = str_replace('1:', 'uploads', $directory);
        $filePath = $directory . '/instagram-' . md5($fileUrl) . '.' . $type;

        $data = file_get_contents($fileUrl);
        file_put_contents($filePath, $data);

        return GeneralUtility::makeInstance(ResourceFactory::class)->retrieveFileOrFolderObject($filePath);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $userId = $input->getArgument('userId');
        $storagePid = (int)$input->getArgument('storagePid');

        $longlivedToken = $this->longlivedaccesstokenRepository->findOneByUserid((int)$userId);

        if (!$longlivedToken instanceof Longlivedaccesstoken) {
            throw new \Exception('Kein Longlivedaccesstoken gefunden!');
        }

        $accesstoken = $longlivedToken->getToken();
        $this->instagramApiClient->setAccesstoken($accesstoken);

        try {
            $instagramUser = $this->instagramApiClient->getUserdata($userId);
        } catch (ClientException $exception) {
            $message = $exception->getMessage();

            if (strpos($message, 'Application request limit reached') !== false) {
                $io->warning("The APIs rate limit of 200 requests/hour is exhausted. Please try again later.");

                return Command::FAILURE;
            }
        }

        if (isset($instagramUser['error'])) {
            throw new \Exception($instagramUser['error']);
        }

        if (!isset($instagramUser['id'])) {
            throw new \Exception('No Instagram user_id found in API response.');
        }

        $account = $this->accountRepository->findOneByUserid($instagramUser['id']);
        $account = $this->upsertAccount($account, $instagramUser, $storagePid);

        $output->writeln([
            'Importing images for IG-Account: ' . $account->getUsername(),
            '============',
            '',
        ]);

        $images = $this->instagramApiClient->getImagesRecursive($userId);

        foreach ($images as $imageData) {
            $image = $this->imageRepository->findOneByInstagramid($imageData['id']);
            $this->handleImage($image, (int)$imageData['id'], $account, $storagePid);
        }

        return self::SUCCESS;
    }

    /**
     * adds an image to the fal.
     */
    protected function addToFal(Image $newElement, File $file, string $tablename, string $fieldname): void
    {
        $fields = [
            'pid' => $newElement->getPid(),
            'uid_local' => $file->getUid(),
            'uid_foreign' => $newElement->getUid(),
            'tablenames' => $tablename,
            'table_local' => 'sys_file',
            'fieldname' => $fieldname,
            'l10n_diffsource' => '',
            'sorting_foreign' => $file->getUid(),
            'tstamp' => time(),
            'crdate' => time(),
        ];

        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);

        $databaseConn = $connectionPool->getConnectionForTable('sys_file_reference');
        $databaseConn->insert('sys_file_reference', $fields);
    }
}
