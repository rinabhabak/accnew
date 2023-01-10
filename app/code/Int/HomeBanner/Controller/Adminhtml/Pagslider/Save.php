<?php
/**
 * Indusnet Technologies.
 *
 * @category  Indusnet
 * @package   Int_HomeBanner
 * @author    Indusnet
 */
namespace Int\HomeBanner\Controller\Adminhtml\Pagslider;
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
     * {@inheritdoc}
     */
    /*
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Int_HomeBanner::sliderlist_save');
    }
    */
 
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
          $id = $this->getRequest()->getParam('slider_id');
        
        if ($data) {
            
            $model = $this->_objectManager->create('Int\HomeBanner\Model\PagSlider');
             $id = $this->getRequest()->getParam('slider_id');
            if ($id) {
                $model->load($id);
            }
            /** @var \Magento\Framework\Filesystem\Directory\Read $mediaDirectory */
                 $mediaDirectory = $this->_objectManager->get('Magento\Framework\Filesystem')
                    ->getDirectoryRead(DirectoryList::MEDIA);
			/* Image upload start */
            if (isset($_FILES['home_banner_image']) && $_FILES['home_banner_image']['name']!='') 
            {
            
			     $uploader = $this->_objectManager->create(
                    'Magento\MediaStorage\Model\File\Uploader',
                    ['fileId' => 'home_banner_image']
                );
                $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
                /** @var \Magento\Framework\Image\Adapter\AdapterInterface $imageAdapter */
                $imageAdapter = $this->_objectManager->get('Magento\Framework\Image\AdapterFactory')->create();
                $uploader->setAllowRenameFiles(true);
                $uploader->setFilesDispersion(true);
                

                $result = $uploader->save($mediaDirectory->getAbsolutePath('homebanner'));
               
                $data['home_banner_image'] = 'homebanner'.$result['file'];
            }
            

                if(isset($data['home_banner_image']['delete']) && $data['home_banner_image']['delete'] == '1'){
                $data['home_banner_image'] = '';
                $mediaFolder = 'homebanner/';
                $imagePath = $mediaDirectory->getAbsolutePath($model->getHomeBannerImage());
                unlink($imagePath);
            }
             if(isset($data['home_banner_image']) && is_array($data['home_banner_image'])){
                unset($data['home_banner_image']);
            }       
			/* Image upload end */
			
			
			
            $model->setData($data);
            try {
                $model->save();
                $this->messageManager->addSuccess(__('Home Banner saved'));
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
                $this->messageManager->addException($e, __('Something went wrong while saving the Banner'));
            }
 
            $this->_getSession()->setFormData($data);
            return $resultRedirect->setPath('*/*/edit', ['slider_id' => $this->getRequest()->getParam('id')]);
        }
        return $resultRedirect->setPath('*/*/');
    }
    
    
    
}
