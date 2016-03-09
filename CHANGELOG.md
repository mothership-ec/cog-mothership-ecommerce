# Changelog

## 3.7.3

- Resolve issue where `PaymentGatewayRecordLoader` attempts to throw `\GatewayNotFoundException` from global namespace instead of its own namespace
- Fix typo in `GatewayNotFoundException` where it extends `\LogicExceptiion` instead of `\LogicException`

## 3.7.2

- Fix issue where editing an order address would throw an error trying to load a class that no longer exists, now loads `Form\AddressForm`
- Do not use master branch of Mockery for unit tests, use 0.9 instead

## 3.7.1

- Order creation will use the default payment gateway if it is not set on the session

## 3.7.0

- Payment gateways are saved against payments
- Added `Payment\PaymentGatewayRecordEdit` class for saving payment gateway information for payments
- Added `Payment\PaymentGatewayRecordLoader` class for loading payment gateway for payments from database
- Added `Payment\GatewayNotFoundException` exception class to be thrown if a payment gateway cannot be found when loading from database
- Added `payment.gateway.edit` service which returns instance of `Payment\PaymentGatewayRecordEdit`
- Added `payment.gateway.loader` service which returns instance of `Payment\PaymentGatewayRecordLoader`
- Added migration to create `payment_gateway` table
- Added `EventListener\OrderListener::sendOrderRefundController` method for setting the controller to use for cancelled orders
- Added `EventListener\OrderListener::sendItemRefundController` method for setting the controller to use for cancelled items
- Deprecated `gateway` service, use `gateways` and select appropriate gateway instead
- Increased `cog-mothership-commerce` dependency to 5.17

## 3.6.1

- Redirect users to start of checkout if they log out during the 'Addresses' stage, instead of attempting to render the form and erroring.

## 3.6.0

- Amended `Pickup` stage of fulfillment to have one form to cover all dispatch methods
- Added `Form\Fulfillment\Pickup` form to represent form of final `Pickup` stage of fulfillment
- Added `form.fulfillment.pickup` service which returns instance of `Form\Fulfillment\Pickup`
- Deprecated `Form\Pickup` form class, use `Form\Fulfillment\Pickup` instead
- Deprecated `form.pickup` service, use `form.fulfillment.pickup` service instead

## 3.5.1

- Resolve issue where registered payment gateways in the `gateways` service would be sorted alphabetically

## 3.5.0

- Fire events during checkout to allow for bespoke functionality during checkout process
- Added `Message\Mothership\Ecommerce\Checkout` namespace for classes relating directly to checkout
- Added `Checkout\Event` class. This is an event which takes an instance of `Message\Mothership\Commerce\Order\Order` as well as an array of data (i.e. form data)
- Added `Checkout\Events` class of constants:
    - `Events::REVIEW` (`ecom.checkout.review`)
    - `Events::ADDRESSES` (`ecom.checkout.address`)
    - `Events::CONFIRM` (`ecom.checkout.confirm`)
- `Controller\Checkout\Checkout::process()` controller fires `Checkout\Events::REVIEW` event when the form to change quantities has been submitted
- `Controller\Checkout\Details::addresses()` controller fires `Checkout\Events::ADDRESSES` event when the user submits their address data and these addresses have been added to the order
- `Controller\Checkout\Confirm::processContinue()` controller fires `Checkout\Events::CONFIRM` event (via `Controller\Checkout\Confirm::_processConfirmData` private method) when the user has confirmed the order is ready for payment and submitted any notes
- Removed unnecessary `use` statements from `Controller\Checkout\Details`
- Added `Checkout\EventTest` unit test for testing `Checkout\Event` class

## 3.4.0

