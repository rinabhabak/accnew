# Alpine_Catalog

This extension adds extensive modifications to the catalog list, search list and product view pages

### Modifications:

  * On product view pages when product urls have a get parameter `b` with value `true` the theme is changed from `accuride` to `accuridemodifications`, this is achieved using an observer set on `catalog_controller_product_view`.
  * The listProduct block (for catalog and search results) is overridden to split product collection on two collections, available online and other; this is done based on the `product_for_sales` attribute (Available Online). There are also methods for requesting json encoded array with calculated prices for all products and all children of configurable products
  * The ImageBuilder block is overridden to ensure that configurable products show configuration images when `checkout/cart/configurable_product_image` is set so
  * The `getResizedImageInfo` method in the Catalog Image helper is extended with a plugin to catch and log any exceptions and allow execution to proceed
  * The `getAssociatedProductCollectionGrouped` method in the Grouped product model is extended to make sure `thumbnail` is added to field list
  * The final price render block for grouped products is overridden to provide methods for checking if price range should be displayed, obtaining price range, min amount and maximum amount
  * The final price render block template for grouped products is replaced to take advantage of the additional methods and display price range
  * New block `Alpine\Catalog\Block\Product\Tabs\Info` is provided to render `Product Features` and `Application` tabs in the product info detail block on product view pages; the main difference from core renderers is that it check if the attribute value displayed in the is html or serialized data to unserialize and set as json type in case od serialized data.
  * The js component `Magento_ConfigurableProduct/js/configurable` is extended to populate select menu options for configurable products.
  * The `accuridemodifications` theme restyles the catalog, in particular it modifies image sizes and appearance in the image gallery; It also reshapes the related products list into `Optional Kits` list
  