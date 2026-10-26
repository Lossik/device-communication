# lossik/device-communication

Common interfaces (`IConnection`, `ICommand`, `IDefinition`, `IOptions`), a base `Connection` and exceptions shared by
the device communication clients [`lossik/mikrotik-api`](https://github.com/Lossik/mikrotik-api) and
[`lossik/mikrotik-ssh`](https://github.com/Lossik/mikrotik-ssh). It does not talk to any device on its own.

**2.x is maintenance only.** It receives fixes needed to run on supported PHP releases, nothing else. New
development happens in 3.0 (branch `feature/3.0`, to be released), which fixes the known issues of 2.x and changes
the API.

## Supported PHP

Only patched PHP releases are supported: **8.2.35+, 8.3.36+, 8.4.27+ and 8.5.12+** (CVE-2026-109618 and
CVE-2026-109619, "PHP2Shell"). `composer.json` requires `"php": "^8.2"`, so Composer also installs the package on
an unpatched PHP: Linux distributions backport the fixes without changing the version number, and Composer cannot
tell the two apart. Only the CI of this project enforces the patched minima. The PHP minimum is raised only in a
minor release, announced in the [changelog](CHANGELOG.md).

## Installation

Usually installed as a dependency of `lossik/mikrotik-api` or `lossik/mikrotik-ssh`. Directly:

```bash
composer require lossik/device-communication:^2.1
```

## Known issues

2.x has design defects that are fixed only in 3.0. They are listed in the [changelog](CHANGELOG.md#known-issues).

## License

MIT, see [LICENSE](LICENSE).
