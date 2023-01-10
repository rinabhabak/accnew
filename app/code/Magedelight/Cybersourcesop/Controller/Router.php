<?php
namespace Magedelight\Cybersourcesop\Controller;


class Router implements \Magento\Framework\App\RouterInterface
{
    /**
     * @var \Magento\Framework\App\ActionFactory
     */
    protected $actionFactory;

    /**
     * Response.
     *
     * @var \Magento\Framework\App\ResponseInterface
     */
    protected $_response;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param \Magento\Framework\App\ActionFactory               $actionFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\App\ResponseInterface           $response
     */
    public function __construct(
    \Magento\Framework\App\ActionFactory $actionFactory, 
    \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig, 
    \Magento\Framework\App\ResponseInterface $response,
    \Magento\Framework\Data\Form\FormKey $formKey        
    ) {
        $this->actionFactory = $actionFactory;
        $this->scopeConfig = $scopeConfig;
        $this->_response = $response;
        $this->formKey = $formKey;
    }

    /**
     * Validate and Match.
     *
     * @param \Magento\Framework\App\RequestInterface $request
     *
     * @return bool
     */
    public function match(\Magento\Framework\App\RequestInterface $request)
    {
        //echo "test"; die();
        $requesturl = trim($request->getPathInfo(), '/');
        if($requesturl=='cybersourcesop/SilentOrder/TokenResponse'){
            $formkey = $this->formKey->getFormKey();
            $request->setParam('form_key',$formkey);
        }
    }

}

