<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Int\GraphCms\Controller\Blog;

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
        $blog_arr = array();
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
        CURLOPT_POSTFIELDS =>"{\"query\":\"query MyQuery {\\n  blogs(stage: PUBLISHED) {\\n    id\\n    postTitle\\n    postSlug\\n    postExcerpt\\n    postDescription{\\n      html\\n    }\\n    postImage{\\n      id\\n      url\\n    }\\n    categories {\\n      id\\n      categoryTitle\\n      categorySlug\\n    }\\n  }\\n}\\n\",\"variables\":{}}",
        CURLOPT_HTTPHEADER => array(
            "Authorization: Bearer ".$authKey,
            "Content-Type: application/json"
        ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        $blogData = json_decode($response);
        $blogs = $blogData->data->blogs;
        for($i=0;$i<count($blogs);$i++){
        $cat_arr = array();
        for($j=0;$j<count($blogs[$i]->categories);$j++){
            $cat_arr[] = $blogs[$i]->categories[$j]->id;
        }
        $blog_arr[] = array(
            'id' => $blogs[$i]->id,
            'title' => array('rendered' => $blogs[$i]->postTitle ),
            'content' => array('rendered' => $blogs[$i]->postDescription->html),
            'slug' => $blogs[$i]->postSlug,
            'guid' => array('rendered' => $baseLinkUrl.'blog/'.$blogs[$i]->categories[0]->categorySlug.'/'.$blogs[$i]->postSlug ),
            'link' => $baseLinkUrl.$blogs[$i]->categories[0]->categorySlug.'/'.$blogs[$i]->postSlug,
            'excerpt' => array('rendered' => $blogs[$i]->postExcerpt),
            'status' => "publish",
            'featured_media' => $blogs[$i]->postImage->id,
            'categories' => $cat_arr,
        );
        }

        header('Content-type: Application/JSON');
        echo json_encode($blog_arr, JSON_PRETTY_PRINT);
    }
}

