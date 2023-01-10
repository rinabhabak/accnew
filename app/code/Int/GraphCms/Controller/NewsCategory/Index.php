<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Int\GraphCms\Controller\NewsCategory;

class Index extends \Magento\Framework\App\Action\Action
{

    protected $resultPageFactory;

    protected $helper;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Action\Context  $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Int\GraphCms\Helper\Data $helper
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->helper = $helper;
        parent::__construct($context);
    }

    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $baseLinkUrl = $this->helper->getBaseLinkUrl();
        $endPoint = $this->helper->getEndPoint();
        $authKey = $this->helper->getAuthKey();

        $cat_arr = array();

        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => $endPoint,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS =>"{\"query\":\"query MyQuery {\\n  newsroomCategories(stage: PUBLISHED) {\\n    id\\n    newsroomCategoryTitle\\n    newsroomCategorySlug\\n    newsrooms {\\n      id\\n    }\\n  }\\n}\\n\",\"variables\":{}}",
        CURLOPT_HTTPHEADER => array(
            "Authorization: Bearer ".$authKey,
            "Content-Type: application/json"
        ),
        ));

        $response = curl_exec($curl);
        $news_cat = json_decode($response);

        curl_close($curl);

        $categories = $news_cat->data->newsroomCategories;

        if(count($categories) > 0)
        {
            foreach($categories as $category)
            {
                if(!$category->id){
                    continue;
                }

                $cat_arr[] = [
                    'id' => $category->id,
                    'count'=> count($category->newsrooms),
                    'name' => $category->newsroomCategoryTitle,
                    'slug' => $category->newsroomCategorySlug,
                    'link' => $baseLinkUrl.'news/category/'.$category->newsroomCategorySlug,
                    'taxonomy' => "category"
                ];
            }
        }

        header('Content-type: Application/JSON');
        echo json_encode($cat_arr, JSON_PRETTY_PRINT);  
    }
}
