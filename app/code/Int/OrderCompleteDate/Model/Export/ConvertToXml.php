<?php
/*
 * Rewritten Export Model Convert To CSV class to include the custom field added to the grid
 * 
 *  
 * @author Jerome Dennis <haijerome@gmail.com>
 * 
 */
namespace Int\OrderCompleteDate\Model\Export;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;


class ConvertToXml extends \Magento\Ui\Model\Export\ConvertToXml
{
  

    /**
     * @param string $componentName
     * @param array $items
     * @return void
     */
    protected function prepareItems($componentName, array $items = [])
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        foreach ($items as $document) {

            if($componentName =='sales_order_grid') {
                   
                $completed_date = $document->getCompletedAt();
                $time_zone = $objectManager->create('Magento\Framework\Stdlib\DateTime\TimezoneInterface');
                $date = $time_zone->date(new \DateTime($completed_date));  
                $formated_date = $date->format('M d, Y h:i:s A');
                $document->setCompletedAt($formated_date);
            }
            $this->metadataProvider->convertDate($document, $componentName);
        }
    }

}


