#Alpine_Acton

Extends VladimirPopov_WebForms

Adds new customer attribute `newsletter` which indicates if user is subscribed for newsletter or not
Provides backend config to set the newsletter form action url in checkout
Provides backend config  to set the form action url for all forms created with VladimirPopov_WebForms

Backend options can be set under:
Configuration -> Alpine -> Act-On

Possible issues:
The customer interface is extended with a plugin which adds the newsletter attribute values to the customer object,
this is done even when customer lists are loaded by looping through the list and getting the values for each entry.
This is highly inefficient, it is unlikely that this attribute should be loaded at all times and should probably be loaded only
when it is required