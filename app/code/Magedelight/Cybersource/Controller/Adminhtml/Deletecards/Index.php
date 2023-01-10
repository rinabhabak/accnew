<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Cybersource
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Cybersource\Controller\Adminhtml\Deletecards;

use Magedelight\Cybersource\Model\CardsFactory;
use Magento\Framework\Controller\ResultFactory;

class Index extends \Magento\Backend\App\Action
{
    
    public $cards;
    
    /**
     * 
     * @param \Magento\Backend\App\Action\Context $context
     * @param CardsFactory $cards
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        CardsFactory $cards
    ){
        parent::__construct($context);
        $this->cards = $cards;        
    }

    public function execute()
    {
        try {
            $cards = $this->cards->create()
                    ->getCollection();
            
            if(!empty($cards->getData())){
                foreach ($cards as $card){                   
                    $card->delete();
                }
                $this->messageManager->addSuccessMessage(__('Your Cards has been deleted.'));
            }
            else{
                $this->messageManager->addErrorMessage(__('Customers having Cards not found.')); 
            }
            
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setUrl($this->_redirect->getRefererUrl());            
            return $resultRedirect;
            
        } catch (\Exception $e){
            $this->messageManager->addErrorMessage(__("Cards not found, Try again"));
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setUrl($this->_redirect->getRefererUrl());
            return $resultRedirect;
        }
        return ;
    }
 
    protected function _isAllowed()
    {
        return true;
    }
}
