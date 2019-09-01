=== Shortcodes for Yotpo ===
Contributors: hxii
Tags: yotpo, shortcode, shortcodes, yotpo add-on, yotpo shortcodes, shortcodes for yotpo
Requires at least: 4.6
Requires PHP: 5.6
Tested up to: 5.3
Stable tag: 1.1.6
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

This plugin adds the ability to use shortcodes to control the placement of Yotpo widgets.

== Description ==
= Prerequisites (Important) =
* The Yotpo plugin must be installed (obviously).
* WooCommerce 3.X and above (due to new methods).
This plugin allows using shortcodes to display Yotpo widgets inside and oustide (applicable widgets only) of product pages e.g. page builders, sidebars, widgets etc.

** For updates & usage instructions: [http://paulglushak.com/shortcodes-for-yotpo/](http://paulglushak.com/shortcodes-for-yotpo/) **
= Usage =
`
[yotpo_widget]
[yotpo_bottomline product_id="47" noempty]
[yotpo_product_gallery gallery_id="5bbb561f5ea79223a9da0e7c"]
[yotpo_product_reviews_carousel background_color="#22fff2" mode="most-recent" type="both" count="3"]
[yotpo_badge]
[yotpo_testimonials]
`
For the reviews carousel, get the attributes when generating the code from your Yotpo dashboard.
Special arguments exclusive to this plugin:
1. **yotpo_product_gallery** - adding `noproduct` will prevent a product ID being added if the gallery is not a product gallery.
2. **yotpo_product_reviews_carousel**:
adding `product_id="product-id-here"` will display a reviews carousel for the given product id.
adding `noproduct` will prevent a product id from being added in case you need a reviews carousel for all reviews.
3. **yotpo_widget** and **yotpo_bottomline** accept an optional `product_id` argument (e.g. `product_id="47") if you'd like to provide a product ID. Otherwise, the ID of the current product will be used.

== Installation ==
Installation is very simple:
1. Upload the plugin files to the `/wp-content/plugins/yotpo-shortcodes` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Use the shortcodes throughout your store.

== Frequently Asked Questions ==
= A shortcode is missing! / I need more shortcodes! =
Get in touch, and I'll see what can be done.

== Screenshots ==
1. Example usage of the shortcodes.
== Changelog ==
= 1.1.6 =
* Updated the code to be used with Yotpo Reviews for WooCommerce version 2.0 (https://github.com/hxii/YRFW).
* Checked with WC 3.7.0 and WP 5.3 (5.3-alpha-45923).
= 1.1.5 =
* Checked with WC 3.6.2 and WP 5.2
* Minor fixes and cleanup
= 1.1.4 =
* OOP Rewrite.
* Fixed requirement of Yotpo.
* Checked with Wordpress 5.2 alpha.
= 1.1.3 =
* Fixed issue with `[yotpo_bottomline]` when arguments are not given.
= 1.1.2 =
* Added `[yotpo_testimonials]` shortcode.
* Added empty template to `[yotpo_bottomline]` (when product has no reviews).
* Tested with latest versions of Wordpress and WooCommerce
= 1.1.1 =
* Added manual mode to reviews carousel.
* Checked with latest versions of Wordpress and WooCommerce.
= 1.1 =
* Added badge widget.
* Added optional `product_id` arguments to main widget and bottomline widget.
* Now returning HTML instead of echoing yotpo functions.
* Make sure to return nothing if post is not a product and no ID is supplied.
= 1.0.1 =
* Added `noproduct` arguments.
* Initial release on WP.
= 1.0 =
* Initial commit.
