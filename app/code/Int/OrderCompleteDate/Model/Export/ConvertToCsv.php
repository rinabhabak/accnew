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


class ConvertToCsv extends \Magento\Ui\Model\Export\ConvertToCsv
{
  


    /**
 * Returns CSV file
 *
 * @return array
 * @throws LocalizedException
 */
 
public function getCsvFile()
{
    $component = $this->filter->getComponent();


    $name = md5(microtime());
    $file = 'export/'. $component->getName() . $name . '.csv';

    $this->filter->prepareComponent($component);
    $this->filter->applySelectionOnTargetProvider();
    $dataProvider = $component->getContext()->getDataProvider();
    //exit(get_class($dataProvider));
    $fields = $this->metadataProvider->getFields($component);

    $options = $this->metadataProvider->getOptions();

    $this->directory->create('export');
    $stream = $this->directory->openFile($file, 'w+');
    $stream->lock();
    $stream->writeCsv($this->metadataProvider->getHeaders($component));
    $i = 1;
    $searchCriteria = $dataProvider->getSearchCriteria()
        ->setCurrentPage($i)
        ->setPageSize($this->pageSize);
    $totalCount = (int) $dataProvider->getSearchResult()->getTotalCount();

    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

    while ($totalCount > 0) {
        $items = $dataProvider->getSearchResult()->getItems();
        //  echo '<pre>'; print_r(get_class($dataProvider)); exit;
        foreach ($items as $item) {
            if($component->getName()=='sales_order_grid') {
                
                
                $completed_date = $item->getCompletedAt();

               

                $time_zone = $objectManager->create('Magento\Framework\Stdlib\DateTime\TimezoneInterface');

                $date = $time_zone->date(new \DateTime($completed_date));
               
                $formated_date = $date->format('M d, Y h:i:s A');

                $item->setCompletedAt($formated_date);
            }
            $this->metadataProvider->convertDate($item, $component->getName());
            $stream->writeCsv($this->metadataProvider->getRowData($item, $fields, $options));
        }
        $searchCriteria->setCurrentPage(++$i);
        $totalCount = $totalCount - $this->pageSize;
    }
    $stream->unlock();
    $stream->close();

    return [
        'type' => 'filename',
        'value' => $file,
        'rm' => true  // can delete file after use
    ];
}

}


