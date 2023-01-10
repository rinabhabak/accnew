<?php
$blogs_news_arr = array();
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
  CURLOPT_POSTFIELDS =>"{\"query\":\"query MyQuery {\\n  newsrooms {\\n    id\\n    newsTitle\\n    newsSlug\\n    metaDescription\\n    newsroomCategories {\\n      newsroomCategorySlug\\n    }\\n    newsImage {\\n      id\\n      fileName\\n      url\\n      createdAt\\n      updatedAt\\n      height\\n      width\\n      mimeType\\n    }\\n  }\\n}\\n\",\"variables\":{}}",
  CURLOPT_HTTPHEADER => array(
    "Authorization: Bearer eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCIsImtpZCI6ImdjbXMtbWFpbi1wcm9kdWN0aW9uIn0.eyJ2ZXJzaW9uIjozLCJpYXQiOjE1OTc3MzgwNDYsImF1ZCI6WyJodHRwczovL2FwaS11cy13ZXN0LTIuZ3JhcGhjbXMuY29tL3YyL2NrN29xOXJ4MjF1Z20wMWFkZXpjbWZyemYvbWFzdGVyIl0sImlzcyI6Imh0dHBzOi8vbWFuYWdlbWVudC5ncmFwaGNtcy5jb20vIiwic3ViIjoiZjIyM2EwZjUtNjU5ZS00NDUyLWE2MTgtMzNlNjk3MjMyMjVjIiwianRpIjoiY2tkem56YWxzNnhlaTAxdzhhdHkzOHpoaCJ9.LmBIGgPk3bee40DnesghBImMgPdrEMz5oTHEAimPrs--0UrrWMH81ZyzEWLafLIDfPidNo_0m7tLySYaip38IHlPqput7QY3XmNdT4ruB-8yigzI650uGW82H-WtPNXJ0HTME7CCZ4ZMBSZImUkEzmC8VQAi1TIS7OsqUIgxmGBhgr5jTmKTUnlFsQ0o1k65x_XdYrEFFir7hjjSXFk3td8vVw_OfFE_RHuJn642kerM4P5QPyalXNY5srNoNDw8vhzvW8THs2ZWubt2oiNCxhByTuz1qESmq-SrKKmtCP2SjE_KLDMHFCbdvXAFUbIFCk0yJo608MkjmR080tRiejkvZn9szuojFI98K_BredfPKcg_2ECmEzvVoaHC2wrb1xdt2x3FOMGw-CvtxJ1YuqmZ8PX0VuOT--xN-0PIjuZTBDGQLDTrfH5xioLjYvLE1u2TPYRoT0fWWU3ITq0aHvgR_WkFd8LzcUVPMxGG9P6IsDXszOIgLYvXpCRkSjrnHv3iQX2icBcZzDSQ0dUC8-F6hqElADF3h2E8_DYz_AVR35nFcwea7j3PvfCnG-mHv_JikX3AYd6pI8HJt8zWWZLd1BJDVwaWXERrIrTB6X-5t_CtKBkByqG-ncfqnOOhvP6HF6OhY7bBq_3ffNDfxg-SzJQyKxICPc6m7in7oII",
    "Content-Type: application/json",
    "Cookie: __cfduid=d8215e4189e480709ac39305795be9fa71598273266"
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
?>

