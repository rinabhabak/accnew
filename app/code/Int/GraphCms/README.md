# Mage2 Module Int GraphCms

    ``int/module-graphcms``

 - [Main Functionalities](#markdown-header-main-functionalities)
 - [Installation](#markdown-header-installation)
 - [Configuration](#markdown-header-configuration)
 - [Specifications](#markdown-header-specifications)
 - [Attributes](#markdown-header-attributes)


## Main Functionalities


## Installation
\* = in production please use the `--keep-generated` option

### Type 1: Zip file

 - Unzip the zip file in `app/code/Int`
 - Enable the module by running `php bin/magento module:enable Int_GraphCms`
 - Apply database updates by running `php bin/magento setup:upgrade`\*
 - Flush the cache by running `php bin/magento cache:flush`

### Type 2: Composer

 - Make the module available in a composer repository for example:
    - private repository `repo.magento.com`
    - public repository `packagist.org`
    - public github repository as vcs
 - Add the composer repository to the configuration by running `composer config repositories.repo.magento.com composer https://repo.magento.com/`
 - Install the module composer by running `composer require int/module-graphcms`
 - enable the module by running `php bin/magento module:enable Int_GraphCms`
 - apply database updates by running `php bin/magento setup:upgrade`\*
 - Flush the cache by running `php bin/magento cache:flush`


## Configuration

 - endpoint (graphcms/general/endpoint)

 - authkey (graphcms/general/authkey)

 - cookie (graphcms/general/cookie)


## Specifications

 - Controller
	- frontend > sli_data/blog/index

 - Controller
	- frontend > sli_data/blog_category/index

 - Controller
	- frontend > sli_data/blog_media/index

 - Controller
	- frontend > sli_data/news/index

 - Controller
	- frontend > sli_data/news_category/index

 - Controller
	- frontend > sli_data/news_media/index

 - Controller
	- frontend > sli_data/resources/index


## Attributes



