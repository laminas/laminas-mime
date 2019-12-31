# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 2.6.0 - 2016-04-20

### Added

- [zendframework/zend-mime#6](https://github.com/zendframework/zend-mime/pull/6) adds
  `Mime::mimeDetectCharset()`, which can be used to detect the charset
  of a given string (usually a header) according to the rules specified in
  RFC-2047.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 2.5.2 - 2016-04-20

### Added

- [zendframework/zend-mime#8](https://github.com/zendframework/zend-mime/pull/8) and
  [zendframework/zend-mime#11](https://github.com/zendframework/zend-mime/pull/11) port documentation
  from the api-tools-documentation repo, and publish it to
  https://docs.laminas.dev/laminas-mime/

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-mime#2](https://github.com/zendframework/zend-mime/pull/2) fixes
  `Mime::encodeBase64()`'s behavior when presented with lines of invalid
  lengths (not multiples of 4).
- [zendframework/zend-mime#4](https://github.com/zendframework/zend-mime/pull/4) modifies
  `Mime::encodeQuotedPrintable()` to ensure it never creates a header line
  consisting of only a dot (concatenation character), a situation that can break
  parsing by Outlook.
- [zendframework/zend-mime#7](https://github.com/zendframework/zend-mime/pull/7) provides a patch that
  allows parsing MIME parts that have no headers.
- [zendframework/zend-mime#9](https://github.com/zendframework/zend-mime/pull/9) updates the
  dependencies to:
  - allow PHP 5.5+ or PHP 7+ versions.
  - allow laminas-stdlib 2.7+ or 3.0+ verions.
