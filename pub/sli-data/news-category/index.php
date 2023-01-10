<?php
$cat_arr = array();

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
  CURLOPT_POSTFIELDS =>"{\"query\":\"query MyQuery {\\n  newsroomCategories(stage: PUBLISHED) {\\n    id\\n    newsroomCategoryTitle\\n    newsroomCategorySlug\\n    newsrooms {\\n      id\\n    }\\n  }\\n}\\n\",\"variables\":{}}",
  CURLOPT_HTTPHEADER => array(
    "Authorization: Bearer eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCIsImtpZCI6ImdjbXMtbWFpbi1wcm9kdWN0aW9uIn0.eyJ2ZXJzaW9uIjozLCJpYXQiOjE1OTc3MzgwNDYsImF1ZCI6WyJodHRwczovL2FwaS11cy13ZXN0LTIuZ3JhcGhjbXMuY29tL3YyL2NrN29xOXJ4MjF1Z20wMWFkZXpjbWZyemYvbWFzdGVyIl0sImlzcyI6Imh0dHBzOi8vbWFuYWdlbWVudC5ncmFwaGNtcy5jb20vIiwic3ViIjoiZjIyM2EwZjUtNjU5ZS00NDUyLWE2MTgtMzNlNjk3MjMyMjVjIiwianRpIjoiY2tkem56YWxzNnhlaTAxdzhhdHkzOHpoaCJ9.LmBIGgPk3bee40DnesghBImMgPdrEMz5oTHEAimPrs--0UrrWMH81ZyzEWLafLIDfPidNo_0m7tLySYaip38IHlPqput7QY3XmNdT4ruB-8yigzI650uGW82H-WtPNXJ0HTME7CCZ4ZMBSZImUkEzmC8VQAi1TIS7OsqUIgxmGBhgr5jTmKTUnlFsQ0o1k65x_XdYrEFFir7hjjSXFk3td8vVw_OfFE_RHuJn642kerM4P5QPyalXNY5srNoNDw8vhzvW8THs2ZWubt2oiNCxhByTuz1qESmq-SrKKmtCP2SjE_KLDMHFCbdvXAFUbIFCk0yJo608MkjmR080tRiejkvZn9szuojFI98K_BredfPKcg_2ECmEzvVoaHC2wrb1xdt2x3FOMGw-CvtxJ1YuqmZ8PX0VuOT--xN-0PIjuZTBDGQLDTrfH5xioLjYvLE1u2TPYRoT0fWWU3ITq0aHvgR_WkFd8LzcUVPMxGG9P6IsDXszOIgLYvXpCRkSjrnHv3iQX2icBcZzDSQ0dUC8-F6hqElADF3h2E8_DYz_AVR35nFcwea7j3PvfCnG-mHv_JikX3AYd6pI8HJt8zWWZLd1BJDVwaWXERrIrTB6X-5t_CtKBkByqG-ncfqnOOhvP6HF6OhY7bBq_3ffNDfxg-SzJQyKxICPc6m7in7oII",
    "Content-Type: application/json",
    "Cookie: __cfduid=d8215e4189e480709ac39305795be9fa71598273266"
  ),
));

$response = curl_exec($curl);
$news_cat = json_decode($response);

curl_close($curl);

$categories = $news_cat->data->newsroomCategories;
for($i=0;$i<=count($categories);$i++){
  if($categories[$i]->id !== null){
    $cat_arr[] = array(
      'id' => $categories[$i]->id,
      'count'=> count($categories[$i]->newsrooms),
      'name' => $categories[$i]->newsroomCategoryTitle,
      'slug' => $categories[$i]->newsroomCategorySlug,
      'link' => 'https://pwa-stage.accuride.com/en-us/news/category/'.$categories[$i]->newsroomCategorySlug,
      'taxonomy' => "category",
    );
  }
}

header('Content-type: Application/JSON');
echo json_encode($cat_arr, JSON_PRETTY_PRINT);  