# Changelog

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