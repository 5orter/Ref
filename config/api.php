<?php

config([
    'accountant' => [
        'ClientId' => '45ccbee5-60a9-42a7-a9b4-c3dd3a55d63e',
        'Secret' => 'JO}ev[J^G<ar5y#*8+s0',
        'DBId' => '24617',
        'refreshAccessToken' => [
            'method' => 'post',
            'url' => 'https://dbservices.armsoft.am/mobiletrade/api/Login/ApiKey/',
            'headers' => [
                'Content-Type' => 'application/json',
            ]
        ],
        'getGoods' => [
            'method' => 'post',
            'url' => 'https://dbservices.armsoft.am/mobiletrade/api/Data/Goods/',
            'headers' => [
                'Content-Type' => 'application/json',
            ]
        ],
        'getGoodsRemsDetailedByStorage' => [
            'method' => 'post',
            'url' => 'https://dbservices.armsoft.am/mobiletrade/api/Data/GoodsRemsDetailedByStorage',
            'headers' => [
                'Content-Type' => 'application/json',
            ]
        ],
    ]
]);
