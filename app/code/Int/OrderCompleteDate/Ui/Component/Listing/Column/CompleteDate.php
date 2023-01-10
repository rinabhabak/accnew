<?php
namespace Int\OrderCompleteDate\Ui\Component\Listing\Column;

use Magento\Ui\Component\Listing\Columns\Column;
 
class CompleteDate extends Column
{
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {

            foreach ($dataSource['data']['items'] as &$item) {
                if(!in_array($item['status'], ['complete', 'closed'])){
                    $item['updated_at'] = '-';
                }
            }
        }
        return $dataSource;
    }
}