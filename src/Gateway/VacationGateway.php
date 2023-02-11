<?php

declare(strict_types=1);

namespace App\Gateway;

use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class VacationGateway
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private CacheInterface $cache
    ) {}

    public function findByYear(string $year): array
    {
        try {
            $content = $this->cache->get(
                'vacations ' . $year, 
                function (ItemInterface $item) use ($year) {
                    $item->expiresAfter(3600 *24 * 30);
                    
                    $response = $this->httpClient->request(
                        'GET',
                        'https://ferien-api.de/api/v1/holidays/SN/' . $year
                    );
                    return $response->toArray();
                }
            );
        } catch (TransportExceptionInterface $exception) {
            $content = [];
        }
        
        return $content;
    }
}
