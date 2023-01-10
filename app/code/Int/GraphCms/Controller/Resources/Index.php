<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Int\GraphCms\Controller\Resources;

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

        $resources_arr = array();
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
        CURLOPT_POSTFIELDS =>"{\"query\":\"query MyQuery {\\n  pageses(where: {pageSlug_in: [\\\"products\\\",\\\"resources\\\",\\\"markets\\\",\\\"support\\\",\\\"company\\\",\\\"shop\\\"]}) {\\n    id\\n    pageTitle\\n    seoTitle\\n    seoDescription\\n    pageSlug\\n    seoImage {\\n      url\\n    }\\n    secKeywords\\n    pageses {\\n      id\\n      pageTitle\\n      seoTitle\\n      seoDescription\\n      pageSlug\\n      seoImage {\\n        url\\n      }\\n      secKeywords\\n    }\\n  }\\n}\\n\",\"variables\":{}}",
        CURLOPT_HTTPHEADER => array(
            "Authorization: Bearer ".$authKey,
            "Content-Type: application/json"
        ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        $resourcesData = json_decode($response);
        $pages = $resourcesData->data->pageses;

        if(count($pages) > 0)
        {
            foreach($pages as $page)
            {
                if($page->pageSlug !== "markets")
                {
                    $resources_arr[] = [
                        'id' => $page->id,
                        'title' => array('rendered' => $page->pageTitle),
                        'slug' => $page->pageSlug,
                        'guid' => array('rendered' => $baseLinkUrl.$page->pageSlug ),
                        'link' => $baseLinkUrl.$page->pageSlug,
                        'status' => "publish",
                        'description' => $page->seoDescription,
                        'image' => !empty($page->seoImage->url) ? $page->seoImage->url : '',
                        'keywords' => $page->secKeywords
                    ];
                }
                
                if(count($page->pageses) > 0)
                {
                    foreach($page->pageses as $pagese)
                    {
                        if($pagese->pageSlug !== "news")
                        {
                            $resources_arr[] = [
                                'id' => $pagese->id,
                                'title' => array('rendered' => $pagese->pageTitle),
                                'slug' => $pagese->pageSlug,
                                'guid' => array('rendered' => $baseLinkUrl.$page->pageSlug.'/'.$pagese->pageSlug ),
                                'link' => $baseLinkUrl.$page->pageSlug.'/'.$pagese->pageSlug,
                                'status' => "publish",
                                'description' => $pagese->seoDescription,
                                'image' => !empty($page->seoImage->url) ? $page->seoImage->url : '',
                                'keywords' => $pagese->secKeywords
                            ];
                        }
                    } 
                }
            }
        }

        header('Content-type: Application/JSON');
        echo json_encode($resources_arr, JSON_PRETTY_PRINT);
    }
}

