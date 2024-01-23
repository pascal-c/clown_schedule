<?php

declare(strict_types=1);

namespace App\Gateway;

use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class VacationGateway
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private CacheInterface $vacationCache,
    ) {
    }

    public function findByYear(string $year): array
    {
        return $this->vacationCache->get(
            'vacations '.$year,
            function (ItemInterface $item) use ($year): array {
                try {
                    $response = $this->httpClient->request(
                        'GET',
                        'https://ferien-api.de/api/v1/holidays/SN/'.$year,
                        [
                            'timeout' => 2,
                            'max_duration' => 2,
                        ]
                    );
                    $item->expiresAfter(3600 * 24 * 30); // keep for 30 days

                    return $response->toArray();
                } catch (TransportExceptionInterface|ClientException) {
                    $item->expiresAfter(10); // something went wrong - retry after 10s

                    return [];
                }
            }
        );
    }
}
