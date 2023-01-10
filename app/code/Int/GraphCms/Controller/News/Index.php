<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Int\GraphCms\Controller\News;

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

        $news_arr = array();

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
        CURLOPT_POSTFIELDS =>"{\"query\":\"query MyQuery {\\n  newsrooms(stage:PUBLISHED) {\\n    id\\n    newsTitle\\n    newsSlug\\n    newsExcerpt\\n    newsDescription{\\n      html\\n    }\\n    newsImage{\\n      id\\n      url\\n    }\\n    newsDate\\n    newsroomCategories{\\n      id\\n      newsroomCategoryTitle\\n      newsroomCategorySlug\\n    }\\n  }\\n}\\n\",\"variables\":{}}",
        CURLOPT_HTTPHEADER => array(
            "Authorization: Bearer ".$authKey,
            "Content-Type: application/json"
        ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        $newsData = json_decode($response);
        $news = $newsData->data->newsrooms;
        //echo "<pre>";print_r($news);
        for($i=0;$i<count($news);$i++){
        $cat_arr = array();
        for($j=0;$j<count($news[$i]->newsroomCategories);$j++){
            $cat_arr[] = $news[$i]->newsroomCategories[$j]->id;
        }
        $news_arr[] = array(
            'id' => $news[$i]->id,
            'date'=> $news[$i]->newsDate,
            'title' => array('rendered' => $news[$i]->newsTitle ),
            'content' => array('rendered' => $news[$i]->newsDescription->html),
            'slug' => $news[$i]->newsSlug,
            'guid' => array('rendered' => $baseLinkUrl.'news/'.$news[$i]->newsSlug ),
            'link' => $baseLinkUrl.'news/'.$news[$i]->newsSlug,
            'excerpt' => array('rendered' => $news[$i]->newsExcerpt),
            'status' => "publish",
            'featured_media' => $news[$i]->newsImage->id,
            'categories' => $cat_arr,
        );
        }

        header('Content-type: Application/JSON');
        echo json_encode($news_arr, JSON_PRETTY_PRINT);
    }
}

