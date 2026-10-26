<?php

declare(strict_types=1);

namespace Lossik\DeviceCommunication;

/**
 * A tree of conditions for `print` (and for `update`/`delete`, which print first).
 *
 * Query compiles nothing; the tree is public so that each transport compiles it to its own form. The RouterOS API
 * forms are:
 *
 *  - where('k', 'v') → `?k=v`, has('k') → `?k`, hasNot('k') → `?-k`
 *  - less('k', 'v') → `?<k=v`, greater('k', 'v') → `?>k=v`
 *  - and(a, b, …) → the operands, then `?#&` for each operand after the first
 *  - or(a, b, …) → the operands, then `?#|` for each operand after the first
 *  - not(a) → the operand, then `?#!`
 *
 * SSH compiles the tree to `where …` and REST to `.query`. A transport that cannot translate a node throws
 * UnsupportedOperationException. There is no `~` (prefix) condition: the API cannot express it.
 */
final class Query
{
    /**
     * @param list<Query> $operands the children of And, Or (two or more) and Not (exactly one)
     */
    private function __construct(
        public readonly QueryOperator $operator,
        public readonly ?string $key = null,
        public readonly ?string $value = null,
        public readonly array $operands = [],
    ) {
    }

    public static function where(string $key, string $value): self
    {
        return new self(QueryOperator::Where, $key, $value);
    }

    public static function has(string $key): self
    {
        return new self(QueryOperator::Has, $key);
    }

    public static function hasNot(string $key): self
    {
        return new self(QueryOperator::HasNot, $key);
    }

    public static function less(string $key, string $value): self
    {
        return new self(QueryOperator::Less, $key, $value);
    }

    public static function greater(string $key, string $value): self
    {
        return new self(QueryOperator::Greater, $key, $value);
    }

    public static function and(Query $first, Query $second, Query ...$more): self
    {
        return new self(QueryOperator::And, operands: [$first, $second, ...array_values($more)]);
    }

    public static function or(Query $first, Query $second, Query ...$more): self
    {
        return new self(QueryOperator::Or, operands: [$first, $second, ...array_values($more)]);
    }

    public static function not(Query $operand): self
    {
        return new self(QueryOperator::Not, operands: [$operand]);
    }
}
