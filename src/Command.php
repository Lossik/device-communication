<?php

declare(strict_types=1);

namespace Lossik\DeviceCommunication;

/**
 * One RouterOS command: menu, verb, attributes, an optional query and an optional proplist.
 *
 * The menu is normalised: spaces become `/`, a leading `/` is added, repeated and trailing slashes are dropped
 * ('ip address' and '/ip/address' are the same menu '/ip/address', as in 2.x).
 */
final class Command
{
    public readonly string $menu;

    /**
     * @param string $menu '/ip/address' (also accepts 'ip address')
     * @param string $verb 'print' | 'add' | 'set' | 'remove' | any other ('enable', 'monitor', 'listen', …)
     * @param array<string, string> $attributes the ID of an item goes to `attributes['.id']`
     * @param list<string> $proplist
     */
    public function __construct(
        string $menu,
        public readonly string $verb,
        public readonly array $attributes = [],
        public readonly ?Query $query = null,
        public readonly array $proplist = [],
    ) {
        $this->menu = '/' . trim((string) preg_replace('~[\s/]+~', '/', $menu), '/');
    }
}
