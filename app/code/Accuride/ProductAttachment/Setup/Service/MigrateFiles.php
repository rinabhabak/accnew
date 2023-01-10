<?php
/**
 * @category  Accuride
 * @package   Accuride_ProductAttachment
 * @copyright Copyright (c) Sitewards GmbH (http://www.sitewards.com)
 * @contact   mail@sitewards.com
 */



namespace Accuride\ProductAttachment\Setup\Service;

use Amasty\ProductAttachment\Controller\Adminhtml\RegistryConstants;
use Amasty\ProductAttachment\Model\File\FileScope\SaveFileScopeInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Amasty\ProductAttachment\Model\File\FileFactory;
use Amasty\ProductAttachment\Model\File\Repository as FileRepository;
use Amasty\ProductAttachment\Model\SourceOptions\AttachmentType;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Amasty\ProductAttachment\Setup\Operation\RenameOldTables;

class MigrateFiles
{

    /**
     * @var FileFactory
     */
    private $fileFactory;

    /**
     * @var FileRepository
     */
    private $fileRepository;

    /**
     * @var FileSystem
     */
    private $fileSystem;

    /**
     * @var array
     */
    private $savedFiles = [];

    /**
     * @var array
     */
    private $fileIds = [];

    /**
     * @var SaveFileScopeInterface
     */
    private $saveFileScope;

    public function __construct(
        FileFactory $fileFactory,
        FileRepository $fileRepository,
        FileSystem $filesystem,
        SaveFileScopeInterface $saveFileScope
    ) {
        $this->fileFactory = $fileFactory;
        $this->fileRepository = $fileRepository;
        $this->fileSystem = $filesystem;
        $this->saveFileScope = $saveFileScope;
    }

    /**
     * This routine is copied from the data migration for ver 2.0.0 of the original amasty extension.
     * There is bug in the original upgrade script which merges all attachments of type link in to one
     * an links all product with link attachements to same one. This is the original code as it is written by amasty
     * with minor changes to fix the issue wrapped inside sitewards comments.
     * In general the main issue was that attachment `filepath` was used as an identifier which actually is an empty
     * string for all attachents of type link and therefore they are all merged. The fix applied is to introduce an new
     * identifier which uses `filepath` for entries of type file then uses hash of the url for those entries which
     * are type link
     *
     * @param ModuleDataSetupInterface $setup
     */
    public function execute(ModuleDataSetupInterface $setup)
    {
        $connection = $setup->getConnection();
        $filesSelect = $connection
            ->select()
            ->from(
                ['main_table' => $setup->getTable(RenameOldTables::AMASTY_FILE_OLD)]
            )->joinLeft(
                ['groups' => $setup->getTable(RenameOldTables::AMASTY_FILE_CUSTOMER_GROUP_OLD)],
                'main_table.id = groups.file_id',
                ['customer_groups' => 'GROUP_CONCAT(customer_group_id)']
            )->joinLeft(
                ['store' => $setup->getTable(RenameOldTables::AMASTY_FILE_STORE_OLD)],
                'main_table.id = store.file_id',
                ['label', 'is_visible', 'show_for_ordered']
            )->group('main_table.id');

        $files = $connection->fetchAll($filesSelect);

        $mediaPath = $this->fileSystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath()
            . 'amasty' . DIRECTORY_SEPARATOR
            . 'amfile' . DIRECTORY_SEPARATOR . 'attach' . DIRECTORY_SEPARATOR;

        foreach ($files as $oldFiles) {
            /** @var \Amasty\ProductAttachment\Model\File\File $file */
            $file = $this->fileFactory->create();
            if (!empty($oldFiles['product_id'])) {
                $file->setProducts([$oldFiles['product_id']]);
            }
            if (!empty($oldFiles['category_id'])) {
                $file->setCategories([$oldFiles['category_id']]);
            }
            if (isset($oldFiles['customer_groups'])) {
                $customerGroups = explode(',', $oldFiles['customer_groups']);
            } else {
                $customerGroups = [];
            }
            $file->setCustomerGroups($customerGroups);
            $path = explode('.', $oldFiles['file_path']);
            $extension = end($path);
            if ($oldFiles['file_type'] == 'url') {
                // SITEWARDS CHANGE START
                $identifier = md5($oldFiles['file_url']);
                $file->setData('identifier', md5($oldFiles['file_url']));
                // SITEWARDS CHANGE END
                $file->setAttachmentType(AttachmentType::LINK);
            } else {
                // SITEWARDS CHANGE START
                $file->setData('identifier', $oldFiles['file_path']);
                // SITEWARDS CHANGE END
                $file->setAttachmentType(AttachmentType::FILE);
            }
            $file->setLink($oldFiles['file_url']);
            $file->setFileExtension($extension);
            $file->setFileName($oldFiles['file_name']);
            $fileInfo = [
                'name' => '',
                'tmp_name' => $mediaPath . $oldFiles['file_path'],
                'file' => $mediaPath . $oldFiles['file_path']
            ];
            $file->setFile([$fileInfo]);
            $file->setLabel($oldFiles['label']);
            $file->setIsVisible($oldFiles['is_visible']);
            $file->setIsIncludeInOrder($oldFiles['show_for_ordered']);
            try {
                // SITEWARDS CHANGE START
                if (!isset($this->savedFiles[$file->getData('identifier')])) {
                    $file = $this->fileRepository->saveAll($file, [], false);
                    $this->savedFiles[$file->getData('identifier')] = $file->getFileId();
                } else {
                    if ($products = $file->getProducts()) {
                        $file->setFileId($this->savedFiles[$file->getData('identifier')]);
                        $file->setData('link', '');
                        $file->setData('file', '');
                        $file->setData('position', 0);
                        $this->saveFileScope->execute(
                            [
                                RegistryConstants::FILES => [
                                    $file,
                                ],
                                RegistryConstants::PRODUCT => $products[0]
                            ],
                            'product'
                        );
                    }

                    if ($categories = $file->getCategories()) {
                        $file->setFileId($this->savedFiles[$file->getData('identifier')]);
                        $file->setData('link', '');
                        $file->setData('file', '');
                        $file->setData('position', 0);
                        $this->saveFileScope->execute(
                            [
                                RegistryConstants::FILES => [
                                    $file
                                ],
                                RegistryConstants::CATEGORY => $categories[0]
                            ],
                            'category'
                        );
                    }
                }
                // SITEWARDS CHANGE END

                $this->fileIds[$oldFiles['id']] = $file->getFileId();
            } catch (\Magento\Framework\Exception\CouldNotSaveException $e) {
            }
        }
    }
}
