wp-e-commerce ezprints


A fork of the wp-e-commerce WordPress plugin to integrate the ezp Builder API from ezpservices.com for printing and drop shipping.  Original plugin at http://www.instinct.co.nz/e-commerce/



Features
========
 - Organize each product by image, with each type of printing as a variation.
 - Assign different prices and hires images for each product variation.
 - Assign an ezprints sku for each variation.
 - Fetch shipping quotes from ezprints.



Installation
============
Install as you would any wp plugin.



Upgrade
=======
To upgrade from a previous wp-e-commerce version, replace your current wp-e-commerce directory with this one.  Go to Manage Plugins in your WP admin screen, click deactivate and then activate.



Setup
=====

In the WP admin screen, under Products > Settings > Shipping, enter your ezprints Partner ID by clicking 'edit' next to the ezprints External Shipping Calculator.

Variations
Each variation is an ezprints product.  Create variation sets with all the ezprints skus you'd like to offer.

Products
Each product is one of your images.  Assign a variation set to the product.  You can assign a single hires image to be used for all variations or override the default for any variation.