<?php

use App\Infrastructure\Services\ClientAPI\ClientService;

if (!function_exists('client')) {
    function client(array $config = []): ClientService
    {
        return new ClientService($config);
    }
}