- Added functionality for Mothership to work with multiple payment gateways
- `payment.yml` `gateway` option can now be set as an array
- Added `Form\CheckoutConfirmForm` form class to replace that returned by `Controller/Checkout/Confirm::continueForm()` for checkout confirmation stage
- `Message:Mothership:Ecommerce::checkout:stage-2-confirm` given `confirmForm` variable which is the form generated from `Form\CheckoutConfirmForm`
- `Message:Mothership:Ecommerce::checkout:stage-2-confirm` given `gateways` variable which are the the registered payment gateways
- Added `checkout.form.confirm` service which returns instance of `Form\CheckoutConfirmForm`
- Added `gateways` service which returns all payment gateways registered in the config file
- `gateway` service returns first payment gateway registered in config file
- Deprecated `Controller/Checkout/Confirm::continueForm()` method. The `Controller/Checkout/Confirm::index()` and `Controller/Checkout/Confirm::processContinue()` methods still process this deprecated form for backwards compatibility reasons, since chances are the Mothership installation will be using a view override for this checkout stage. In order to use the multiple payment gateway feature, developers will be required to ensure that the checkout confirmation view uses the `confirmForm` variable and not the `continueForm` variable
- Refactored `Gateway\Collection` to extend `Message\Cog\ValueObject\Collection`
- Added translations to display on checkout confirmation stage
- Updated Cog dependency to 4.10
- Implemented Travis continuous integration

## 3.3.1

- Resolve issue where option names and values were not showing up properly on **Pages** tab

## 3.3.0

- Added **Pages** tab to product overview, which allows users to view and create new product pages from the product screen in the admin panel
- Added `ProductAdminListener` event listener to inject **Pages** tab into product tab menu
- Added `Form\Product\ProductPageCreateSingle` form class for creating product pages based on the product and its options
- Added `Controller\ProductPage\PageList` controller for displaying the product pages under the **Pages** tab of the product screen
- Added `Controller\ProductPage\Create` controller for creating product pages submitted via the product create form
- Added `ProductPage\Create:setListingPageType()` method to set the page type for product listing pages
- Added `ProductPage\Create::allowDuplicates()` method to allow for multiple identical product pages to be created (defaults to false)
- Added `ProductPage\Exists::includeDeleted()` method to include deleted pages when checking if a product page already exists
- Added `ProductPage\Exists::includeUnpublished()` method to include unpublished pages when checking if a product page already exists
- `ProductPage\Create::__construct()` now accepts `null` for `$listingPageType` parameter
- Increase `cog-mothership-commerce` dependency to 5.14

## 3.2.1

- Resolve issue where `ProductPageListener::saveProductUnitRecords()` fails if there is no field for product options on product pages

## 3.2.0

- Added `ProductPage\UnitRecord\Edit` class for saving records of which units are assigned to which page to the database
- Added `ProductPageListener::saveProductUnitRecords()` event listener, which listens to page content edits and creation and works out which product units are assigned to that page, and saves records to the database
- Added `product_page_unit_record` table which keeps track of which units are assigned to which page
- Added migration to port units into `product_page_unit_record` table
- Altered `Filter\SaleFilter` to make use of the product page unit records instead of using a complicated query to work out which unit pages to display
- Deprecated `Filter\SaleFilter::setOptionField()` as it is no longer necessary
- Improved validation when registering via checkout, no longer catches all exceptions and assumes this is because the email address is already in use
- Set `cog-mothership-cms` requirement to 4.7

## 3.1.2

- Tracking code is optional when processing an order in fulfillment
- Set `cog-mothership-commerce` requirement to 5.10

## 3.1.1

- Totals view in checkout uses difference between total gross price and total discounted price to determine tax instead of the total base price
- Set `cog-mothership-commerce` requirement to 5.8

## 3.1.0

- Added `Filter\SaleFilter` class for filtering product pages that are in the sale using the `Filtering` Cog/CMS component
- Updated `cog-mothership-commerce` requirement to 5.6

## 3.0.5

- All view references in fulfillment use fully qualified view names, prevents problems with subrequests in view overrides breaking the parser's ability to resolve short-hand view names

## 3.0.4

- Picking slips on exchange orders no longer display items that have already been dispatched
- `Fulfillment\Process` controller uses fully qualified view names

## 3.0.3

- Remove default payment logger
- Forgotten password link no longer links to admin password screen

## 3.0.2

- Fix issue where shipping method wouldn't update after changing address
- Listen for `UpdateFailedEvent` and redirect users to home page if caught
- Updated `cog-mothership-commerce` requirement to 5.3

## 3.0.1

- Fixed issues with automatic shipping selection when provided with multiple shipping options

## 3.0.0

- Initial open source release