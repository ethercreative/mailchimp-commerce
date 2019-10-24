## [Unreleased] 1.1.11
### Fixed
- Fix order sync failing if purchasable had been deleted (Fixes #14 via [@stenvdb](https://github.com/stenvdb))
- Fix list help text link being incorrect if admin path had changed (Fixes #15)

## 1.1.10 - 2019-08-23
### Fixed
- Fix issue when syncing variants without public urls

## 1.1.9 - 2019-08-23
### Fixed
- Fix error when syncing products without public urls (Fixes #9)
- Fix issue syncing orders or carts that are missing addresses

## 1.1.8 - 2019-08-01
### Fixed
- Fix order sync error when address 2 isn't set (Fixes #7)
- Fix DB tables not being removed after uninstall (Fixes #8)

## 1.1.7 - 2019-07-30
### Changed
- Products without variants will no longer sync

### Fixed
- Fix issue when using aliases in product urls
- Fix store address sending null values (Fixes #7)

## 1.1.6 - 2019-07-30
### Added
- Added checks for required data before store can be synced (Closes #6)
- Added Disconnect button to settings

### Improved
- More details about the request will be logged when an error is encountered

### Fixed
- Fix issue when syncing products that share images (Fixes #3)

## 1.1.5 - 2019-07-23
### Fixed
- Fix product image urls being relative, not absolute to the site

## 1.1.4 - 2019-07-18
### Added
- Add section showing all synced products

### Changed
- Product / variant images crop using mode fit

## 1.1.3 - 2019-07-11
### Fixed
- Fix product sync error when some products are soft deleted (Fixes #3 again)

## 1.1.2 - 2019-06-28
### Fixed
- Fix casting issue on some properties

## 1.1.1 - 2019-06-28
### Fixed
- Fix error when syncing an order with no return URL (Fixes #3)

## 1.1.0 - 2019-06-28
### Added
- Add support for 3rd party product and purchasable types
- Add support for Verbb Events (Fixes #3)

## 1.0.8 - 2019-06-26
### Added
- Restored carts now track their email campaign ID and sync it back to the order

## 1.0.7 - 2019-06-25
### Fixed
- Fix incorrect request method error when updating an order

## 1.0.6 - 2019-06-25
### Fixed
- Fix missing customer email for guest customers
- Fix abandoned cart restore URL requiring admin login

## 1.0.5 - 2019-06-25
### Added
- Actually add cart restore functionality (oops).

## 1.0.4 - 2019-06-24
### Added
- Added alert to the Mailchimp CP section telling the user that syncing is disabled (if it is).

## 1.0.3 - 2019-06-24
### Added
- Added `disableSyncing` setting to prevent all syncing to Mailchimp

## 1.0.2 - 2019-06-24
### Fixed
- Fix another issue when using an env variable for the API key

## 1.0.1 - 2019-06-24
### Fixed
- Fix issue when using an env variable for the API key

## 1.0.0 - 2019-06-24
### Changed
- Initial Release
