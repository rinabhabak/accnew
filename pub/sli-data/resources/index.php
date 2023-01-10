<?php
$resources_arr = array();
$xml = new DOMDocument("1.0");
$xml->formatOutput=true;
$parent = $xml->createElement("resources");
$xml->appendChild($parent);
$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => "https://api-us-west-2.graphcms.com/v2/ck7oq9rx21ugm01adezcmfrzf/master",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "POST",
  CURLOPT_POSTFIELDS =>"{\"query\":\"query MyQuery {\\n  pageses(where: {pageSlug_in: [\\\"products\\\",\\\"resources\\\",\\\"markets\\\",\\\"support\\\",\\\"company\\\",\\\"shop\\\"]}) {\\n    id\\n    pageTitle\\n    seoTitle\\n    seoDescription\\n    pageSlug\\n    seoImage {\\n      url\\n    }\\n    secKeywords\\n    pageses {\\n      id\\n      pageTitle\\n      seoTitle\\n      seoDescription\\n      pageSlug\\n      seoImage {\\n        url\\n      }\\n      secKeywords\\n    }\\n  }\\n}\\n\",\"variables\":{}}",
  CURLOPT_HTTPHEADER => array(
    "Authorization: Bearer eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCIsImtpZCI6ImdjbXMtbWFpbi1wcm9kdWN0aW9uIn0.eyJ2ZXJzaW9uIjozLCJpYXQiOjE1OTc3MzgwNDYsImF1ZCI6WyJodHRwczovL2FwaS11cy13ZXN0LTIuZ3JhcGhjbXMuY29tL3YyL2NrN29xOXJ4MjF1Z20wMWFkZXpjbWZyemYvbWFzdGVyIl0sImlzcyI6Imh0dHBzOi8vbWFuYWdlbWVudC5ncmFwaGNtcy5jb20vIiwic3ViIjoiZjIyM2EwZjUtNjU5ZS00NDUyLWE2MTgtMzNlNjk3MjMyMjVjIiwianRpIjoiY2tkem56YWxzNnhlaTAxdzhhdHkzOHpoaCJ9.LmBIGgPk3bee40DnesghBImMgPdrEMz5oTHEAimPrs--0UrrWMH81ZyzEWLafLIDfPidNo_0m7tLySYaip38IHlPqput7QY3XmNdT4ruB-8yigzI650uGW82H-WtPNXJ0HTME7CCZ4ZMBSZImUkEzmC8VQAi1TIS7OsqUIgxmGBhgr5jTmKTUnlFsQ0o1k65x_XdYrEFFir7hjjSXFk3td8vVw_OfFE_RHuJn642kerM4P5QPyalXNY5srNoNDw8vhzvW8THs2ZWubt2oiNCxhByTuz1qESmq-SrKKmtCP2SjE_KLDMHFCbdvXAFUbIFCk0yJo608MkjmR080tRiejkvZn9szuojFI98K_BredfPKcg_2ECmEzvVoaHC2wrb1xdt2x3FOMGw-CvtxJ1YuqmZ8PX0VuOT--xN-0PIjuZTBDGQLDTrfH5xioLjYvLE1u2TPYRoT0fWWU3ITq0aHvgR_WkFd8LzcUVPMxGG9P6IsDXszOIgLYvXpCRkSjrnHv3iQX2icBcZzDSQ0dUC8-F6hqElADF3h2E8_DYz_AVR35nFcwea7j3PvfCnG-mHv_JikX3AYd6pI8HJt8zWWZLd1BJDVwaWXERrIrTB6X-5t_CtKBkByqG-ncfqnOOhvP6HF6OhY7bBq_3ffNDfxg-SzJQyKxICPc6m7in7oII",
    "Content-Type: application/json",
    "Cookie: __cfduid=d8215e4189e480709ac39305795be9fa71598273266"
  ),
));

$response = curl_exec($curl);

curl_close($curl);

$resourcesData = json_decode($response);
$pages = $resourcesData->data->pageses;

for($i=0;$i<count($pages);$i++){
    if($pages[$i]->pageSlug !== "markets" && $pages[$i]->pageTitle !== null){
      $resources_arr[] = array(
        'id' => $pages[$i]->id,
        'title' => array('rendered' => $pages[$i]->pageTitle),
        'slug' => $pages[$i]->pageSlug,
        'guid' => array('rendered' => 'https://pwa-stage.accuride.com/en-us/'.$pages[$i]->pageSlug ),
        'link' => 'https://pwa-stage.accuride.com/en-us/'.$pages[$i]->pageSlug,
        'status' => "publish",
        'description' => $pages[$i]->seoDescription,
        'image' => $pages[$i]->seoImage->url,
        'keywords' => $pages[$i]->secKeywords,
      );
    }

    for($j=0;$j<count($pages[$i]->pageses);$j++){
      if($pages[$i]->pageses[$j]->pageSlug !== "news" && $pages[$i]->pageses[$j]->pageTitle !== null){
        $resources_arr[] = array(
          'id' => $pages[$i]->pageses[$j]->id,
          'title' => array('rendered' => $pages[$i]->pageses[$j]->pageTitle),
          'slug' => $pages[$i]->pageses[$j]->pageSlug,
          'guid' => array('rendered' => 'https://pwa-stage.accuride.com/en-us/'.$pages[$i]->pageSlug.'/'.$pages[$i]->pageses[$j]->pageSlug ),
          'link' => 'https://pwa-stage.accuride.com/en-us/'.$pages[$i]->pageSlug.'/'.$pages[$i]->pageses[$j]->pageSlug,
          'status' => "publish",
          'description' => $pages[$i]->pageses[$j]->seoDescription,
          'image' => $pages[$i]->seoImage->url,
          'keywords' => $pages[$i]->pageses[$j]->secKeywords,
        );
      }
    }
}
for($i=0;$i<count($resources_arr);$i++){
  $resourcesPages = $xml->createElement("resourcesPages");
  $parent->appendChild($resourcesPages);
  $resourcesPages->setAttribute('id',$resources_arr[$i]->id);

  $title = $xml->createElement("title");
  $title->nodeValue=$resources_arr[$i]->title;
  $resourcesPages->appendChild($title);

  $slug = $xml->createElement("slug");
  $slug->nodeValue=$resources_arr[$i]->slug;
  $resourcesPages->appendChild($slug);

  $guid = $xml->createElement("guid");
  $guid->nodeValue=$resources_arr[$i]->guid[0]->rendered;
  $resourcesPages->appendChild($guid);

  $link = $xml->createElement("link");
  $link->nodeValue=$resources_arr[$i]->link;
  $resourcesPages->appendChild($link);

  $status = $xml->createElement("status");
  $status->nodeValue=$resources_arr[$i]->status;
  $resourcesPages->appendChild($status);

  $description = $xml->createElement("description");
  $description->nodeValue=$resources_arr[$i]->description;
  $resourcesPages->appendChild($description);

  $image = $xml->createElement("image");
  $image->nodeValue=$resources_arr[$i]->image;
  $resourcesPages->appendChild($image);

  $keywords = $xml->createElement("keywords");
  $keywords->nodeValue=$resources_arr[$i]->keywords;
  $resourcesPages->appendChild($keywords);
}
header('Content-type: Application/JSON');
echo json_encode($resources_arr, JSON_PRETTY_PRINT);



