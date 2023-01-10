<?php
namespace Custom\Csvmail\Controller\Index;

use Magento\Framework\App\RequestInterface;


class Index extends \Magento\Framework\App\Action\Action
{
     
    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $_transportBuilder;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
 
    public function __construct(
        \Magento\Framework\App\Action\Context $context
        , \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
        , \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Filesystem\DirectoryList $directory_list
    )
    {
        $this->_transportBuilder = $transportBuilder;
        $this->_storeManager = $storeManager;
        $this->directory_list = $directory_list; 
        parent::__construct($context);
    }
 
    public function execute()
    {

        $var = $this->directory_list->getPath('var');

$file = $var.'/my-email.csv';


        $csv = array();
$file = fopen($file, 'r');

while (($result = fgetcsv($file)) !== false)
{
    $csv[] = $result;
}


        try {
            $store = $this->_storeManager->getStore()->getId();
            $send_success_logger = new \Zend\Log\Logger();
            $send_success = new \Zend\Log\Writer\Stream($var.'/log/send_success.log');
            $send_success_logger->addWriter($send_success);

        foreach($csv as $eachrow)
{
        $transport = $this->_transportBuilder->setTemplateIdentifier(78)
            ->setTemplateOptions(['area' => 'frontend', 'store' => $store])
            ->setTemplateVars(
                [
                    'store' => $this->_storeManager->getStore(),
                    'username' => $eachrow[1]
                ]
            )
            ->setFrom('general')
            // you can config general email address in Store -> Configuration -> General -> Store Email Addresses
            ->addTo($eachrow[2], $eachrow[1])
            ->getTransport();
        $transport->sendMessage();
        //file_put_contents('var/email-logs.txt', $eachrow[2]. "\n", FILE_APPEND);
        $send_success_logger->info($eachrow[2]);
    }

     } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\MailException(new \Magento\Framework\Phrase($e->getMessage()), $e);
            $send_failure_logger = new \Zend\Log\Logger();
            $send_failure = new \Zend\Log\Writer\Stream($var.'/log/send_failure.log');
            $send_failure_logger->addWriter($send_failure);
            $send_failure_logger->info($eachrow[2]);
        }
}
}