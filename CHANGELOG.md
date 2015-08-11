# Changelog

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