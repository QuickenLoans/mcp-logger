# Change Log
All notable changes to this project will be documented in this file. See [keepachangelog.com](http://keepachangelog.com) for reference.

This package follows [semver](http://semver.org/) versioning.

## [3.0.1] - 2019-05-01
### Fixed
- Removed use of deprecated constants `FILTER_FLAG_SCHEME_REQUIRED` and `FILTER_FLAG_HOST_REQUIRED` when using
  **GuzzleService** to send logs over http.
  - This enables PHP 7.3 compatibility.

## [3.0.0] - 2016-11-20

### Added
- **GuzzleService** added to support both Guzzle 5 and Guzzle 6.
    - Please note this service has no buffering or async abilities. Sending logs will block further PHP code from running until finished.
    - We recommend not using this service for performance reasons, and using **Syslog** instead.
- **ErrorLogService** added to support writing logs to SAPI, OS, or a file.
- **LineSerializer** added to easily format serialized logs to a single plaintext line.
- Added constants to **MessageInterface** to easily allow setting properties consistently.
- Added **BroadcastLogger** to allow broadcasting messages to multiple loggers.

### Changed
- Changed namespace from `MCP\Logger\*` to `QL\MCP\Logger\*`.
- **LoggerFiltered** renamed to **FilterLogger** to match Monolog naming convention.
- **LogLevelInterface** renamed to **QLLogLevel**.
- Most services or other classes now have defaults for several dependencies.
    - **Logger**
        - Service: `SyslogService`
    - **SyslogService**
        - Serializer: `JSONSerializer`
    - **GuzzleService**
        - Serializer: `XMLSerializer`
    - **ErrorLogService**
        - Serializer: `LineSerializer`
- **Renderers** have been renamed to **Serializers**
    - `XmlRenderer` is now `XMLSerializer`
    - `JsonRenderer` is now `JSONSerializer`
- Standard properties for **Message** have been changed.
    - `affectedSystem` removed.
    - `categoryId` removed.
    - `referrer` removed.
    - `userCommonId` removed.
    - `userDisplayName` removed.
    - `userScreenName` removed.
    - `applicationId` changed to `applicationID`.
    - `level` changed to `severity`.
    - `createTime` changed to `created`.
    - `exceptionData` changed to `errorDetails`.
    - `extendedProperties` changed to `context`.
    - `machineIPAddress` changed to `serverIP`.
    - `machineName` changed to `serverHostname`.
    - `url` changed to `requestURL`.
    - `userAgentBrowser` changed to `userAgent`.
    - `userIPAddress` changed to `userIP`.

### Removed
- **Guzzle3Service** removed.
- **Guzzle4Service** removed.
- **Guzzle5Service** removed.
- **HttpService** removed.
- **KinesisService** removed.
- **PSRMessageFactory** removed.
    - Converting from PSR-3 to QL log levels is now the responsibility of services, not the factory.

## [2.4.4] - 2016-10-26

### Added
- Add **LoggerFiltered**
    - PSR-3 logger that allows a minimum logging level to be defined.
    - Log messages below that level will be ignored, while messages that meet or exceed that level will be proxied and logged.
- Add **NullService**
    - Logging service that will silently consume all logs.

## [2.4.3] - 2016-07-21

### Changed
- **Logger** now requires `MessageFactoryInterface` instead of `MessageFactory`.

## [2.4.2] - 2016-03-14

### Changed
- Update **XMLRenderer** and **JSONRenderer** to add microseconds to 6-digits for TimePoints.
    - Currently this will always be `000000` as **TimePoint** does not support microseconds.

## [2.4.1] - 2016-02-09

### Added
- Add `id` property to **MessageInterface**.
    - This property must be a `QL\MCP\Common\GUID` object and will be generated automatically if not provided.
- Add `ID` property to **JsonRenderer**.
    - This id will always be a lowercase hex encoded and dash separated string.
- Add `LogEntryClientID` to **XmlRenderer**.
    - This id will always be a lowercase hex encoded and dash separated string.
- Add configuration for **JsonRenderer**.
    - Only currently supported value is `JsonRenderer::CONFIG_BACKLOAD_LIMIT` which sets the number of characters for which the backload logic should be applied.

### Changed
- Add backload logic to **JsonRenderer** so that fields with values with a length over 10k (default) are moved to the end of the serialized message.
- Add composer requirement for `paragonie/random_compat` which adds support for random_bytes on `PHP < 7.0`.
    - This requirement has no effect on systems when `PHP >= 7.0`.

## [2.4.0] - 2015-12-28

### Changed
- MCP Logger now requires [MCP Common](https://github.com/quickenloans-mcp/mcp-common) instead of **MCP Core**.

## [2.3.2] - 2015-10-05

### Added
- Add Environment property to Message
    - Supported values: dev, test, beta, prod
    - Apps logging to **Syslog** should set this as a default property on the `MessageFactory`.
- Restore **Guzzle5Service** batching ability.
- Add support to `jsonserializable` objects on extended properties.

### Changed
- Clean up tests
- Clean up README
- Set a default renderer on several services.

## [2.3.1] - 2015-09-01

### Changed
- Resolve a bug that required `PHP 5.6` or greater when using the **SyslogService** class.

## [2.3.0] - 2015-07-24

### Added
- Added **HttpService** for use with MCP HTTP.

## [2.2.1] - 2015-09-01

### Changed
- Resolve a bug that required `PHP 5.6` or greater when using the **SyslogService** class.

## [2.2.0] - 2015-07-17

### Added
- Added **SyslogService**
    - This is now the preferred method of logging as it is better for performance.

## [2.1.0] - 2015-07-01

### Added
- Added **KinesisService**
    - Kinesis allows sending logs in an AWS environment.
    - Use of `MCP\Logger\Service\KinesisService` requires that the optional dependency aws/aws-sdk-php be available. Note that only version 3 and above is supported.

## [2.0.0] - 2015-07-22

### Changed
- The namespace has changed.
    - Previous: `MCP\Service\Logger`
    - Now: `MCP\Logger`
- Changed name of Guzzle 3 service
    - Previous: `GuzzleService`
    - Now: `Guzzle3Service`
- Require Absolute URI Templates for all services.
- Services are now silent by default
    - If a logging failure occurs, backup messages will be sent to `error_log` instead of throwing an exception.

### Added
- Added Guzzle 5 service
    - The Guzzle 5 service supports pooling and async requests. See the `README.md` for more details.
- Added JSON renderer.
    - Not currently supported by HTTP logger. But required for Kinesis Service.
- Added default parameters
    - `XMLWriter` and `Clock` are built automatically if not provided to classes that require them.

### Removed
- Removed PECL HTTP 1.* service.
