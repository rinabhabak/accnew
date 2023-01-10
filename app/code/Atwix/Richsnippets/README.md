# Rich Snippets for Magento 2 

Rich Snippets is one of the most valuable extensions when it comes to improving site’s SEO, increasing organic traffic and click-through rate in search results. Implementing Rich Snippets will show your potential customers additional information about your products with enhanced search results, increasing the outreach of your brand to your audience.

Rich Snippets extension is essential for any store looking to:

- Increase of the click-through rate in Google and Yahoo search
- Improve organic traffic
- Display website breadcrumbs in Google, Bing and Yahoo search results
- Display lowest price, average rating, availability in Google search results
- Adjust website name in Google search results
- Enable correct social sharing of site's web pages
- Include product rating when sharing pages on social networks

## Features Overview:

- Structured data for breadcrumbs by schema.org, RDF or JSON
- Structured data for category pages by schema.org, RDF or JSON
- Additional website name meta tags
- Configurable Open Graph meta tags
- Additional Open Graph snippet - Product Rating
- Configurable Twitter Cards meta tags
- Social meta tags for Facebook, LinkedIn, Pinterest, Instagram, Twitter, Google Plus

## Installation
1. Create directory `app/code/Atwix/Richsnippets/`
2. Upload contents of the Rich Snippets installation package to the `app/code/Atwix/
Richsnippets/` directory.
3. Flush cache storage
4. From your Magento root directory run the following two commands one by one:
```bash
./bin/magento module:enable Atwix_Richsnippets
./bin/magento setup:upgrade
```
4. Log out from admin panel.
5. Log in again

## Release Notes
#### Version 1.2.0
Updated 13 March, 2018 

- Added compatibility fixes for Magento 2.2.*
- Minor fixes and improvements

*Compatibility:* 2.0.x, 2.1, 2.2.*

#### Version 1.1.8
Updated 11 August, 2017 

- Fixed product canonical url generation that was affected by module 

*Compatibility:* 2.0.x, 2.1

#### Version 1.1.7
Updated 09 August, 2016 

- Minor cosmetic fixes

*Compatibility:* 2.0.x, 2.1

#### Version 1.1.6
Updated 11 July, 2016 

- Added Search Box rich snippet
- Added ability to override product title rich snippet value
- Added ability to override product description rich snippet value 
- Fixed issues for PHP 7.0
- If product description is empty, the `og:description` value will be taken from the short description
- Fixed OG image issues on category pages
- Fixed product rating summary on the product list page
- Fixed prices issues on category pages
- Fixed issues on empty categories

*Compatibility:* 2.0.x, 2.1

#### Version 1.0.0
Updated 04 April, 2016 

- Stable Release

*Compatibility:* 2.0
