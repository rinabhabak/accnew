# Alpine_Vwo

This extension adds Visual Website Optimizer (VWO, visualwebsiteoptimizer.com) to the shop

### How it works:
There is a template with WVO js code snippet which is added to all pages via layout xml.  
There is a second template added to checkout which uses a helper class to get the last order data and push the subtotal as revenue amount to VWO

### Notes
There is no configuration in admin or xml, all the settings are hardcoded directly in the template rendering the vwo js snippet