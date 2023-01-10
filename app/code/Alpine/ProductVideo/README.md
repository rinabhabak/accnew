#Alpine_ProductVideo
Adds three new product attributes for assigning up to three product videos:
 * video1_url
 * video2_url
 * video3_url
 
The videos are then assigned to the product gallery by overriding:
```Magento\Catalog\Block\Product\View\Gallery:getGalleryImagesJson```