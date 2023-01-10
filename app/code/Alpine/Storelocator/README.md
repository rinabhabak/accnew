#Alpine_Storelocator

Extends Amasty_Storelocator to modify and extend the functionality of the locator

###Backend Settings:
Configuration -> Alpine -> Store Locator -> No results message

###Modifications:
* Adds new column `fax` to the `amasty_amlocator_location` table
* Updates the `industry` attribute store labels
* Creates `amlocator-description` static block displayed on amlocator page
* Modifies the store locator edit form in admin, making state field hidden and 
the rest of the address fields optional
* Overrides the location block to add method for getting a "no results" notice which can be edited in admin. 
The rendering of the message is handled by overridden template in the accuride theme
* It also extends the original location block to alter the `getAttributes` method in order to handle the custom `industry` attribute
* Overrides the ajax controller to append the industry attribute to the returned data
* Alters the `applyAttributeFilters` method in the location collection with a plugin to not apply industry filter when the `default` value `All` is selected
* Changes the template for the `amasty.locator.center` block in layout, the html is heavily refactored in the new template to achieve the 
desired look and functionality of the locator
* Adds css to the amlocator page to style the selects
* Overrides the select.js code to modify the functionality and append a second select component (selectTwo)
* Overrides the main.js code to modify the functionality (full extend not analyzed)
* Overrides the distributor.js view to save product data in a cookie on click over a distributor, purpose for saving the cookie is unknown
