=== Metizpay Payment Gateway For WooCommerce ===
Contributors: (this should be a list of wordpress.org userid's)
Donate link: https://metizpay.com/
Tags: woocommerce metizpay, metizpay, payment gateway, woocommerce, woocommerce payment gateway, credit cards, refunds, capture. metizpay woocommerce
Requires at least: 5.9.1
Tested up to: 5.9.1
Requires PHP: 7.1
WC requires at least:5.8.0
WC tested up to:5.8.0
Stable tag: 1.0.1
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Metizpay Payment Gateway For WooCommerce extends the functionality of WooCommerce to accept payments from credit/debit cards using Metizpay Gateway

== Description ==

<h3>Metizpay Payment Gateway for WooCommerce</h3> makes your website ready to use Metizpay payment gateway to accept credit/debit cards on your woocommerce store in safe way. Metizpay HMAC-SHA512 Compatible version.



Metizpay is most widely used payment gateway to process payments online and accepts Visa, MasterCard, Discover and other variants of cards.

= Features =
Few features of this plugin:

1. No SSL required
2. No PCI required
3. Easy to install and configure
4. Option to configure success & failure message
5. Safe way to process credit cards and debit cards on WooCommerce using metizpay SIM
6. This plugin use hosted solution provided by Metizpay and payment is processed on secured servers of Metizpay



**Contact our plugin support and quick solutions at plugin support for [WooCommerce Metizpay Plugin](https://metizpay.com/)


== Installation ==

Easy steps to install the plugin:

1. Upload `metizpay-payment-gateway-for-woocommerce` folder/directory to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Go to WooCommerce => Settings
4. On the "Settings" page, select "Payment Gateways" tab.
5. Under "Payment Gateways" you will find all the available gateways, select "Metizpay" option
6. On this page you will find option to configure the plugin for use with WooCommerce
7. Enter the Configuration details (tid, encryptionKey, mid, responseUrl, transactionType, recurringPeriod, recurringDay, noOfRecurring)
8. Configurable elements:

Title: This will appear on checkout page as name for this payment gateway

Description: This will appear on checkout page as description for this payment gateway

tid: TID provided to merchant.

encryptionKey: encryption key provided to the Merchant.

mid: MID assigned to the merchant.

responseUrl: Response URL to be given by Merchant, where the response will be posted.

transactionType: TransactionType provided to the Merchant (Default- S).

recurringPeriod: RecurringPeriod provided to the Merchant (Default- NA).

recurringDay: RecurringDay provided to the Merchant (Default- 0).

noOfRecurring: NoOfRecurring provided to the Merchant (Default- 0).

Transaction Success Message: This message will appear upon successful transaction. You can customize this message as per your need.

Transaction Failed Message: This message will appear when transaction will get failed/declined at payment gateway.

API Mode: This option sets the mode of API. Test/Sandbox Mode is when you need to test the gateway using some test transations. Live Mode to be used to process real transaction and card will actually get charged for this.

== Frequently Asked Questions ==
= Is SSL Required to use this plugin? =
SSL is not required

== Screenshots ==

1. Activate Plugin.
2. Go to WooCommerce Settings.
3. Select Payment Gateways tab.
4. Select Metizpay option.
5. Enable Metizpay option from this page.
6. Enter the details.
7. Set gateway mode.
8. Sample screenshot from checkout page


== Upgrade Notice ==

= 1.0 =
Upgrade notices describe the reason a user should upgrade.  No more than 300 characters.

= 0.5 =
This version fixes a security related bug.  Upgrade immediately.


== Changelog ==
= 1.0 =
* Initial release.



