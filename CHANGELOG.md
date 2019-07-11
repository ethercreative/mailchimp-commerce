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
