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
  CURLOPT_POSTFIELDS =>"{\"query\":\"query MyQuery {\\n  blogs {\\n    id\\n    postTitle\\n    postSlug\\n    metaDescription\\n    categories {\\n      categorySlug\\n    }\\n    postImage {\\n      id\\n      fileName\\n      url\\n      createdAt\\n      updatedAt\\n      height\\n      width\\n      mimeType\\n    }\\n  }\\n}\\n\",\"variables\":{}}",
  CURLOPT_HTTPHEADER => array(
    "Authorization: Bearer eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCIsImtpZCI6ImdjbXMtbWFpbi1wcm9kdWN0aW9uIn0.eyJ2ZXJzaW9uIjozLCJpYXQiOjE1OTc3MzgwNDYsImF1ZCI6WyJodHRwczovL2FwaS11cy13ZXN0LTIuZ3JhcGhjbXMuY29tL3YyL2NrN29xOXJ4MjF1Z20wMWFkZXpjbWZyemYvbWFzdGVyIl0sImlzcyI6Imh0dHBzOi8vbWFuYWdlbWVudC5ncmFwaGNtcy5jb20vIiwic3ViIjoiZjIyM2EwZjUtNjU5ZS00NDUyLWE2MTgtMzNlNjk3MjMyMjVjIiwianRpIjoiY2tkem56YWxzNnhlaTAxdzhhdHkzOHpoaCJ9.LmBIGgPk3bee40DnesghBImMgPdrEMz5oTHEAimPrs--0UrrWMH81ZyzEWLafLIDfPidNo_0m7tLySYaip38IHlPqput7QY3XmNdT4ruB-8yigzI650uGW82H-WtPNXJ0HTME7CCZ4ZMBSZImUkEzmC8VQAi1TIS7OsqUIgxmGBhgr5jTmKTUnlFsQ0o1k65x_XdYrEFFir7hjjSXFk3td8vVw_OfFE_RHuJn642kerM4P5QPyalXNY5srNoNDw8vhzvW8THs2ZWubt2oiNCxhByTuz1qESmq-SrKKmtCP2SjE_KLDMHFCbdvXAFUbIFCk0yJo608MkjmR080tRiejkvZn9szuojFI98K_BredfPKcg_2ECmEzvVoaHC2wrb1xdt2x3FOMGw-CvtxJ1YuqmZ8PX0VuOT--xN-0PIjuZTBDGQLDTrfH5xioLjYvLE1u2TPYRoT0fWWU3ITq0aHvgR_WkFd8LzcUVPMxGG9P6IsDXszOIgLYvXpCRkSjrnHv3iQX2icBcZzDSQ0dUC8-F6hqElADF3h2E8_DYz_AVR35nFcwea7j3PvfCnG-mHv_JikX3AYd6pI8HJt8zWWZLd1BJDVwaWXERrIrTB6X-5t_CtKBkByqG-ncfqnOOhvP6HF6OhY7bBq_3ffNDfxg-SzJQyKxICPc6m7in7oII",
    "Content-Type: application/json",
    "Cookie: __cfduid=d8215e4189e480709ac39305795be9fa71598273266"
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
?>

