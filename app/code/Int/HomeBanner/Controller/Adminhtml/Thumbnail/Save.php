<?php
/**
 * Indusnet Technologies.
 *
 * @category  Indusnet
 * @package   Int_HomeBanner
 * @author    Indusnet
 */
namespace Int\HomeBanner\Controller\Adminhtml\Thumbnail;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Backend\App\Action;
 
class Save extends Action
{
    
    protected $_model;
    protected $_objectManager;
	protected $_fileSystem;
    public function __construct(
        Action\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Filesystem $filesystem
    ) {
		$this->_objectManager = $objectManager;
		$this->_fileSystem = $filesystem;
        parent::__construct($context);
    }
 
    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        
        if ($data) {
            
            $model = $this->_objectManager->create('Int\HomeBanner\Model\Thumbnail');

            $id = $this->getRequest()->getParam('thumbnail_id');

            if ($id) {
                $model->load($id);
            }
            /** @var \Magento\Framework\Filesystem\Directory\Read $mediaDirectory */
            $mediaDirectory = $this->_objectManager->get('Magento\Framework\Filesystem')->getDirectoryRead(DirectoryList::MEDIA);
			/* Image upload start */
            if (isset($_FILES['thumbnail_image']) && $_FILES['thumbnail_image']['name']!='') 
            {
            
			    $uploader = $this->_objectManager->create(
                    'Magento\MediaStorage\Model\File\Uploader',
                    ['fileId' => 'thumbnail_image']
                );

                $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
                /** @var \Magento\Framework\Image\Adapter\AdapterInterface $imageAdapter */
                $imageAdapter = $this->_objectManager->get('Magento\Framework\Image\AdapterFactory')->create();
                $uploader->setAllowRenameFiles(true);
                $uploader->setFilesDispersion(true);
                
                $result = $uploader->save($mediaDirectory->getAbsolutePath('home_thumbnail'));
               
                $data['thumbnail_image'] = 'home_thumbnail'.$result['file'];
            }
            

            if(isset($data['thumbnail_image']['delete']) && $data['thumbnail_image']['delete'] == '1'){
                $data['thumbnail_image'] = '';
                $mediaFolder = 'home_thumbnail/';
                $imagePath = $mediaDirectory->getAbsolutePath($model->getHomeBannerImage());
                unlink($imagePath);
            }
            
            if(isset($data['thumbnail_image']) && is_array($data['thumbnail_image'])){
                unset($data['thumbnail_image']);
            }       
			/* Image upload end */
            $data['is_active'] = 1;
            $model->setData($data);

            try {
                $model->save();
                $this->messageManager->addSuccess(__('Thumbnail saved successfully.'));
                $this->_getSession()->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['id' => $model->getId(), '_current' => true]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the Thumbnail'));
            }
 
            $this->_getSession()->setFormData($data);
            return $resultRedirect->setPath('*/*/edit', ['thumbnail_id' => $this->getRequest()->getParam('id')]);
        }
        return $resultRedirect->setPath('*/*/');
    }
    
    
    
}
