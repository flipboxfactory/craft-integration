Changelog
=========

## 3.0.0 - 2019-05-23
### Updated
- Updated dependencies: `flipboxfactory/craft-psr16` for php 8 compatibility

## 2.2.4 - 2019-05-23
### Updated
- `IntegrationConnections::find()` (service) now accepts a connection 'id' although it's recommended to use 'handle'

## 2.2.3 - 2019-05-14
### Fixed
- Handling a `migration::up()` response appropriately

## 2.2.2 - 2019-05-13
### Changed
- `Field::getObjectLabel` is now public (was protected).

### Added
- EnvironmentalTableTrait to assist w/ environment specific tables 

## 2.2.1 - 2019-05-08
### Fixed
- Migration would attempt to add 'name' column even if it already existed.

## 2.2.0 - 2019-03-06
### Added
- Cache and Connection abstract services

## 2.1.0 - 2019-03-04
### Removed
- Integrations field no longer has an 'objects' property.

### Added
- Integrations field has an abstract `getObjectLabel` method that must be implemented.

## 2.0.2 - 2019-01-16
### Added
- Field type is included in index as an option.

## 2.0.1 - 2019-01-15
### Fixed
- Incorrect verbiage w/ min/max error messaging

## 2.0.0 - 2019-01-08
### Changed
- Major refactoring 

## 1.1.0.2 - 2018-09-07
### Fixed
- Save connection trait references Yii's ActiveRecord class

## 1.1.0.1 - 2018-09-07
### Changed
- IntegrationConnectionManager class name for autoloading support

## 1.1.0 - 2018-09-07
### Added
- Connection management

## 1.0.5 - 2018-08-01
### Changed
- Setting input label

## 1.0.4 - 2018-08-01
### Changed
- Field input no longer has 'instanceId' instead the entire connection is available

## 1.0.3 - 2018-07-18
### Fixed
- Field settings were not picking up all of the setting attributes

## 1.0.2 - 2018-07-18
### Changed
- Integration Field service supports input/setting html variables overrides 

### Removed
- `IntegrationAssociation::validateObject()` is no longer required

## 1.0.1 - 2018-07-18
### Added
- Additional association methods that may be useful

## 1.0.0
- Initial release.
