#Alpine_Reviews

This extension adds backend configurable limit for reviews to be displayed per page with a "show more link"

Backend setting:
Configuration -> Alpine -> PDP Review -> Review Settings -> Limit

##Possible issues:

Functionality is achieved in a very inefficient way, first it overrides the reviews pager to unset the limit, to force all reviews
to be rendered on the page, then javascript is used to show the first items up to the set limit only using javascript. When "show more"
link is used another batch of reviews up the set limit is made visible, this process repeats until all reviews are visible.

Better way to achieve this will be to set the page size to the configured limit and render only those reviews on the page, then 
provide an api endpoint which can be called with ajax to load and render more reviews when "show more" is clicked

Another issue is that design portion of the module is placed in the Accuride theme instead of having together with the extension
