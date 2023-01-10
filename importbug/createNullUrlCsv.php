<?php
class createNullUrlCsv
    extends \Magento\Framework\App\Http
    implements \Magento\Framework\AppInterface {
    public function launch()
    {
        $this->_state->setAreaCode('frontend'); //Set area code 'frontend' or 'adminhtml
        $id = 1828;
        $_product = $this->_objectManager->create('\Magento\Catalog\Model\Product')->load($id);

        echo $_product->getName();

        return $this->_response;
    }

    public function catchException(\Magento\Framework\App\Bootstrap $bootstrap, \Exception $exception)
    {
        return false;
    }

}