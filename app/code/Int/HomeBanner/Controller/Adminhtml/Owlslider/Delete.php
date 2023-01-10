<?php
/**
 * Indusnet Technologies.
 *
 * @category  Indusnet
 * @package   Int_HomeBanner
 * @author    Indusnet
 */
namespace Int\HomeBanner\Controller\Adminhtml\Owlslider;
 
use Magento\Backend\App\Action;
use Magento\Framework\App\Filesystem\DirectoryList;
 
class Delete extends Action
{
    protected $_model;
    protected $_objectManager;
    public function __construct(
        Action\Context $context,
        \Int\HomeBanner\Model\SecondSlider $model,
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        parent::__construct($context);
        $this->_model = $model;
        $this->_objectManager = $objectManager;
    }
 
    /**
     * {@inheritdoc}
     */
    /*
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Int_HomeBanner::sliderlist_delete');
    }
    */
 
    /**
     * Delete action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($id) {
            try {
                $model = $this->_model;
                $model->load($id);
                $model->delete();
                $mediaDirectory = $this->_objectManager->get('Magento\Framework\Filesystem')
            ->getDirectoryRead(DirectoryList::MEDIA);
                // Delete, Upload Image
                $imagePath = $mediaDirectory->getAbsolutePath($model->getHomeBannerImage());
                if(isset($imagePath))
                unlink($imagePath);
                $this->messageManager->addSuccess(__('Home Banner deleted'));
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                return $resultRedirect->setPath('*/*/edit', ['id' => $id]);
            }
        }
        $this->messageManager->addError(__('Banner does not exist'));
        return $resultRedirect->setPath('*/*/');
    }
}
