# Atwix_Richsnippets

## Settings and behavior 

### Breadcrumbs

If **Schema Type** = **Schema.org**. Custom template for core Breadcrumbs is used 
`view/frontend/templates/theme/html/breadcrumbs.phtml`. Breadcrumbs items are updated using Schema.org syntax. If 
**Custom Home Page Title** is set - first breadcrumb will be replaced with **Custom Home Page Title Text**.

If **Schema Type** = **RDF**. Custom template for core Breadcrumbs is used 
`view/frontend/templates/theme/html/breadcrumbs.phtml`. Breadcrumbs items are updated using RDF syntax. If 
**Custom Home Page Title** is set - first breadcrumb will be replaced with **Custom Home Page Title Text**.

If **Schema Type** = **JSON**. Custom template for core Breadcrumbs is used 
`view/frontend/templates/theme/html/breadcrumbs.phtml`. Breadcrumbs markup is not changed comparing to the core template.
`application/ld+json` script with Breadcrumbs JSON snippet is added to page source. 
Enabling **Custom Home Page Title** has no visual affect on frontend in Breadcrumbs block, but JSON snippet in page 
source is updated.


### Services and helpers
In order to keep compatibility across different versions of Magento custom serializer class is used: 
`Atwix\Richsnippets\Service\SerializerService`.