<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Int\GraphCms\Controller\BlogCategory;

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
        CURLOPT_POSTFIELDS =>"{\"query\":\"{\\n  categories(stage: PUBLISHED){\\n    id\\n    categoryTitle\\n    categorySlug\\n    parentCategory{\\n      id\\n      categoryTitle\\n    }\\n    categories{\\n      id\\n      categoryTitle\\n      categorySlug\\n    }\\n    blogs{\\n        id\\n    }\\n  }\\n}\",\"variables\":{}}",
        CURLOPT_HTTPHEADER => array(
            "Authorization: Bearer ".$authKey,
            "Content-Type: application/json"
        ),
        ));

        $response = curl_exec($curl);
        $blog_cat = json_decode($response);

        curl_close($curl);

        $categories = $blog_cat->data->categories;

        for($i=0;$i<=count($categories);$i++){
            if (array_key_exists($i,$categories)){
                if(!empty($categories[$i]->categories)){
                    $parent_id = $categories[$i]->categories[0]->id;
                    $link = $baseLinkUrl.'blog/category/'.$categories[$i]->categories[0]->categorySlug.'/'.$categories[$i]->categorySlug;
                }
                else{
                    $parent_id = 0;
                    $link = $baseLinkUrl.'blog/category/'.$categories[$i]->categorySlug;
                }
                if($categories[$i]->id !== null){
                    $cat_arr[] = array(
                    'id' => $categories[$i]->id,
                    'count'=> count($categories[$i]->blogs),
                    'name' => $categories[$i]->categoryTitle,
                    'slug' => $categories[$i]->categorySlug,
                    'parent'=> $parent_id,
                    'link' => $link,
                    'taxonomy' => "category",
                    );
                }
            }
        }

        header('Content-type: Application/JSON');
        echo json_encode($cat_arr, JSON_PRETTY_PRINT);
    }
}

