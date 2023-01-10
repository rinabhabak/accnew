<?php
/**
 * Indusnet Technologies.
 *
 * @category  Indusnet
 * @package   Int_HomeBanner
 * @author    Indusnet
 */
namespace Int\HomeBanner\Controller\Adminhtml\Thumbnail;
 
use Magento\Backend\App\Action;
use Magento\Framework\App\Filesystem\DirectoryList;
 
class Delete extends Action
{
    protected $_model;
    protected $_objectManager;
    public function __construct(
        Action\Context $context,
        \Int\HomeBanner\Model\Thumbnail $model,
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        parent::__construct($context);
        $this->_model = $model;
        $this->_objectManager = $objectManager;
    }

 
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
                $imagePath = $mediaDirectory->getAbsolutePath($model->getThumbnailImage());
                if(isset($imagePath)){
                    unlink($imagePath);
                }
                $this->messageManager->addSuccess(__('Thumbnail deleted.'));
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                return $resultRedirect->setPath('*/*/edit', ['id' => $id]);
            }
        }
        $this->messageManager->addError(__('Thumbnail does not exist'));
        return $resultRedirect->setPath('*/*/');
    }
}
