# Magento 2 Better Maintenance GraphQL/PWA

**Magento 2 Better Maintenance GraphQL is now a part of the Mageplaza Better Maintenance extension that adds GraphQL features. It supports PWA compatibility.**

[Mageplaza Better Maintenance for Magento 2](https://www.mageplaza.com/magento-2-better-maintenance/) helps online stores notify customers about the website’s maintenance or up-gradation situation in a friendly way. 

The extension supports the coming soon page and maintenance page. When you are going to launch a new product or service, the coming soon page will be necessary to let customers know about the launch and create buzz around the new products. When your store has any update or mistakes to be fixed, redirecting customers to a maintenance page will effectively avoid causing frustration in customers. 

A visual countdown timer is included in the coming soon and maintenance pages, showing the remaining time for the maintenance to finish or new products to be introduced. The extension supports five clock styles, including Simple, Circle, Square, Stack, and Modern. These clocks are designed to blend well with the page with eye-catching and modern styles. The store admin can also customize the clock to suit the store theme by changing the background color or clock number types. Customers will not feel bored and leave your store right away when being redirected to these pages.

On each page, there will be a subscription box. Customers who are concerned about the upcoming content can fill in their email address to get notifications when released. A “contact us” section with different social media buttons will be another quick option for customers to contact the store for more information.

Magento 2 Better Maintenance enables store admins to customize their coming soon and maintenance pages with ease. All the components of a page can be changed on the fly with a simple configuration from the backend. The page layout, title, description, and visual elements like images or videos can be selected and added to the page without difficulty. Notably, there will be a progress bar on the maintenance page that shows the complete status of the maintenance process. 

Besides, Better Maintenance for Magento 2 supports a whitelist that contains people you don’t want to be redirected to these two pages, such as admins or special customers. In particular, IP addresses in the whitelist can still access the store without being redirected to coming soon or maintenance pages. You can also add specific pages to the whitelist to enable customers to reach these pages without any redirection.


## 1. How to install

Run the following command in Magento 2 root folder:

```
composer require mageplaza/module-better-maintenance-graphql
php bin/magento setup:upgrade
php bin/magento setup:static-content:deploy
```

**Note:**
Magento 2 Better Maintenance GraphQL requires installing [Mageplaza Better Maintenance](https://github.com/mageplaza/magento-2-better-maintenance) in your Magento installation.

## 2. How to use

To perform GraphQL queries in Magento, please do the following requirements:

- Use Magento 2.3.x or higher. Set your site to [developer mode](https://www.mageplaza.com/devdocs/enable-disable-developer-mode-magento-2.html).
- Set GraphQL endpoint as `http://<magento2-server>/graphql` in url box, click **Set endpoint**. 
(e.g. `http://dev.site.com/graphql`)
- To view the queries that the **Mageplaza Better Maintenance GraphQL** extension supports, you can look in `Docs > Query` in the right corner

## 3. Devdocs

- [Magento 2 Better Maintenance API & examples](https://documenter.getpostman.com/view/10589000/TVYJ7Hep)
- [Magento 2 Better Maintenance GraphQL & examples](https://documenter.getpostman.com/view/10589000/TVYJ7Heq)

## 4. Contribute to this module

Feel free to **Fork** and contribute to this module and create a pull request so we will merge your changes main branch.

## 5. Get Support

- Feel free to [contact us](https://www.mageplaza.com/contact.html) if you have any further questions.
- If you kike this project, please give us a **Star** ![star](https://i.imgur.com/S8e0ctO.png)
