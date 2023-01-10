<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Int\GraphCms\Controller\BlogMedia;

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
        CURLOPT_POSTFIELDS =>"{\"query\":\"query MyQuery {\\n  blogs {\\n    id\\n    postTitle\\n    postSlug\\n    metaDescription\\n    categories {\\n      categorySlug\\n    }\\n    postImage {\\n      id\\n      fileName\\n      url\\n      createdAt\\n      updatedAt\\n      height\\n      width\\n      mimeType\\n    }\\n  }\\n}\\n\",\"variables\":{}}",
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
            $blogs_news_arr[] = array(
            'id' => $blogs[$i]->postImage->id,
            'date' => $blogs[$i]->postImage->createdAt,
            'date_gmt' => $blogs[$i]->postImage->createdAt,
            'guid' => array('rendered' => $blogs[$i]->postImage->url ),
            'modified' => $blogs[$i]->postImage->updatedAt,
            'modified_gmt' => $blogs[$i]->postImage->updatedAt,
            'slug' => $blogs[$i]->postSlug,
            'status' => "inherit",
            'type' => "attachment",
            'link' => $blogs[$i]->postImage->url,
            'title' => array('rendered' => $blogs[$i]->postTitle),
            'author' => 24,
            'comment_status' => "closed",
            'ping_status' => "closed",
            'template' => "",
            'meta' => "[]",
            'description' => array('rendered' => '<p class="attachment">
                <a data-rel="iLightbox[postimages]" data-title="" data-caption="" href="'.$blogs[$i]->postImage->url.'">
                    <img width="'.$blogs[$i]->postImage->width.'" height="'.$blogs[$i]->postImage->height.'" src="'.$blogs[$i]->postImage->url.'" class="" alt="'.$blogs[$i]->postTitle.'" srcset="'.$blogs[$i]->postImage->url.' 200w, '.$blogs[$i]->postImage->url.' 300w, '.$blogs[$i]->postImage->url.' 320w, '.$blogs[$i]->postImage->url.' 400w, '.$blogs[$i]->postImage->url.' 600w, '.$blogs[$i]->postImage->url.' 700w, '.$blogs[$i]->postImage->url.' 768w, '.$blogs[$i]->postImage->url.' 800w sizes="(max-width: 300px) 100vw, 300px" />
                </a>
                </p>'),
            'caption' => array('rendered' => $blogs[$i]->metaDescription),
            'alt_text' => $blogs[$i]->postTitle,
            'media_type' => "image",
            'mime_type' => $blogs[$i]->postImage->mimeType,
            );
        }
        header('Content-type: Application/JSON');
        echo json_encode($blogs_news_arr, JSON_PRETTY_PRINT);
    }
}

