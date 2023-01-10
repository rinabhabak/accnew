<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Int\GraphCms\Controller\NewsMedia;

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

        $blogs_news_arr = array();
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
        CURLOPT_POSTFIELDS =>"{\"query\":\"query MyQuery {\\n  newsrooms {\\n    id\\n    newsTitle\\n    newsSlug\\n    metaDescription\\n    newsroomCategories {\\n      newsroomCategorySlug\\n    }\\n    newsImage {\\n      id\\n      fileName\\n      url\\n      createdAt\\n      updatedAt\\n      height\\n      width\\n      mimeType\\n    }\\n  }\\n}\\n\",\"variables\":{}}",
        CURLOPT_HTTPHEADER => array(
            "Authorization: Bearer ".$authKey,
            "Content-Type: application/json"
        ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        $newsData = json_decode($response);

        $newsroom_arr = $newsData->data->newsrooms;
        for($i=0;$i<count($newsroom_arr);$i++){
            $blogs_news_arr[] = array(
            'id' => $newsroom_arr[$i]->newsImage->id,
            'date' => $newsroom_arr[$i]->newsImage->createdAt,
            'date_gmt' => $newsroom_arr[$i]->newsImage->createdAt,
            'guid' => array('rendered' => $newsroom_arr[$i]->newsImage->url ),
            'modified' => $newsroom_arr[$i]->newsImage->updatedAt,
            'modified_gmt' => $newsroom_arr[$i]->newsImage->updatedAt,
            'slug' => $newsroom_arr[$i]->newsSlug,
            'status' => "inherit",
            'type' => "attachment",
            'link' => $newsroom_arr[$i]->newsImage->url,
            'title' => array('rendered' => $newsroom_arr[$i]->newsTitle),
            'author' => 24,
            'comment_status' => "closed",
            'ping_status' => "closed",
            'template' => "",
            'meta' => "[]",
            'description' => array('rendered' => '<p class="attachment">
                <a data-rel="iLightbox[postimages]" data-title="" data-caption="" href="'.$newsroom_arr[$i]->newsImage->url.'">
                    <img width="'.$newsroom_arr[$i]->newsImage->width.'" height="'.$newsroom_arr[$i]->newsImage->height.'" src="'.$newsroom_arr[$i]->newsImage->url.'" class="" alt="'.$newsroom_arr[$i]->newsTitle.'" srcset="'.$newsroom_arr[$i]->newsImage->url.' 200w, '.$newsroom_arr[$i]->newsImage->url.' 300w, '.$newsroom_arr[$i]->newsImage->url.' 320w, '.$newsroom_arr[$i]->newsImage->url.' 400w, '.$newsroom_arr[$i]->newsImage->url.' 600w, '.$newsroom_arr[$i]->newsImage->url.' 700w, '.$newsroom_arr[$i]->newsImage->url.' 768w, '.$newsroom_arr[$i]->newsImage->url.' 800w sizes="(max-width: 300px) 100vw, 300px" />
                </a>
                </p>'),
            'caption' => array('rendered' => $newsroom_arr[$i]->metaDescription),
            'alt_text' => $newsroom_arr[$i]->newsTitle,
            'media_type' => "image",
            'mime_type' => $newsroom_arr[$i]->newsImage->mimeType,
            );
        }
        header('Content-type: Application/JSON');
        echo json_encode($blogs_news_arr, JSON_PRETTY_PRINT);
    }
}

