# Joomla HikaShop plugin for Paylike [![Build Status](https://travis-ci.org/paylike/plugin-hikashop.svg?branch=master)](https://travis-ci.org/paylike/plugin-hikashop)

This plugin is *not* developed or maintained by Paylike but kindly made
available by a user.

Released under the GPL V3 license: https://opensource.org/licenses/GPL-3.0

## Supported HikaShop versions

 [![Last succesfull test](https://log.derikon.ro/api/v1/log/read?tag=hikashop&view=svg&label=Hikashop&key=ecommerce&background=34b5ca)](https://log.derikon.ro/api/v1/log/read?tag=hikashop&view=html)

* The plugin has been tested with most versions of HikaShop at every iteration. We recommend using the latest version of HikaShop, but if that is not possible for some reason, test the plugin with your HikaShop version and it would probably function properly. 
 

## Installation

1.Once you have installed HikaShop on your Joomla setup, follow these simple steps:
  Signup at (paylike.io) [https://paylike.io] (it’s free)
  
  1. Create a live account
  1. Create an app key for your Joomla website
  1. Upload the ```paylike.zip``` and ```paylike_status.zip``` trough the Joomla Admin
  1. Activate both plugins through the 'Extensions' screen in Joomla.
  1. Under HikaShop payment methods create a new payment method and select Hikashop `Paylike Payment Plugin`.
  1. Insert the app key and your public key in the settings for the Paylike payment gateway you just created
  

## Updating settings

Under the Hikashop Paylike payment method settings, you can:
 * Update the payment method text in the payment gateways list
 * Update the payment method description in the payment gateways list
 * Update the title that shows up in the payment popup 
 * Add test/live keys
 * Set payment mode (test/live)
 * Change the capture type (Instant/Delayed)
 
 ## How to
 
 1. Capture
 * In Instant mode, the orders are captured automatically
 * In delayed mode you can capture an order by moving the order to the shipped status. 
 2. Refund
   * To refund an order move the order into refunded status.
 3. Void
   * To void an order you can move the order into refunded status. If its not captured it will get voided otherwise it will get refunded. 
