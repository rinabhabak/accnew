#Alpine_ConfigurableChildVisibility

This extension makes sure out of stock configurable products are 
shown when magento's default setting for showing out of stock products 
is enabled. There is no change in behaviour for other product types.

### Backend Setting (magento default):
Configuration -> Catalog -> Inventory -> Display Out of Stock Products

## Possible Complications:
Seems like this extension is changing the default behavior, where this core magento setting works for all products expected configurable products and they include those too.  
It holds dead code (Alpine/ConfigurableChildVisibility/Pricing/Render/_FinalPriceBox.php)