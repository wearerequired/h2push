# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.1.0] - 2022-07-26

### Fixed
* Fix incorrect handling of output buffer for `wp_head` action when stacked buffers are used.

### Added
* New `h2push.is_allowed_push_host` filter to allow external files to be preloaded.

## [2.0.0] - 2021-04-21

### Changed
* Requires PHP 7.4.
* Requires WordPress 5.6.
* Use output buffer for `wp_head` action to be able to send headers.

### Added
* New `h2push.as_header` filter to define whether to use Link headers or the `<link>` element.
* New `h2push.push_resources` filter to customize URLs for resources to push.
* Support resources with custom `as`, `type`, `crossorigin` and `nopush` parameters.

## [1.3.0] - 2020-09-30

### Changed
* Add fallback to `<link>` element if headers are already sent.

## [1.2.0] - 2018-06-12

### Fixed
* Fix incorrect handling of dependencies with version set to `null`.
* Fix linking of dependencies with protocol-relative URLs.

## [1.1.0] - 2017-12-09

### Changed
* Add support for relative script and style paths.

## [1.0.0] - 2017-12-08

### Added
* The initial version of the plugin.

[Unreleased]: https://github.com/wearerequired/h2push/compare/2.1.0...HEAD
[2.1.0]: https://github.com/wearerequired/h2push/compare/2.0.0...2.1.0
[2.0.0]: https://github.com/wearerequired/h2push/compare/1.3.0...2.0.0
[1.3.0]: https://github.com/wearerequired/h2push/compare/1.2.0...1.3.0
[1.2.0]: https://github.com/wearerequired/h2push/compare/1.1.0...1.2.0
[1.1.0]: https://github.com/wearerequired/h2push/compare/1.0.0...1.1.0
[1.0.0]: https://github.com/wearerequired/h2push/compare/f1bd977d83e063311d162b2415f3499b20d7296e...0.1.0
