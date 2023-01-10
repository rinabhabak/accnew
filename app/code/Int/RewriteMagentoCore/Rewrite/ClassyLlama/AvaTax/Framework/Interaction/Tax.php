<?php

namespace Int\RewriteMagentoCore\Rewrite\ClassyLlama\AvaTax\Framework\Interaction;

use ClassyLlama\AvaTax\Framework\Interaction\MetaData\ValidationException;
use Magento\Framework\Exception\LocalizedException;

class Tax extends \ClassyLlama\AvaTax\Framework\Interaction\Tax
{
    /**
     * Creates and returns a populated request object for a quote
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Tax\Api\Data\QuoteDetailsInterface $taxQuoteDetails
     * @param \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment
     * @return null|\Magento\Framework\DataObject
     * @throws LocalizedException
     */
    public function getTaxRequestForQuote(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Tax\Api\Data\QuoteDetailsInterface $taxQuoteDetails,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment
    ) {
        $request = $this->convertTaxQuoteDetailsToRequest($taxQuoteDetails, $shippingAssignment, $quote);

        if (is_null($request)) {
            return null;
        }

        $store = $quote->getStore();
        $shippingAddress = $shippingAssignment->getShipping()->getAddress();
        $this->addGetTaxRequestFields($request, $store, $shippingAddress, $quote->getCustomerId());

        /**
         *  Adding importer of record override
         */
        if ($quote->getCustomerId() !== null) {
            $customer = $this->getCustomerById($quote->getCustomerId());

            if($customer !== null) {
                $this->setIsImporterOfRecord($customer, $request);
            }
        }

        try {
            $validatedData = $this->metaDataObject->validateData($request->getData());
            $request->setData($validatedData);
        } catch (ValidationException $e) {
            $this->avaTaxLogger->error('Error validating data: ' . $e->getMessage());
        }

        return $request;
    }
}