# lossik/device-communication

The transport-independent contract of the RouterOS clients [`lossik/mikrotik-api`](https://github.com/Lossik/mikrotik-api)
and [`lossik/mikrotik-ssh`](https://github.com/Lossik/mikrotik-ssh): a command (`Command`, `Query`, `Result`), the
transport interfaces (`Transport`, `StreamingTransport`), the user API (`Client`, `Menu`) and typed exceptions. It
does not talk to any device on its own.

**3.0 is in development (branch `feature/3.0`, unreleased).** The 3.0 transports of the clients are not released
yet; see the [changelog](CHANGELOG.md). The released line is 2.x (maintenance only).

## The contract

The contract is a RouterOS **command**, not a socket: a menu, a verb, attributes, an optional query and an optional
property list. A transport turns it into whatever its protocol needs and returns the rows. That level also fits a
transport that has no socket of its own: a REST transport is planned as a separate package on top of this one; it
is not part of this package.

| class | role |
|---|---|
| `Command(string $menu, string $verb, array $attributes = [], ?Query $query = null, array $proplist = [])` | a value object. The menu is normalised (`'ip address'` and `'/ip/address'` are both `'/ip/address'`); the ID of an item goes to `attributes['.id']` |
| `Query` | a tree of conditions, built with `where`, `has`, `hasNot`, `less`, `greater`, `and`, `or`, `not`; the node kind is `QueryOperator`. `Query` compiles nothing: each transport translates the tree |
| `Result(array $rows, ?string $ret = null)` | `rows`: a list of `string => string` rows; `ret`: the value of `=ret=`, the ID of an added item |
| `Transport` | `execute(Command): Result`, `close()` (idempotent), `isOpen()` |
| `StreamingTransport extends Transport` | adds `stream(Command, callable $onRow, ?float $idleTimeout = null)` for long-running commands (`listen`, `monitor`); `$onRow` returns `false` to cancel |
| `Client`, `Menu` | the user API over any `Transport`: `$client->menu('/ip/address')->print(...)` |

The calls below are an overview, not a script: `$transport` is any `Transport` (the quick start of
`lossik/mikrotik-api` runs them over its `ApiTransport`); a complete transport with a runnable example follows in
[Writing a transport](#writing-a-transport).

```php
use Lossik\DeviceCommunication\Client;
use Lossik\DeviceCommunication\Query;

$client = new Client($transport);   // any Transport
$rows = $client->menu('/ip/address')->print(Query::where('interface', 'ether1'), proplist: ['.id', 'address']);
$row = $client->menu('/queue/tree')->first(Query::where('name', 'q1'));         // ?array, null = nothing matches
$id = $client->menu('/ip/arp')->add(['address' => '192.0.2.2', 'interface' => 'ether1']);   // ?string: the new ID
$n = $client->menu('/ip/firewall/nat')->update(Query::where('comment', 'x'), ['disabled' => 'yes'], onlyFirst: true);
$n = $client->menu('/ppp/secret')->delete(Query::where('name', 'u1'));          // int: the number of items
$client->menu('/system/identity')->run('set', ['name' => 'R1']);                // any verb, returns the Result
$version = $client->version();                                                  // ?string
$client->menu('/interface')->listen(fn (array $row): bool => true, idleTimeout: 120.0);   // StreamingTransport only
```

`update()` and `delete()` first `print` the `.id` of the matching items and then send **one** `set` or `remove` with
all of them (`.id=*1,*2`); with `onlyFirst: true` only the first one. When nothing matches, nothing is sent and
they return 0. `listen()` on a transport that is not a `StreamingTransport` throws `UnsupportedOperationException`
before anything is sent.

### Query

| node | RouterOS API form (as `mikrotik-api` sends it) |
|---|---|
| `where('k', 'v')`, `has('k')`, `hasNot('k')` | `?k=v`, `?k`, `?-k` |
| `less('k', 'v')`, `greater('k', 'v')` | `?<k=v`, `?>k=v` |
| `and(a, b, ...)` | the operands, then `?#&` for each operand after the first |
| `or(a, b, ...)` | the operands, then `?#\|` for each operand after the first |
| `not(a)` | the operand, then `?#!` |

There is no prefix or regular-expression condition (the API cannot express one): filter the rows in PHP. A
transport that cannot translate a node throws `UnsupportedOperationException`.

## Exceptions

```
CommunicationException                     (interface; every runtime error of a transport)
|- ConnectionException                     the transport is closed after it
|    |- TimeoutException                   ->limit: which limit ran out, named as the transport's option
|    |- FatalException                     ->reason (the device ended the session)
|    '- ProtocolException                  the device sent something the protocol does not allow
|- AuthenticationException                 wrong user name or password
'- TrapException                           the device refused the command: ->category (?int), ->traps()
\UnsupportedOperationException             a \LogicException, NOT a CommunicationException
```

Every `CommunicationException` extends `\RuntimeException`, so `catch (\Exception)` catches them too. The numeric
error codes of 2.x are gone; tell the errors apart by class. `UnsupportedOperationException` is a programming error
(an operation the transport cannot do, such as `listen` over a transport that cannot stream); it is thrown before
anything is sent and does not close the transport.

## Writing a transport

Implement `Transport` (and `StreamingTransport` if the protocol can stream). The rules the clients rely on:

- **After a `ConnectionException` or a subclass the transport is closed**, and every further call throws
  `ConnectionException` without any I/O. A `TrapException` does not close it: read the response to its end first.
  `close()` is idempotent; `isOpen()` tells the state.
- For type-valid input `execute()` throws no `\Error`. Input the transport cannot encode (a value its charset
  cannot represent) may be an `\InvalidArgumentException` before any I/O; every other failure is a
  `CommunicationException`.
- A `Query` node the transport cannot translate is an `UnsupportedOperationException`, never a wrong query.
- `stream()` calls `$onRow` for every row and cancels the command when it returns `false`; it ends with
  `TimeoutException` (`limit` `'idleTimeout'`) when the device is silent for `$idleTimeout` seconds (`null` = no limit).
- Later needs get a new optional interface (as `StreamingTransport` did), not a change of `Transport`.

A complete transport that keeps its tables in memory (it answers `print` with a plain `where`):

<!-- run -->
```php
use Lossik\DeviceCommunication\Client;
use Lossik\DeviceCommunication\Command;
use Lossik\DeviceCommunication\ConnectionException;
use Lossik\DeviceCommunication\Query;
use Lossik\DeviceCommunication\QueryOperator;
use Lossik\DeviceCommunication\Result;
use Lossik\DeviceCommunication\Transport;
use Lossik\DeviceCommunication\UnsupportedOperationException;

final class MemoryTransport implements Transport
{
    private bool $open = true;

    /** @param array<string, list<array<string, string>>> $tables menu => rows */
    public function __construct(private array $tables) {}

    public function execute(Command $command): Result
    {
        $q = $command->query;
        if (!$this->open) { throw new ConnectionException('The transport is closed'); }
        if ($command->verb !== 'print' || ($q !== null && $q->operator !== QueryOperator::Where)) {
            throw new UnsupportedOperationException('Only print with a plain where is supported');
        }
        $rows = array_filter($this->tables[$command->menu] ?? [], fn (array $r): bool => $q === null || ($r[$q->key ?? ''] ?? null) === $q->value);

        return new Result(array_values($rows));
    }
    public function close(): void { $this->open = false; }
    public function isOpen(): bool { return $this->open; }
}

$transport = new MemoryTransport(['/ip/address' => [
    ['.id' => '*1', 'address' => '192.0.2.1/24', 'interface' => 'ether1'],
    ['.id' => '*2', 'address' => '198.51.100.1/24', 'interface' => 'ether2'],
]]);
$client = new Client($transport);

$rows = $client->menu('ip address')->print(Query::where('interface', 'ether2'));   // the second row only
$none = $client->menu('/ip/address')->update(Query::where('interface', 'ether9'), ['disabled' => 'yes']);   // 0
try {
    $client->menu('/interface')->listen(fn (array $row): bool => false);
} catch (UnsupportedOperationException) {
    $canStream = false;   // MemoryTransport is not a StreamingTransport: nothing was sent
}
$transport->close();
try {
    $client->version();
} catch (ConnectionException) {
    $closed = !$transport->isOpen();   // the transport is closed: no I/O, ConnectionException
}
```

`tests/FakeTransport.php` is a scripted test double of this package's own suite; it is not part of the installed
package.

## Supported PHP

Only patched PHP releases are supported: **8.2.35+, 8.3.36+, 8.4.27+ and 8.5.12+** (CVE-2026-109618 and
CVE-2026-109619, "PHP2Shell"). `composer.json` requires `"php": "^8.2"`, so Composer also installs the package on
an unpatched PHP: Linux distributions backport the fixes without changing the version number, and Composer cannot
tell the two apart. Only the CI of this project enforces the patched minima. The PHP minimum is raised only in a
minor release, announced in the [changelog](CHANGELOG.md).

## License

MIT, see [LICENSE](LICENSE).
