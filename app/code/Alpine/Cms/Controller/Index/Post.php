<?php
/**
 * Quote Form Post Controller
 *
 * @category    Alpine
 * @package     Alpine_Cms
 * @copyright   Copyright (c) 2018 Alpine Consulting Inc.
 * @author      Dmitry Naumov <dmitry.naumov@alpineinc.com>
 */

namespace Alpine\Cms\Controller\Index;

use Alpine\Cms\Controller\Index;
use Alpine\Cms\Model\Quote\Config;
use Alpine\Cms\Model\Quote\Mail;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\HTTP\PhpEnvironment\Request;
use Magento\Framework\DataObject;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Data\Form\FormKey\Validator;

/**
 * Quote Form Post Controller
 *
 * @category    Alpine
 * @package     Alpine_Cms
 */
class Post extends Index
{
    /**
     * Data persistor
     *
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * Context
     *
     * @var Context
     */
    private $context;

    /**
     * Mail interface
     *
     * @var Mail
     */
    private $mail;

    /**
     * Logger interface
     *
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Config
     */
    private $quoteConfig;

    /**
     * @var Validator
     */
    protected $_formKeyValidator;
    
    /**
     * Post constructor
     *
     * @param Context $context
     * @param Config $quoteConfig
     * @param Mail $mail
     * @param DataPersistorInterface $dataPersistor
     * @param LoggerInterface $logger
     * @param TransportBuilder $transportBuilder
     * @param StateInterface $inlineTranslation
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        Config $quoteConfig,
        Mail $mail,
        DataPersistorInterface $dataPersistor,
        LoggerInterface $logger,
        TransportBuilder $transportBuilder,
        StateInterface $inlineTranslation,
        StoreManagerInterface $storeManager,
        Validator $formKeyValidator
    ) {
        parent::__construct($context, $quoteConfig);
        $this->context = $context;
        $this->mail = $mail;
        $this->mail->__construct($quoteConfig, $transportBuilder, $inlineTranslation, $storeManager);
        $this->dataPersistor = $dataPersistor;
        $this->logger = $logger;
        $this->quoteConfig = $quoteConfig;
        $this->_formKeyValidator = $formKeyValidator;
    }

    /**
     * Post user question
     *
     * @return Redirect
     */
    public function execute()
    {
        if (!$this->_formKeyValidator->validate($this->getRequest())){
            return $this->resultRedirectFactory->create()->setPath('*/*/');
        }
        
        if (!$this->isPostRequest()) {
            return $this->resultRedirectFactory->create()->setPath('*/*/');
        }
        try {
            $this->sendEmail($this->validatedParams());
            $this->messageManager->addSuccessMessage(
                __('Thanks for submitting the quote to us with your information. We\'ll respond to you very soon.')
            );
            $this->dataPersistor->clear('alpine_quote_form');
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $this->dataPersistor->set('alpine_quote_form', $this->getRequest()->getParams());
        } catch (\Exception $e) {
            $this->logger->critical($e);
            $this->messageManager->addErrorMessage(
                __('An error occurred while processing your form. Please try again later.')
            );
            $this->dataPersistor->set('alpine_quote_form', $this->getRequest()->getParams());
        }
        return $this->resultRedirectFactory->create()->setPath('quote/index');
    }

    /**
     * Send email
     *
     * @param array $post Post data from quote form
     * @return void
     */
    private function sendEmail($post)
    {
        if (key_exists('first_name', $post) && (key_exists('last_name', $post))) {
            $post['name'] = $post['first_name'] . ' ' . $post['last_name'];
        }
        $this->mail->send(
            $post['e_mail'],
            ['data' => new DataObject($post)]
        );
    }

    /**
     * Is post request ?
     *
     * @return bool
     */
    private function isPostRequest()
    {
        /** @var Request $request */
        $request = $this->getRequest();
        return !empty($request->getPostValue());
    }

    /**
     * Validate required parameters
     *
     * @return array
     * @throws \Exception
     */
    private function validatedParams()
    {
        $request = $this->getRequest();
        if (!$request->getParam('first_name')) {
            throw new LocalizedException(__('First name is missing'));
        }
        if (!$request->getParam('last_name')) {
            throw new LocalizedException(__('Last name is missing'));
        }
        if (false === \strpos($request->getParam('e_mail'), '@')) {
            throw new LocalizedException(__('Invalid email address'));
        }
        if (trim($request->getParam('hideit')) !== '') {
            throw new \Exception();
        }
        return $request->getParams();
    }
}
