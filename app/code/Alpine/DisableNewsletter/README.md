#Alpine_DisableNewsletter

This extension adds admin option to disable the built in newsletter 
functionality.  
It also removes the link to newswletter from account area 
permanently regardless of the setting


Backend setting:
Configuration -> Newsletter -> Subscription Options -> Newsletter enabled

## Possible Complications:
Magento has its own setting for disabling the newsletter, unclear why the extension creates a custom one, maybe it was done before the built in option was there.

Even though it seems like the extension should give ability to enable the newsletter it actually serves (as it's name yould imply) to only disable it, to enable again 
the extension would have to be refactored or removed