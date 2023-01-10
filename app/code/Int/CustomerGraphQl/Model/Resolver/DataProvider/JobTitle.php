<?php
namespace Int\CustomerGraphQl\Model\Resolver\DataProvider;

use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\Exception\NoSuchEntityException;

class JobTitle
{
 
   

    private $objectmanager;



    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectmanager
    )
    {

        $this->objectmanager = $objectmanager;
       
    }
    
    
    /**
     * Get banner data
     * @return array
     * @throws GraphQlNoSuchEntityException
     */
    public function updateCustomerJobTitle($args)
    {

       /* echo json_encode($args['job_title']) ;
        die();*/
        $message = [];

        try {

            $customerId = $args['customer_id'];

            $job_title = $args['job_title'];

            $job_title_model =  $this->objectmanager->create('Magento\Company\Model\Customer');
            $job_title_model->setCustomerId($customerId);
            $job_title_model->setJobTitle($job_title);
            $job_title_model->save();


        $message['message'] = "Thank you! your account has been updated successfully";
                
        } catch (\Exception $e) { 
            $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/CustomerUpdate.log');
            $logger = new \Zend\Log\Logger();
            $logger->addWriter($writer);
            $logger->info($e);
        }
            
        return $message;
    }


     public function getFormKey()
    {
        return $this->_formKey->getFormKey();
    }
}
