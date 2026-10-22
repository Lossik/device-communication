# lossik/device-communication

The transport-independent contract of the RouterOS clients [`lossik/mikrotik-api`](https://github.com/Lossik/mikrotik-api)
and [`lossik/mikrotik-ssh`](https://github.com/Lossik/mikrotik-ssh): a command (`Command`, `Query`, `Result`), the
transport interfaces (`Transport`, `StreamingTransport`), the user API (`Client`, `Menu`) and typed exceptions. It
does not talk to any device on its own.

**3.0 is in development (branch `feature/3.0`, unreleased).** The API is not final and the 3.0 transports of the
clients are not released yet; see the [changelog](CHANGELOG.md). The released line is 2.x (maintenance only).

## Supported PHP

Only patched PHP releases are supported: **8.2.35+, 8.3.36+, 8.4.27+ and 8.5.12+** (CVE-2026-109618 and
CVE-2026-109619, "PHP2Shell"). `composer.json` requires `"php": "^8.2"`, so Composer also installs the package on
an unpatched PHP: Linux distributions backport the fixes without changing the version number, and Composer cannot
tell the two apart. Only the CI of this project enforces the patched minima. The PHP minimum is raised only in a
minor release, announced in the [changelog](CHANGELOG.md).

## License

MIT, see [LICENSE](LICENSE).
