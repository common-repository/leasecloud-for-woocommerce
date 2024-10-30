=== LeaseCloud for WooCommerce ===
Contributors: leasecloud, krokedil, NiklasHogefjord, spathon
Tags: ecommerce, e-commerce, woocommerce, leasecloud, checkout
Requires at least: 4.7
Tested up to: 4.8.2
Requires PHP: 5.6
WC requires at least: 3.0
WC tested up to: 3.4.7
Stable tag: trunk
Requires WooCommerce at least: 3.0
Tested WooCommerce up to: 3.1.2
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html


== DESCRIPTION ==
LeaseCloud for WooCommerce is a plugin that extends WooCommerce, making it easy for merchants to easily offer their B2B customers leasing as a payment option in the checkout.

= Get started =
To get started with LeaseCloud you need to [sign up](https://www.leasecloud.se/) for an account.

More information on how to get started can be found in the [plugin documentation](http://docs.krokedil.com/se/).

= Demo site with LeaseCloud in action =
If you want to see the plugin in action you can visit [permanad.se](https://www.permanad.se).


== INSTALLATION	 ==
1. Download the latest release zip file or install it directly via the plugins menu in WordPress Administration.
2. If you use the WordPress plugin uploader to install this plugin skip to step 4.
3. Unzip and upload the entire plugin directory to your /wp-content/plugins/ directory.
4. Activate the plugin through the 'Plugins' menu in WordPress Administration.
5. Go WooCommerce Settings --> Payment Gateways and configure your LeaseCloud settings.
6. Read more about the configuration process in the [plugin documentation](http://docs.krokedil.com/se/).


== Frequently Asked Questions ==
= Which countries does this payment gateway support? =
At the moment it's only available for merchants in Sweden.

= Where can I find LeaseCloud for WooCommerce documentation? =
For help setting up and configuring LeaseCloud for WooCommerce please refer to our [documentation](http://docs.krokedil.com/se/).



== CHANGELOG ==
= 2019.04.11    - version 1.1.9 =
* Fix           - Remove CRN (organisation number) validation in plugin. We leave this to the LeaseCloud service for better reliability.

= 2018.11.26    - version 1.1.8 =
* Update        - Add option to make calls to test server
* Fix           - Min sum to be based on total excluding vat

= 2018.11.07    - version 1.1.7 =
* Fix           - Fixed monthly cost in checkout with shipping.

= 2018.09.06    - version 1.1.6 =
* Fix           - Fixed vat calculation.

= 2018.07.20    - version 1.1.5 =
* Fix           - Changed how we handle the page reload after selecting display payment length.

= 2018.07.04    - version 1.1.4 =
* Fix           - Fixed error where widget would not save session info for customers not logged in and without a cart. Switched to Cookie instead.
* Fix           - Fixed a display error due to settings

= 2018.03.05    - version 1.1.3 =
* Update        - Changed translation on Monthly Payment
* Fix           - Check if product is a variable product.
* Fix           - Fix total amount
* Fix           - Improved price calculation.

= 2018.02.13    - version 1.1.2 =
* Update        - Updated SDK

= 2017.11.14    - version 1.1.1 =
* Enhancement   - Updated error handling
* Enhancement   - Updated Webhook signature response.

= 2017.11.14    - version 1.1.0=
* Enhancement   - Changed how we handle statuses for LeaseCloud orders.
* Enhancement   - Changed settings. Added "Leaseing only" setting.
* Enhancement   - Changed some price displays.
* Enhancement   - Added a check on settings save to check for valid credentials.

= 2017.10.25    - version 1.0.4=
* Fix           - Fixed 500 error with some settings.
* Fix           - Some formatting errors for the price displays.
* Fix           - Fixed calculations for the order data total amounts.
* Enhancement   - Updated SDK.
* Enhancement   - Added support for test purchases with LeaseCloud.


= 2017.10.23    - version 1.0.3=
* Fix           - Updates to the display price per month.
* Fix           - Fixed so Swedish translation is used.


= 2017.10.19    - version 1.0.2=
* Fix           - Updated the order status to work with API update
* Enhancement   - Added global setting for default product type for leasable products.
* Enhancement   - Added column to admin product list for leasable status.
* Enhancement   - Added swedish translation.

= 2017.10.16    - version 1.0.1=
* Fix           - Added product type for variable products.
* Fix           - Added monthly cost calculations to all checkout fields.

= 2017.10.11    - version 1.0.0 =
* First release on wordpress.org.
