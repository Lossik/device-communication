<?php

declare(strict_types=1);

namespace Lossik\DeviceCommunication;

/**
 * Commands of one menu (`/ip/address`, `/queue/tree`, …). Created by Client::menu().
 */
final class Menu
{
    /**
     * @param string $path '/ip/address' (also accepts 'ip address')
     */
    public function __construct(private readonly Transport $transport, private readonly string $path)
    {
    }

    /**
     * @param Query|null $query null = all rows
     * @param list<string> $proplist empty = all properties
     * @return list<array<string, string>>
     * @throws CommunicationException
     */
    public function print(?Query $query = null, array $proplist = []): array
    {
        return $this->transport->execute(new Command($this->path, 'print', [], $query, $proplist))->rows;
    }

    /**
     * @param Query|null $query null = all rows
     * @return array<string, string>|null the first matching row; null = nothing matches
     * @throws CommunicationException
     */
    public function first(?Query $query = null): ?array
    {
        return $this->print($query)[0] ?? null;
    }

    /**
     * @param array<string, string> $attributes
     * @return string|null the ID of the new item (`ret`); null when the device returns none
     * @throws CommunicationException
     */
    public function add(array $attributes): ?string
    {
        return $this->transport->execute(new Command($this->path, 'add', $attributes))->ret;
    }

    /**
     * Sets the attributes on the items matching the query: one `print` of their IDs, then one `set` with all of them
     * (`.id=*1,*2`), or only with the first one when $onlyFirst. Nothing matches → no `set` is sent.
     *
     * @param array<string, string> $attributes
     * @return int the number of updated items
     * @throws CommunicationException
     */
    public function update(Query $query, array $attributes, bool $onlyFirst = false): int
    {
        return $this->applyToMatches('set', $query, $attributes, $onlyFirst);
    }

    /**
     * Removes the items matching the query: one `print` of their IDs, then one `remove` with all of them
     * (`.id=*1,*2`), or only with the first one when $onlyFirst. Nothing matches → no `remove` is sent.
     *
     * @return int the number of removed items
     * @throws CommunicationException
     */
    public function delete(Query $query, bool $onlyFirst = false): int
    {
        return $this->applyToMatches('remove', $query, [], $onlyFirst);
    }

    /**
     * Any verb of this menu; the ID of an item goes to `$attributes['.id']`.
     *
     * @param array<string, string> $attributes
     * @throws CommunicationException
     */
    public function run(string $verb, array $attributes = []): Result
    {
        return $this->transport->execute(new Command($this->path, $verb, $attributes));
    }

    /**
     * Streams `listen` of this menu; only over a StreamingTransport.
     *
     * @param callable(array<string, string>): bool $onRow returns false = cancel (/cancel)
     * @param float|null $idleTimeout silence after which the stream ends with TimeoutException; null = no limit
     * @throws UnsupportedOperationException the transport cannot stream (nothing is sent)
     * @throws CommunicationException
     */
    public function listen(callable $onRow, ?float $idleTimeout = null): void
    {
        if (!$this->transport instanceof StreamingTransport) {
            throw new UnsupportedOperationException(
                'listen needs a StreamingTransport, ' . $this->transport::class . ' is not one',
            );
        }
        $this->transport->stream(new Command($this->path, 'listen'), $onRow, $idleTimeout);
    }

    /**
     * @param array<string, string> $attributes
     * @throws CommunicationException
     */
    private function applyToMatches(string $verb, Query $query, array $attributes, bool $onlyFirst): int
    {
        $ids = array_column($this->print($query, ['.id']), '.id');
        if ($ids === []) {
            return 0;
        }
        if ($onlyFirst) {
            $ids = [$ids[0]];
        }
        $this->transport->execute(new Command($this->path, $verb, ['.id' => implode(',', $ids)] + $attributes));

        return count($ids);
    }
}
