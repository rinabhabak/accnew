# Mage2 Module Int Attributes

    ``int/module-attributes``

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
 - Enable the module by running `php bin/magento module:enable Int_Attributes`
 - Apply database updates by running `php bin/magento setup:upgrade`\*
 - Flush the cache by running `php bin/magento cache:flush`

### Type 2: Composer

 - Make the module available in a composer repository for example:
    - private repository `repo.magento.com`
    - public repository `packagist.org`
    - public github repository as vcs
 - Add the composer repository to the configuration by running `composer config repositories.repo.magento.com composer https://repo.magento.com/`
 - Install the module composer by running `composer require int/module-attributes`
 - enable the module by running `php bin/magento module:enable Int_Attributes`
 - apply database updates by running `php bin/magento setup:upgrade`\*
 - Flush the cache by running `php bin/magento cache:flush`


## Configuration




## Specifications




## Attributes

 - Product - Drawer Opening Width Over25 (drawer_opening_width_over25)

 - Product - Sliding Door Opening Height Over60 (sliding_door_opening_height_ov)

 - Product - Hinge Door Opening Height Over48 (hinge_door_opening_height_over)

 - Product - Feature (feature)

 - Product - Slide Length (slide_length)

