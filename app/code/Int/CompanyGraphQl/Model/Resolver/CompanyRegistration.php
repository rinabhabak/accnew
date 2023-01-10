<?php
namespace Int\CompanyGraphQl\Model\Resolver;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;


class CompanyRegistration implements ResolverInterface
{
    private $_companyRegistration;

    public function __construct(
        \Int\CompanyGraphQl\Model\DataProvider\CompanyRegistration $companyRegistration
    ) {
        $this->_companyRegistration = $companyRegistration;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ){
        try {

            if (!isset($args['input']['company'][0]['company_name']) || empty($args['input']['company'][0]['company_name'])) {
                throw new GraphQlInputException(__('"company_name" value should be specified'));
            }

            if (!isset($args['input']['company'][0]['company_email']) || empty($args['input']['company'][0]['company_email'])) {
                throw new GraphQlInputException(__('"company_email" value should be specified'));
            }

            if (!isset($args['input']['company'][0]['street']) || empty($args['input']['company'][0]['street'])) {
                throw new GraphQlInputException(__('"street" value should be specified'));
            }

            if (!isset($args['input']['company'][0]['city']) || empty($args['input']['company'][0]['city'])) {
                throw new GraphQlInputException(__('"city" value should be specified'));
            }

            if (!isset($args['input']['company'][0]['country_id']) || empty($args['input']['company'][0]['country_id'])) {
                throw new GraphQlInputException(__('"country_id" value should be specified'));
            }

            if (!isset($args['input']['company'][0]['region']) || empty($args['input']['company'][0]['region'])) {
                throw new GraphQlInputException(__('"region" value should be specified'));
            }

            if (!isset($args['input']['company'][0]['postcode']) || empty($args['input']['company'][0]['postcode'])) {
                throw new GraphQlInputException(__('"postcode" value should be specified'));
            }

            if (!isset($args['input']['company'][0]['telephone']) || empty($args['input']['company'][0]['telephone'])) {
                throw new GraphQlInputException(__('"telephone" value should be specified'));
            }

            if (!isset($args['input']['customer_category']) || empty($args['input']['customer_category'])) {
                throw new GraphQlInputException(__('"customer_category" value should be specified'));
            }

            if (!isset($args['input']['customer_email']) || empty($args['input']['customer_email'])) {
                throw new GraphQlInputException(__('"customer_email" value should be specified'));
            }

            if (!isset($args['input']['firstname']) || empty($args['input']['firstname'])) {
                throw new GraphQlInputException(__('"firstname" value should be specified'));
            }

            if (!isset($args['input']['lastname']) || empty($args['input']['lastname'])) {
                throw new GraphQlInputException(__('"lastname" value should be specified'));
            }

            if (!isset($args['input']['pick_your_industry']) || empty($args['input']['pick_your_industry'])) {
                throw new GraphQlInputException(__('"pick_your_industry" value should be specified'));
            }

            $customerData = [
                "company" => [
                    "company_name" => $args['input']['company'][0]['company_name'],
                    "legal_name" => !empty($args['input']['company'][0]['legal_name']) ? $args['input']['company'][0]['legal_name']: '',
                    "company_email" => $args['input']['company'][0]['company_email'],
                    "vat_tax_id" => !empty($args['input']['company'][0]['vat_tax_id']) ? $args['input']['company'][0]['vat_tax_id']: '',
                    "reseller_id" => !empty($args['input']['company'][0]['reseller_id']) ? $args['input']['company'][0]['reseller_id']: '',
                    "street" => $args['input']['company'][0]['street'],
                    "city" => $args['input']['company'][0]['city'],
                    "country_id" => $args['input']['company'][0]['country_id'],
                    "region_id" => isset($args['input']['company'][0]['region']['region_id']) ? $args['input']['company'][0]['region']['region_id'] : '',
                    "region" => $args['input']['company'][0]['region'],
                    "postcode" => $args['input']['company'][0]['postcode'],
                    "telephone" => $args['input']['company'][0]['telephone'],
                    "customer_group_id" => 1
                ],
                "job_title" => !empty($args['input']['job_title']) ? $args['input']['job_title'] : '',
                "email" => $args['input']['customer_email'],
                "firstname" => $args['input']['firstname'],
                "lastname" => $args['input']['lastname'],
                "gender" => !empty($args['input']['gender']) ? $args['input']['gender'] : '',
                "customer_category" => $args['input']['customer_category'],
                "pick_your_industry" => $args['input']['pick_your_industry'],
                "other_industry" => !empty($args['input']['other_industry']) ? $args['input']['other_industry'] : ''
            ];

            $response = $this->_companyRegistration->register($customerData);
                  


        } catch (AuthenticationException $e) {

            $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/companyRegistration.log');
            $logger = new \Zend\Log\Logger();
            $logger->addWriter($writer);
            $logger->info($e);
        }
        return $response;

    }
}