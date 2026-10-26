<?php

declare(strict_types=1);

namespace Lossik\DeviceCommunication\Tests;

use Lossik\DeviceCommunication\Client;
use Lossik\DeviceCommunication\Command;
use Lossik\DeviceCommunication\Query;
use Lossik\DeviceCommunication\Result;
use Lossik\DeviceCommunication\UnsupportedOperationException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ClientTest extends TestCase
{
    /**
     * @return iterable<string, array{list<array<string, string>>, bool, string|null, int}>
     */
    public static function matchScenarios(): iterable
    {
        yield 'no match: nothing sent' => [[], false, null, 0];
        yield 'one match' => [[['.id' => '*1']], false, '*1', 1];
        yield 'two matches in one command' => [[['.id' => '*1'], ['.id' => '*2']], false, '*1,*2', 2];
        yield 'two matches, onlyFirst' => [[['.id' => '*1'], ['.id' => '*2']], true, '*1', 1];
    }

    /**
     * @param list<array<string, string>> $printed
     */
    #[DataProvider('matchScenarios')]
    public function testUpdateSetsOnlyMatchedIds(array $printed, bool $onlyFirst, ?string $ids, int $count): void
    {
        $transport = new FakeTransport(new Result($printed), new Result([]));
        $query = Query::where('comment', 'x');

        $updated = (new Client($transport))->menu('/ip/firewall/nat')
            ->update($query, ['disabled' => 'yes'], $onlyFirst);

        self::assertSame($count, $updated);
        self::assertPrintOfIds($query, $transport->received[0]);
        $expected = $ids === null ? [] : [['set', ['.id' => $ids, 'disabled' => 'yes']]];
        self::assertSame($expected, self::writes($transport->received));
    }

    /**
     * @param list<array<string, string>> $printed
     */
    #[DataProvider('matchScenarios')]
    public function testDeleteRemovesOnlyMatchedIds(array $printed, bool $onlyFirst, ?string $ids, int $count): void
    {
        $transport = new FakeTransport(new Result($printed), new Result([]));
        $query = Query::where('name', 'u1');

        $deleted = (new Client($transport))->menu('/ppp/secret')->delete($query, $onlyFirst);

        self::assertSame($count, $deleted);
        self::assertPrintOfIds($query, $transport->received[0]);
        $expected = $ids === null ? [] : [['remove', ['.id' => $ids]]];
        self::assertSame($expected, self::writes($transport->received));
    }

    /**
     * @return iterable<string, array{list<array<string, string>>, array<string, string>|null}>
     */
    public static function firstRows(): iterable
    {
        yield 'nothing → null' => [[], null];
        yield 'rows → the first one' => [[['name' => 'q1'], ['name' => 'q2']], ['name' => 'q1']];
    }

    /**
     * @param list<array<string, string>> $printed
     * @param array<string, string>|null $expected
     */
    #[DataProvider('firstRows')]
    public function testFirstReturnsTheFirstRowOrNull(array $printed, ?array $expected): void
    {
        $transport = new FakeTransport(new Result($printed));

        self::assertSame($expected, (new Client($transport))->menu('/queue/tree')->first(Query::where('name', 'q1')));
    }

    public function testAddReturnsTheIdFromRet(): void
    {
        $transport = new FakeTransport(new Result([], '*9A'));

        $id = (new Client($transport))->menu('/ip/arp')->add(['address' => '10.0.0.2']);

        self::assertSame('*9A', $id);
    }

    public function testListenOverNonStreamingTransportSendsNothing(): void
    {
        $transport = new FakeTransport();

        try {
            (new Client($transport))->menu('/interface')->listen(static fn (array $row): bool => true);
            self::fail('UnsupportedOperationException expected');
        } catch (UnsupportedOperationException) {
        }

        self::assertSame([], $transport->received);
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function menus(): iterable
    {
        yield 'with slashes' => ['/ip/firewall/nat', '/ip/firewall/nat'];
        yield 'with spaces' => ['ip firewall nat', '/ip/firewall/nat'];
    }

    #[DataProvider('menus')]
    public function testMenuWithSpacesIsTheSameAsWithSlashes(string $menu, string $expected): void
    {
        self::assertSame($expected, (new Command($menu, 'print'))->menu);
    }

    private static function assertPrintOfIds(Query $query, Command $print): void
    {
        self::assertSame('print', $print->verb);
        self::assertSame($query, $print->query);
        self::assertSame(['.id'], $print->proplist);
    }

    /**
     * The commands sent after the first one (the print of IDs), as [verb, attributes].
     *
     * @param list<Command> $received
     * @return list<array{string, array<string, string>}>
     */
    private static function writes(array $received): array
    {
        return array_map(static fn (Command $c): array => [$c->verb, $c->attributes], array_slice($received, 1));
    }
}
