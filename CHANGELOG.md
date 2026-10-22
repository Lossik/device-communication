# Changelog

All notable changes to this project are documented in this file.
The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/) and the project follows
[Semantic Versioning](https://semver.org/).

## [3.0.0] - Unreleased

A new, transport-independent contract. Nothing of the 2.x API remains; full upgrade notes come with the release.

### Changed

- **New namespace `Lossik\DeviceCommunication\`** (was `Lossik\Device\Communication\`); no 2.x class remains, so
  mixed code fails with "Class not found" instead of changing behaviour silently.
- The contract is a RouterOS command, not a socket: `Command` (menu, verb, attributes, query, proplist), `Query`
  (a condition tree compiled by each transport), `Result` (rows and `ret`), `Transport` and `StreamingTransport`.
  `IConnection`, `ICommand`, `IDefinition`, `IOptions` and `Connection` are removed.
- User API `Client` and `Menu`: `print`, `first` (returns `null` when nothing matches), `add` (returns the new ID),
  `update` and `delete` (send nothing and return 0 when nothing matches), `run`, `listen`, `Client::version()`.
- Typed exceptions instead of one `RuntimeException` with numeric codes: the marker interface
  `CommunicationException`, `ConnectionException`, `TimeoutException`, `AuthenticationException`, `TrapException`,
  `FatalException`, `ProtocolException` and `UnsupportedOperationException`.
- Supported are only patched PHP releases (8.2.35+, 8.3.36+, 8.4.27+, 8.5.12+), as in 2.1.0; the PHP minimum is
  raised only in a minor release.

## [2.1.0]

Maintenance release of the 2.x line: it runs on patched PHP and is MIT licensed. The code and the public API are
unchanged and the known issues of 2.x are **not** fixed (see Known issues; they are fixed in 3.0).

### Changed

- Requires PHP `^8.2` (was `>=7.1`). Users on older PHP stay on 2.0.x.
- **Supported are only patched PHP releases: 8.2.35+, 8.3.36+, 8.4.27+ and 8.5.12+** (CVE-2026-109618 and
  CVE-2026-109619, "PHP2Shell"). Composer cannot tell a patched build from an unpatched one, because Linux
  distributions backport the fixes without changing the version number, so `composer.json` says `^8.2` and only
  the CI of this project enforces the patched minima.
- Policy: the PHP minimum is raised only in a minor release, with a note in this changelog.
- Licensed under the MIT license (`LICENSE`).

### Known issues

Not fixed in 2.x; fixed in 3.0.

- **A command after `disconnect()`:** `Connection::comm()` has no guard and passes the closed (`null`) socket to the
  definition; with `lossik/mikrotik-api` 2.x this throws `TypeError` (an `\Error`, not an `\Exception`), and
  `isLoged()` stays `true`.
- **The interfaces do not describe the contract in use:** `IConnection` lacks `isConnected()`, `isLoged()` and
  `disconnect()`, `ICommand` lacks `getOneItem()`, and `IOptions` is empty.
- **Errors:** there is a single `RuntimeException` type; the error kind is only a numeric code, and the codes are
  namespace constants (e.g. `IMPOSSIBLE_LOGIN`), not class constants.

## [2.0.1] and older

Released without a changelog; see the git history.
