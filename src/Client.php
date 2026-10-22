<?php

declare(strict_types=1);

namespace Lossik\DeviceCommunication;

/**
 * The user API over any Transport.
 *
 *     $client = new Client($transport);
 *     $rows = $client->menu('/ip/address')->print(Query::where('interface', 'ether1'), proplist: ['.id', 'address']);
 */
final class Client
{
    public function __construct(private readonly Transport $transport)
    {
    }

    /**
     * @param string $path '/ip/address' (also accepts 'ip address')
     */
    public function menu(string $path): Menu
    {
        return new Menu($this->transport, $path);
    }

    /**
     * The RouterOS version (`/system/resource/print .proplist=version`); null when the device does not return it.
     *
     * @throws CommunicationException
     */
    public function version(): ?string
    {
        return $this->menu('/system/resource')->print(proplist: ['version'])[0]['version'] ?? null;
    }
}
