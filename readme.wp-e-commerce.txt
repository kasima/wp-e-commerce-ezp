=== WP e-Commerce ===
Contributors: Dan Milward, Tom Howard, Jeffry Ghazally, Chris Beavan, Allen Han
Donate link: http://www.instinct.co.nz/e-commerce
Tags: e-commerce, shop, cart, dropshop, ajax, web2.0, paypal, authorize, exchange, stock control, ecommerce 
Requires at least: 2.7
Tested up to: 2.9
Stable tag: 3.7.5.3

WP e-Commerce is a Web 2.0 application designed with usability, aesthetics, and presentation in mind. 

== Description ==

The WP e-Commerce shopping cart plugin for WordPress is an elegant easy to use fully featured shopping cart application suitable for selling your products, services, and or fees online.

WP e-Commerce is a Web 2.0 application designed with usability, aesthetics, and presentation in mind. 

Perfect for:

* Bands & Record Labels
* Clothing Companies
* Crafters & Artists
* Books, DVDs & MP3 files 

== Installation ==

1. Upload the folder 'wp-e-commerce' to the '/wp-content/plugins/' directory
2. Activate the plugin through the 'Plugins' menu in WordPress

= Updating =

Simply copy the new files across and replace the old files saving saving ones you've modified. If you have product images uploaded then do not overwrite the product_images folder or images folder. If you have downloadable products for sale do not overwrite the files folder.

When updating it is important that you do not overwrite the entire images folder. Instead you should copy over the contents of the new images folder into the existing images folder on your server - saving all the exiting product images you may have already uploaded.

If you experience database errors try de-activating and re-activating your plugin. 


== Changelog == 

= 3.7.5.3 =
* Support for WordPress 2.9 canonical URLs for Products and Categories

= 3.7.5.2 =
* More Fixes to the Paypal Pro merchant file
* Image thumbnail size fixes
* Updated readme to mark plugin as working with 2.9
* Purchase log filtering bug fixed
* Fix for a bug when no shipping module is used where the shipping country and region were not being changed
* Remove button on checkout page now clears stock claims

= 3.7.5.1 =
* Fixes to the Paypal Pro merchant file
* Fixes to the Paypal Express Checkout merchant file
* Tracking email improvements
* HTML in descriptions does not break RSS (thanks to http://www.leewillis.co.uk)
* Category permalinks will now be regenerated properly on instalation
* Category list bug preventing viewing a product when viewing a category fixed.


= 3.7.5 =
* Added code for upgrades/additions from nielo.info and lsdev.biz,  we will be using this for new modules in the future.
* All In One SEO Pack compatibility bugfixes and improvements.
* CSV has had some work done on it, it now takes larger files, and associates a CSV file to a single category of your choice. We'd love to be able to allow users to add the categories and images as part of the CSV file. We will look into it more at a later date.
* SSL we fixed the image issue from beta1 and used James Collis recommended fix (using is_ssl() for our conditions) Thanks James!
* ‘Show list of Product Groups’ shows all Groups <- there may be some backwards compatibility issues (we havent encountered any but nevertheless if you spot any let us know)
* When duplicating products, their tags do not get duplicated for the new product. <- Oh yes they DO!
* Google Checkout now sends off Discount Coupons As well. And we fixed the `name` vs `code` Issue people mentioned in the forum
* Category shortcode backwards compatibility
* Fix Purchlogs - We had a lot of users that somehow by passed the 'fix purchase logs' page when upgrading from 3.6, so we added some better conditions to the mix and added it on to the debug page (a powerful wp-e-commerce page that is hidden from most users as it's usage is very corrosive backing up your DB and files is strongly recommended if not necessary when you work with this page).
* Valid XHTML for front end of wpec YAY!
* Fixed adding variations when adding products
* Sender from the 'resend email to buyer' link on the purchase log details page has been fixed
* Shipping Discount Bug that stopped shipping working at all.
* Categories Widget has had numerous changes –
* Better MU support. 
* Canadian Tax – Fixes
* US Tax –Fixes
* Product Categories Caching Issue  Resolved
* Coupons – ‘Apply to all Products’ and numerous bug fixes
* ‘Your Account’  done some fixes to it.
* ‘Accepted Payment’ goes straight to ‘Closed Order’
* Stock claims are now cleared when the cart is emptied
* Purchase log bulk actions now work
* PayPal gateway module fixes and improvements
* HTML Tables can now be added to product descriptions
* Flat Rate and Weight Rate improvements


= 3.7.4 =  
* Changes to shipping to fix the bugs from 3.7.3 with shipping and the new shipping_discount feature
* Fixes for variations under grid view


== Frequently Asked Questions ==

= Where do I find DropShop and Grid View =

From the BlogShop of course http://www.instinct.co.nz/shop/

= How do I customize WP e-Commerce =

First of all you should check out the presentation settings which are in the Shop Options page.

Advanced users can edit the CSS (and do just about anything). Not so advanced users can contact Instinct and either purchase professional support.

== Screenshots ==

We're taking this one step further. 

Check out our flickr guides online
www.flickr.com/photos/danmilward/sets/72157594425079473/detail/

== Readme Validator ==

This readme was made using:
wordpress.org/extend/plugins/about/validator/

Totally totally cool tool!!
