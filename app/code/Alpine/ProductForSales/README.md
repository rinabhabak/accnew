#Alpine_ProductForSales

This extension adds several modifycation for products on sales:
  * sets the `product_is_saleable_and_visible` check to also check if product is "for sales"
  * disables the `isSaleable` check in the product model
  * Overrides the final price box to return empty string instead of price for  products `for sales`