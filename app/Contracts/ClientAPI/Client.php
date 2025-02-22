<?php

namespace App\Contracts\ClientAPI;

/**
 * Interface Client
 * @package App\Contracts\ClientAPI
 * @method \App\Infrastructure\Builders\GuzzleRequestBuilder get($uri, array $options = [])
 * @method \App\Infrastructure\Builders\GuzzleRequestBuilder post($uri, array $options = [])
 * @method \App\Infrastructure\Builders\GuzzleRequestBuilder patch($uri, array $options = [])
 * @method \App\Infrastructure\Builders\GuzzleRequestBuilder put($uri, array $options = [])
 * @method \App\Infrastructure\Builders\GuzzleRequestBuilder delete($uri, array $options = [])
 */
interface Client
{
    public function request(string $method, string $url, array $options = []);
}
